<?php

namespace App\Services;

use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\AuditLog;
use App\Models\EmployeeEarnings;
use App\Models\DeductionType;
use App\Models\PayrollDeductions;
use App\Models\EmployeeLoan;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class EnhancedPayrollService
{
    public function processRun(PayrollRun $run): void
    {
        $employees = Employee::where('status', 'active')->get();
        DB::transaction(function () use ($run, $employees) {
            $totalGross = $totalDeductions = $totalNet = 0;
            foreach ($employees as $employee) {
                $slip = $this->computePayslip($employee, $run);
                Payslip::updateOrCreate(
                    ['payroll_run_id' => $run->id, 'employee_id' => $employee->id],
                    $slip
                );
                $totalGross      += $slip['gross_salary'];
                $totalDeductions += $slip['total_deductions'];
                $totalNet        += $slip['net_salary'];
            }
            $run->update([
                'total_gross'      => $totalGross,
                'total_deductions' => $totalDeductions,
                'total_net'        => $totalNet,
                'status'           => 'processed',
                'processed_by'     => auth()->id(),
                'processed_at'     => now(),
            ]);
            AuditLog::log('processed', 'PayrollRun', $run->id, null, ['status' => 'processed']);
        });
    }

    public function computePayslip(Employee $employee, PayrollRun $run): array
    {
        $basicSalary = (float) $employee->basic_salary;
        $gross = $basicSalary;

        // Calculate deductions
        $nssfEmployee = $this->calculateNSSF($gross);
        $taxable = max(0, $gross - $nssfEmployee);
        $paye = max(0, $this->calculatePAYE($taxable) - 2400);
        $shif = $this->calculateSHIF($gross);
        $housingLevy = round($gross * 0.015, 2);

        $totalDeductions = $nssfEmployee + $paye + $shif + $housingLevy;
        $net = max(0, $gross - $totalDeductions);

        return [
            'basic_salary' => $basicSalary,
            'gross_salary' => $gross,
            'nssf_employee' => $nssfEmployee,
            'nssf_employer' => $nssfEmployee,
            'nhif' => $shif,
            'taxable_income' => $taxable,
            'paye' => $paye,
            'housing_levy' => $housingLevy,
            'total_deductions' => $totalDeductions,
            'net_salary' => $net,
        ];
    }

    private function calculateNSSF(float $gross): float
    {
        $tierI  = min($gross, 9000) * 0.06;
        $tierII = max(0, min($gross, 108000) - 9000) * 0.06;
        return round($tierI + $tierII, 2);
    }

    private function calculatePAYE(float $taxable): float
    {
        $tax = 0;
        $bands = [[24000, 0.10], [8333, 0.25], [467667, 0.30], [300000, 0.325]];
        foreach ($bands as [$limit, $rate]) {
            if ($taxable <= 0) break;
            $chunk = min($taxable, $limit);
            $tax += $chunk * $rate;
            $taxable -= $chunk;
        }
        if ($taxable > 0) $tax += $taxable * 0.35;
        return round($tax, 2);
    }

    private function calculateSHIF(float $gross): float
    {
        $bands = [
            [5999, 150], [7999, 300], [11999, 400], [14999, 500], [19999, 600],
            [24999, 750], [29999, 850], [34999, 900], [39999, 950], [44999, 1000],
            [49999, 1100], [59999, 1200], [69999, 1300], [79999, 1400],
            [89999, 1500], [99999, 1600], [PHP_INT_MAX, 1700],
        ];
        foreach ($bands as [$ceiling, $amount]) {
            if ($gross <= $ceiling) return $amount;
        }
        return 1700;
    }
}
