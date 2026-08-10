<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\Payslip;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * PayrollService orchestrates payroll processing with support for:
 * - Flexible earnings and deductions
 * - Configurable statutory rates
 * - Audit logging
 * - GL entry generation
 * - Loan repayment integration
 */
class PayrollService
{
    private PayrollCalculationService $calculationService;
    private PayrollGLService $glService;
    private LoanService $loanService;

    public function __construct(
        PayrollCalculationService $calculationService,
        PayrollGLService $glService,
        LoanService $loanService
    ) {
        $this->calculationService = $calculationService;
        $this->glService = $glService;
        $this->loanService = $loanService;
    }

    /**
     * Process a complete payroll run
     */
    public function processRun(PayrollRun $run): void
    {
        // Check for duplicate runs
        $existingRun = PayrollRun::where('month', $run->month)
            ->where('year', $run->year)
            ->where('id', '!=', $run->id)
            ->where('status', '!=', 'voided')
            ->first();

        if ($existingRun) {
            throw new \Exception("Payroll already processed for {$run->month}/{$run->year}");
        }

        $employees = Employee::where('status', 'active')->get();

        DB::transaction(function () use ($run, $employees) {
            $totalGross = $totalDeductions = $totalNet = 0;
            $totalNSSFEmployer = 0;

            $processDate = Carbon::createFromDate($run->year, $run->month, 1);

            foreach ($employees as $employee) {
                $slip = $this->calculatePayslip($employee, $processDate);

                // Add payroll_run_id and employee_id
                $slip['payroll_run_id'] = $run->id;
                $slip['employee_id'] = $employee->id;

                Payslip::updateOrCreate(
                    ['payroll_run_id' => $run->id, 'employee_id' => $employee->id],
                    $slip
                );

                $totalGross           += $slip['gross_salary'];
                $totalDeductions      += $slip['total_deductions'];
                $totalNet             += $slip['net_salary'];
                $totalNSSFEmployer    += $slip['nssf_employer'];
            }

            // Update payroll run totals
            $run->update([
                'total_gross'      => $totalGross,
                'total_deductions' => $totalDeductions,
                'total_net'        => $totalNet,
                'total_nssf_employer' => $totalNSSFEmployer,
                'status'           => 'processed',
                'processed_by'     => auth()->id(),
                'processed_at'     => now(),
            ]);

            // Generate GL entries
            $this->glService->generateGLEntriesForRun($run);

            // Log the action
            AuditLogService::logAction(
                'processed',
                $run,
                "Payroll run processed for {$run->month}/{$run->year}",
                ['employee_count' => $employees->count()]
            );
        });
    }

    /**
     * Calculate complete payslip for an employee
     */
    public function calculatePayslip(Employee $employee, ?Carbon $date = null): array
    {
        $date = $date ?? now();

        return $this->calculationService->calculatePayslip($employee, $date);
    }

    /**
     * Void a payroll run
     */
    public function voidRun(PayrollRun $run, ?string $reason = null): void
    {
        DB::transaction(function () use ($run, $reason) {
            $run->update([
                'status' => 'voided',
                'voided_by' => auth()->id(),
                'voided_at' => now(),
            ]);

            // Reverse GL entries if posted
            $this->glService->reverseGLEntries($run);

            // Log the action
            AuditLogService::logAction(
                'voided',
                $run,
                "Payroll run voided: {$reason}",
                ['reason' => $reason]
            );
        });
    }

    /**
     * Regenerate payslip for single employee
     */
    public function regeneratePayslip(PayrollRun $run, Employee $employee): Payslip
    {
        $slip = $this->calculatePayslip($employee, Carbon::createFromDate($run->year, $run->month, 1));

        $payslip = Payslip::updateOrCreate(
            ['payroll_run_id' => $run->id, 'employee_id' => $employee->id],
            $slip
        );

        // Log the action
        AuditLogService::logAction(
            'regenerated',
            $payslip,
            "Payslip regenerated for {$employee->full_name}"
        );

        return $payslip;
    }

    /**
     * Get payroll summary for a period
     */
    public function getPayrollSummary(int $month, int $year): array
    {
        $run = PayrollRun::where('month', $month)
            ->where('year', $year)
            ->first();

        if (!$run) {
            return [];
        }

        $payslips = $run->payslips()->get();

        return [
            'payroll_run_id'          => $run->id,
            'period'                  => "{$month}/{$year}",
            'employee_count'          => $payslips->count(),
            'total_gross'             => $payslips->sum('gross_salary'),
            'total_nssf_employee'     => $payslips->sum('nssf_employee'),
            'total_nssf_employer'     => $payslips->sum('nssf_employer'),
            'total_paye'              => $payslips->sum('paye'),
            'total_shif'              => $payslips->sum('shif'),
            'total_housing_levy'      => $payslips->sum('housing_levy'),
            'total_statutory'         => $payslips->sum('total_statutory_deductions'),
            'total_voluntary'         => $payslips->sum('total_voluntary_deductions'),
            'total_deductions'        => $payslips->sum('total_deductions'),
            'total_net'               => $payslips->sum('net_salary'),
            'status'                  => $run->status,
            'processed_at'            => $run->processed_at,
        ];
    }

    /**
     * Get GL trial balance for payroll
     */
    public function getGLTrialBalance(PayrollRun $run): array
    {
        return $this->glService->getTrialBalance($run);
    }
}
