<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeEarning;
use App\Models\EmployeeDeduction;
use App\Models\StatutoryRate;
use Carbon\Carbon;

/**
 * PayrollCalculationService handles all payroll calculations with support for:
 * - Flexible earnings and allowances
 * - Flexible deductions
 * - Configurable statutory rates (PAYE, NSSF, SHIF, Housing Levy)
 * - Tax relief calculations
 * - Effective date-based earnings/deductions
 */
class PayrollCalculationService
{
    /**
     * Calculate total gross earnings for an employee on a specific date
     */
    public function calculateGrossSalary(Employee $employee, ?Carbon $date = null): float
    {
        $date = $date ?? now();
        $gross = 0;

        // Get all active earnings for the employee
        $earnings = EmployeeEarning::where('employee_id', $employee->id)
            ->activeOn($date)
            ->with('earningsType')
            ->get();

        foreach ($earnings as $earning) {
            if ($earning->earningsType->type === 'fixed') {
                $gross += $earning->amount;
            } elseif ($earning->earningsType->type === 'percentage') {
                // Percentage of basic salary
                $basicSalary = EmployeeEarning::where('employee_id', $employee->id)
                    ->whereHas('earningsType', function ($q) {
                        $q->where('name', 'Basic Salary');
                    })
                    ->activeOn($date)
                    ->value('amount') ?? $employee->basic_salary;

                $gross += ($basicSalary * $earning->percentage) / 100;
            }
        }

        return round($gross, 2);
    }

    /**
     * Calculate NSSF contribution (both employee and employer)
     */
    public function calculateNSSF(float $gross, ?Carbon $date = null): array
    {
        $date = $date ?? now();

        $rates = StatutoryRate::activeBandsFor('NSSF', $date);

        $tierI = 0;
        $tierII = 0;

        // Tier I: up to 7,000 at 6%
        $tierIRate = collect($rates)->firstWhere('rate_type', 'NSSF_TIER_I');
        if ($tierIRate) {
            $tierILimit = $tierIRate['ceiling'] ?? 7000;
            $tierIPercentage = $tierIRate['percentage'] ?? 0.06;
            $tierI = min($gross, $tierILimit) * ($tierIPercentage / 100);
        } else {
            // Fallback to hard-coded if rates not found
            $tierI = min($gross, 7000) * 0.06;
        }

        // Tier II: 7,001 to 36,000 at 6%
        $tierIIRate = collect($rates)->firstWhere('rate_type', 'NSSF_TIER_II');
        if ($tierIIRate) {
            $tierIIFloor = $tierIIRate['floor'] ?? 7000;
            $tierIICeiling = $tierIIRate['ceiling'] ?? 36000;
            $tierIIPercentage = $tierIIRate['percentage'] ?? 0.06;
            $tierII = max(0, min($gross, $tierIICeiling) - $tierIIFloor) * ($tierIIPercentage / 100);
        } else {
            $tierII = max(0, min($gross, 36000) - 7000) * 0.06;
        }

        $totalNSSF = round($tierI + $tierII, 2);

        return [
            'employee' => $totalNSSF,
            'employer' => $totalNSSF,
            'total'    => $totalNSSF * 2,
        ];
    }

    /**
     * Calculate taxable income (gross - NSSF employee)
     */
    public function calculateTaxableIncome(float $gross, float $nssfEmployee): float
    {
        return max(0, $gross - $nssfEmployee);
    }

    /**
     * Calculate PAYE tax with personal relief
     */
    public function calculatePAYE(float $taxable, ?Carbon $date = null): float
    {
        $date = $date ?? now();

        // Get PAYE tax bands from statutory rates
        $bands = StatutoryRate::where('rate_type', 'PAYE_BAND')
            ->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $date);
            })
            ->orderBy('floor')
            ->get();

        $tax = 0;

        if ($bands->isNotEmpty()) {
            foreach ($bands as $band) {
                if ($taxable <= 0) break;

                $bandFloor = $band->floor ?? 0;
                $bandCeiling = $band->ceiling ?? PHP_INT_MAX;
                $rate = $band->percentage / 100;

                if ($taxable > $bandFloor) {
                    $chunk = min($taxable - $bandFloor, $bandCeiling - $bandFloor);
                    $tax += $chunk * $rate;
                }
            }
        } else {
            // Fallback to hard-coded bands
            $tax = $this->calculatePAYELegacy($taxable);
        }

        // Get personal relief
        $relief = StatutoryRate::where('rate_type', 'PAYE_RELIEF')
            ->activeFor('PAYE_RELIEF', $date)
            ->first();

        $personalRelief = $relief ? $relief->amount : 2400;

        return max(0, round($tax - $personalRelief, 2));
    }

    /**
     * Fallback PAYE calculation (legacy)
     */
    private function calculatePAYELegacy(float $taxable): float
    {
        $tax = 0;
        $bands = [
            [24000,  0.10],
            [8333,   0.25],
            [467667, 0.30],
            [300000, 0.325],
        ];

        foreach ($bands as [$limit, $rate]) {
            if ($taxable <= 0) break;
            $chunk = min($taxable, $limit);
            $tax   += $chunk * $rate;
            $taxable -= $chunk;
        }

        if ($taxable > 0) {
            $tax += $taxable * 0.35;
        }

        return round($tax, 2);
    }

    /**
     * Calculate SHIF contribution (replaces NHIF)
     */
    public function calculateSHIF(float $gross, ?Carbon $date = null): float
    {
        $date = $date ?? now();

        // Get SHIF bands from statutory rates
        $bands = StatutoryRate::where('rate_type', 'SHIF_BAND')
            ->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $date);
            })
            ->orderBy('ceiling')
            ->get();

        if ($bands->isNotEmpty()) {
            foreach ($bands as $band) {
                if ($gross <= $band->ceiling) {
                    return round($band->amount ?? 0, 2);
                }
            }
            // Return highest band if gross exceeds all bands
            return round($bands->last()->amount ?? 0, 2);
        }

        // Fallback to NHIF bands
        return $this->calculateNHIFLegacy($gross);
    }

    /**
     * Fallback NHIF calculation (legacy)
     */
    private function calculateNHIFLegacy(float $gross): float
    {
        $bands = [
            [5999,   150],
            [7999,   300],
            [11999,  400],
            [14999,  500],
            [19999,  600],
            [24999,  750],
            [29999,  850],
            [34999,  900],
            [39999,  950],
            [44999,  1000],
            [49999,  1100],
            [59999,  1200],
            [69999,  1300],
            [79999,  1400],
            [89999,  1500],
            [99999,  1600],
            [PHP_INT_MAX, 1700],
        ];

        foreach ($bands as [$ceiling, $amount]) {
            if ($gross <= $ceiling) return (float) $amount;
        }

        return 1700;
    }

    /**
     * Calculate Housing Levy (1.5% of gross)
     */
    public function calculateHousingLevy(float $gross, ?Carmen $date = null): float
    {
        $date = $date ?? now();

        $rate = StatutoryRate::where('rate_type', 'HOUSING_LEVY')
            ->activeFor('HOUSING_LEVY', $date);

        $percentage = $rate ? ($rate->first()->percentage / 100) : 0.015;

        return round($gross * $percentage, 2);
    }

    /**
     * Calculate total statutory deductions
     */
    public function calculateStatutoryDeductions(float $gross, float $nssfEmployee, float $paye, float $shif, float $housingLevy): float
    {
        return $nssfEmployee + $paye + $shif + $housingLevy;
    }

    /**
     * Calculate voluntary deductions for an employee
     */
    public function calculateVoluntaryDeductions(Employee $employee, ?Carbon $date = null): array
    {
        $date = $date ?? now();
        $deductions = [];
        $total = 0;

        $employeeDeductions = EmployeeDeduction::where('employee_id', $employee->id)
            ->activeOn($date)
            ->with('deductionType')
            ->get();

        foreach ($employeeDeductions as $deduction) {
            if (!$deduction->deductionType->is_statutory) {
                $amount = $deduction->deductionType->type === 'fixed'
                    ? $deduction->amount
                    : ($employee->basic_salary * $deduction->percentage) / 100;

                $deductions[$deduction->deductionType->name] = round($amount, 2);
                $total += $amount;
            }
        }

        return [
            'breakdown' => $deductions,
            'total'     => round($total, 2),
        ];
    }

    /**
     * Calculate loan repayments for an employee in a month
     */
    public function calculateLoanRepayments(Employee $employee, Carbon $month): float
    {
        $total = 0;

        $repayments = $employee->loans()
            ->where('status', 'active')
            ->with('repayments')
            ->get();

        foreach ($repayments as $loan) {
            $nextRepayment = $loan->getNextInstallment();
            if ($nextRepayment && $nextRepayment->due_date->format('Y-m') === $month->format('Y-m')) {
                $total += $nextRepayment->total_payment;
            }
        }

        return round($total, 2);
    }

    /**
     * Calculate complete payslip for an employee
     */
    public function calculatePayslip(Employee $employee, ?Carbon $date = null): array
    {
        $date = $date ?? now();

        // Calculate earnings
        $gross = $this->calculateGrossSalary($employee, $date);

        // Calculate statutory deductions
        $nssf = $this->calculateNSSF($gross, $date);
        $taxable = $this->calculateTaxableIncome($gross, $nssf['employee']);
        $paye = $this->calculatePAYE($taxable, $date);
        $shif = $this->calculateSHIF($gross, $date);
        $housingLevy = $this->calculateHousingLevy($gross, $date);

        $totalStatutory = $this->calculateStatutoryDeductions($gross, $nssf['employee'], $paye, $shif, $housingLevy);

        // Calculate voluntary deductions
        $voluntaryDeductions = $this->calculateVoluntaryDeductions($employee, $date);

        // Calculate loan repayments
        $loanRepayments = $this->calculateLoanRepayments($employee, $date);

        // Total deductions
        $totalDeductions = $totalStatutory + $voluntaryDeductions['total'] + $loanRepayments;

        // Net salary
        $netSalary = max(0, $gross - $totalDeductions);

        return [
            'employee_id'                   => $employee->id,
            'basic_salary'                  => $employee->basic_salary,
            'gross_salary'                  => $gross,
            'total_earnings'                => $gross,
            'nssf_employee'                 => $nssf['employee'],
            'nssf_employer'                 => $nssf['employer'],
            'taxable_income'                => $taxable,
            'paye'                          => $paye,
            'shif'                          => $shif,
            'housing_levy'                  => $housingLevy,
            'total_statutory_deductions'    => $totalStatutory,
            'total_voluntary_deductions'    => $voluntaryDeductions['total'],
            'loan_repayments'               => $loanRepayments,
            'total_deductions'              => $totalDeductions,
            'net_salary'                    => $netSalary,
            'earnings_breakdown'            => [], // Could be enriched with earnings details
            'deductions_breakdown'          => $voluntaryDeductions['breakdown'],
            'nhif'                          => $shif, // For backward compatibility
        ];
    }
}
