<?php

namespace App\Services;

use App\Models\PayrollAnomaly;
use App\Models\Payslip;
use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\StatutoryRate;
use Carbon\Carbon;

/**
 * PayrollGuardianService detects anomalies in payroll data
 *
 * Features:
 * - Detect unusual salary changes (>20% from baseline)
 * - Detect duplicate payments
 * - Verify PAYE calculations match statutory requirements
 * - Verify NSSF calculations (6% Tier I up to 9k, Tier II up to 108k)
 * - Verify SHIF contributions
 * - Verify Housing Levy (1.5%)
 * - Flag compliance violations
 */
class PayrollGuardianService
{
    public function __construct(private PayrollCalculationService $payrollService) {}

    /**
     * Scan a payroll run for all anomalies
     */
    public function scanPayrollRun(PayrollRun $payrollRun): void
    {
        // Clear previous anomalies for this run
        PayrollAnomaly::where('payroll_run_id', $payrollRun->id)->delete();

        $payslips = Payslip::where('payroll_run_id', $payrollRun->id)
            ->with('employee')
            ->get();

        foreach ($payslips as $payslip) {
            $this->detectSalaryChangeAnomalies($payslip);
            $this->detectDuplicatePayments($payslip);
            $this->detectPAYEAnomalies($payslip);
            $this->detectNSSFAnomalies($payslip);
            $this->detectSHIFAnomalies($payslip);
            $this->detectHousingLevyAnomalies($payslip);
        }
    }

    /**
     * Detect unusual salary changes (>20% from baseline)
     */
    protected function detectSalaryChangeAnomalies(Payslip $payslip): void
    {
        $employee = $payslip->employee;

        // Get previous payslip from 3 months ago
        $previousPayslip = Payslip::where('employee_id', $employee->id)
            ->where('payroll_run_id', '!=', $payslip->payroll_run_id)
            ->with('payrollRun')
            ->orderByDesc('created_at')
            ->first();

        if (!$previousPayslip) {
            return; // No previous data to compare
        }

        $currentGross = (float) $payslip->gross_salary;
        $previousGross = (float) $previousPayslip->gross_salary;

        if ($previousGross === 0) {
            return;
        }

        $percentageChange = abs(($currentGross - $previousGross) / $previousGross) * 100;

        if ($percentageChange > 20) {
            $severity = $this->calculateSeverityForSalaryChange($percentageChange);

            PayrollAnomaly::create([
                'employee_id' => $employee->id,
                'payroll_run_id' => $payslip->payroll_run_id,
                'anomaly_type' => 'salary_change',
                'description' => "Salary changed by {$percentageChange}% compared to previous period",
                'expected_value' => $previousGross,
                'actual_value' => $currentGross,
                'variance_amount' => $currentGross - $previousGross,
                'variance_percentage' => $percentageChange,
                'severity_score' => $severity,
                'severity_level' => $this->getSeverityLevel($severity),
                'recommendation' => 'Verify this is an approved salary adjustment or bonus',
                'detected_at' => now(),
            ]);
        }
    }

    /**
     * Detect duplicate payments for same employee
     */
    protected function detectDuplicatePayments(Payslip $payslip): void
    {
        // Check if this employee has multiple payslips in the same month
        $count = Payslip::where('employee_id', $payslip->employee_id)
            ->where('payroll_run_id', $payslip->payroll_run_id)
            ->count();

        if ($count > 1) {
            PayrollAnomaly::create([
                'employee_id' => $payslip->employee_id,
                'payroll_run_id' => $payslip->payroll_run_id,
                'anomaly_type' => 'duplicate_payment',
                'description' => "Employee has {$count} payslips for the same period",
                'expected_value' => 1,
                'actual_value' => $count,
                'variance_amount' => $count - 1,
                'severity_score' => 95,
                'severity_level' => 'critical',
                'recommendation' => 'Verify and remove duplicate payslips immediately',
                'detected_at' => now(),
            ]);
        }
    }

    /**
     * Detect PAYE calculation errors
     */
    protected function detectPAYEAnomalies(Payslip $payslip): void
    {
        $employee = $payslip->employee;
        $date = $payslip->payrollRun->getPayrollDate();

        // Recalculate expected PAYE
        $gross = (float) $payslip->gross_salary;
        $nssfEmployee = (float) $payslip->nssf_employee;

        $calculatedTaxableIncome = $this->payrollService->calculateTaxableIncome($gross, $nssfEmployee);
        $calculatedPAYE = $this->payrollService->calculatePAYE($calculatedTaxableIncome, $date);

        $actualPAYE = (float) $payslip->paye;
        $difference = abs($calculatedPAYE - $actualPAYE);
        $percentageDiff = $calculatedPAYE > 0 ? ($difference / $calculatedPAYE) * 100 : 0;

        // Allow small tolerance for rounding (0.5%)
        if ($percentageDiff > 0.5) {
            PayrollAnomaly::create([
                'employee_id' => $employee->id,
                'payroll_run_id' => $payslip->payroll_run_id,
                'anomaly_type' => 'paye_mismatch',
                'description' => "PAYE calculation differs by {$percentageDiff}% from statutory requirement",
                'expected_value' => $calculatedPAYE,
                'actual_value' => $actualPAYE,
                'variance_amount' => $difference,
                'variance_percentage' => $percentageDiff,
                'severity_score' => $this->calculateSeverityForTaxError($difference, $actualPAYE),
                'severity_level' => $difference > 1000 ? 'high' : 'medium',
                'recommendation' => 'Verify PAYE calculation against statutory tax tables',
                'detected_at' => now(),
            ]);
        }
    }

    /**
     * Detect NSSF calculation errors
     * Tier I: 6% up to KES 9,000 (employee + employer)
     * Tier II: 6% from KES 9,001 to KES 108,000 (voluntary)
     */
    protected function detectNSSFAnomalies(Payslip $payslip): void
    {
        $gross = (float) $payslip->gross_salary;
        $actualNSSF = (float) $payslip->nssf_employee;

        // Calculate expected NSSF
        $tierI = min($gross, 9000) * 0.06;
        $tierII = max(0, min($gross, 108000) - 9000) * 0.06;
        $expectedNSSF = $tierI + $tierII;

        $difference = abs($expectedNSSF - $actualNSSF);
        $percentageDiff = $expectedNSSF > 0 ? ($difference / $expectedNSSF) * 100 : 0;

        if ($percentageDiff > 1) { // Allow 1% tolerance
            PayrollAnomaly::create([
                'employee_id' => $payslip->employee_id,
                'payroll_run_id' => $payslip->payroll_run_id,
                'anomaly_type' => 'nssf_error',
                'description' => "NSSF contribution differs by {$percentageDiff}% from statutory requirement (Tier I: 6% up to 9k, Tier II: 6% up to 108k)",
                'expected_value' => $expectedNSSF,
                'actual_value' => $actualNSSF,
                'variance_amount' => $difference,
                'variance_percentage' => $percentageDiff,
                'severity_score' => $this->calculateSeverityForDeduction($difference),
                'severity_level' => $percentageDiff > 5 ? 'high' : 'medium',
                'recommendation' => 'Verify NSSF contribution calculation against current rates',
                'detected_at' => now(),
            ]);
        }
    }

    /**
     * Detect SHIF (formerly NHIF) calculation errors
     */
    protected function detectSHIFAnomalies(Payslip $payslip): void
    {
        $employee = $payslip->employee;

        // SHIF is now managed by Payroll system
        // Check if SHIF/NHIF rate is missing when employee has SHIF enabled
        if ($employee->shif_number && $payslip->nhif == 0) {
            PayrollAnomaly::create([
                'employee_id' => $employee->id,
                'payroll_run_id' => $payslip->payroll_run_id,
                'anomaly_type' => 'shif_error',
                'description' => "Employee has SHIF number but no SHIF contribution recorded",
                'expected_value' => 300, // Minimum typical SHIF
                'actual_value' => 0,
                'variance_amount' => 300,
                'variance_percentage' => 100,
                'severity_score' => 75,
                'severity_level' => 'high',
                'recommendation' => 'Verify SHIF deduction configuration for this employee',
                'detected_at' => now(),
            ]);
        }
    }

    /**
     * Detect Housing Levy errors (1.5% of gross)
     */
    protected function detectHousingLevyAnomalies(Payslip $payslip): void
    {
        $gross = (float) $payslip->gross_salary;
        $actualHousingLevy = (float) $payslip->housing_levy;
        $expectedHousingLevy = $gross * 0.015; // 1.5%

        $difference = abs($expectedHousingLevy - $actualHousingLevy);
        $percentageDiff = $expectedHousingLevy > 0 ? ($difference / $expectedHousingLevy) * 100 : 0;

        if ($percentageDiff > 1) {
            PayrollAnomaly::create([
                'employee_id' => $payslip->employee_id,
                'payroll_run_id' => $payslip->payroll_run_id,
                'anomaly_type' => 'housing_levy_error',
                'description' => "Housing Levy differs by {$percentageDiff}% from expected 1.5% of gross",
                'expected_value' => $expectedHousingLevy,
                'actual_value' => $actualHousingLevy,
                'variance_amount' => $difference,
                'variance_percentage' => $percentageDiff,
                'severity_score' => $this->calculateSeverityForDeduction($difference),
                'severity_level' => $percentageDiff > 5 ? 'high' : 'medium',
                'recommendation' => 'Verify Housing Levy is calculated at 1.5% of gross salary',
                'detected_at' => now(),
            ]);
        }
    }

    /**
     * Calculate severity score for salary changes
     */
    protected function calculateSeverityForSalaryChange(float $percentageChange): float
    {
        // 20-50% = 40-60 points
        // 50-100% = 60-80 points
        // >100% = 80-100 points
        if ($percentageChange > 100) {
            return min(100, 80 + ($percentageChange - 100) / 10);
        } elseif ($percentageChange > 50) {
            return 60 + (($percentageChange - 50) / 50) * 20;
        } else {
            return 40 + (($percentageChange - 20) / 30) * 20;
        }
    }

    /**
     * Calculate severity for tax errors
     */
    protected function calculateSeverityForTaxError(float $difference, float $actual): float
    {
        if ($actual === 0) {
            return 85;
        }
        $ratio = $difference / $actual;
        return min(100, 50 + ($ratio * 50));
    }

    /**
     * Calculate severity for deduction errors
     */
    protected function calculateSeverityForDeduction(float $difference): float
    {
        // Scale based on absolute difference
        if ($difference > 5000) {
            return 90;
        } elseif ($difference > 1000) {
            return 70;
        } elseif ($difference > 500) {
            return 50;
        } else {
            return 30;
        }
    }

    /**
     * Get severity level from score
     */
    protected function getSeverityLevel(float $score): string
    {
        if ($score >= 80) {
            return 'critical';
        } elseif ($score >= 60) {
            return 'high';
        } elseif ($score >= 40) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Get all unresolved anomalies
     */
    public function getUnresolvedAnomalies(?int $limit = null)
    {
        $query = PayrollAnomaly::unresolved()
            ->orderByDesc('severity_score')
            ->orderByDesc('detected_at')
            ->with('employee', 'payrollRun');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get anomalies summary by severity
     */
    public function getAnomaliesSummary(): array
    {
        $unresolved = PayrollAnomaly::unresolved();

        return [
            'total' => $unresolved->count(),
            'critical' => $unresolved->whereSeverityLevel('critical')->count(),
            'high' => $unresolved->whereSeverityLevel('high')->count(),
            'medium' => $unresolved->whereSeverityLevel('medium')->count(),
            'low' => $unresolved->whereSeverityLevel('low')->count(),
        ];
    }
}
