<?php

namespace App\Services;

use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\PayrollGLEntry;
use Illuminate\Support\Collection;

/**
 * PayrollGLService handles GL entry generation for payroll
 * Maps payroll components to GL accounts automatically
 */
class PayrollGLService
{
    /**
     * GL Account mapping configuration
     */
    private array $accountMapping = [
        'salary_expense'           => '5010',
        'nssf_payable'             => '2110',
        'nssf_employer'            => '5020',
        'paye_payable'             => '2100',
        'shif_payable'             => '2105',
        'housing_levy_payable'     => '2115',
        'salary_receivable'        => '1020', // Advance salary
        'loan_repayment_payable'   => '2120',
        'employee_deduction_payable' => '2125',
    ];

    /**
     * Generate GL entries for a payroll run
     */
    public function generateGLEntriesForRun(PayrollRun $payrollRun): Collection
    {
        $entries = collect();

        // Group payslips by component to create summary GL entries
        $payslips = $payrollRun->payslips()->get();

        // Total salary expense
        $totalGrossSalary = $payslips->sum('gross_salary');
        $entries->push($this->createGLEntry(
            $payrollRun,
            null,
            'debit',
            $totalGrossSalary,
            $this->accountMapping['salary_expense'],
            'Salary Expense',
            "Salary Expense - {$payrollRun->month}/{$payrollRun->year}"
        ));

        // NSSF Employee contribution payable
        $totalNSSFEmployee = $payslips->sum('nssf_employee');
        if ($totalNSSFEmployee > 0) {
            $entries->push($this->createGLEntry(
                $payrollRun,
                null,
                'credit',
                $totalNSSFEmployee,
                $this->accountMapping['nssf_payable'],
                'NSSF Payable',
                "NSSF Employee Contribution - {$payrollRun->month}/{$payrollRun->year}"
            ));
        }

        // NSSF Employer contribution expense
        $totalNSSFEmployer = $payslips->sum('nssf_employer');
        if ($totalNSSFEmployer > 0) {
            $entries->push($this->createGLEntry(
                $payrollRun,
                null,
                'debit',
                $totalNSSFEmployer,
                $this->accountMapping['nssf_employer'],
                'NSSF Employer Contribution',
                "NSSF Employer Contribution - {$payrollRun->month}/{$payrollRun->year}"
            ));

            $entries->push($this->createGLEntry(
                $payrollRun,
                null,
                'credit',
                $totalNSSFEmployer,
                $this->accountMapping['nssf_payable'],
                'NSSF Payable',
                "NSSF Employer Contribution - {$payrollRun->month}/{$payrollRun->year}"
            ));
        }

        // PAYE payable
        $totalPAYE = $payslips->sum('paye');
        if ($totalPAYE > 0) {
            $entries->push($this->createGLEntry(
                $payrollRun,
                null,
                'credit',
                $totalPAYE,
                $this->accountMapping['paye_payable'],
                'PAYE Payable',
                "PAYE Tax - {$payrollRun->month}/{$payrollRun->year}"
            ));
        }

        // SHIF payable
        $totalSHIF = $payslips->sum('shif');
        if ($totalSHIF > 0) {
            $entries->push($this->createGLEntry(
                $payrollRun,
                null,
                'credit',
                $totalSHIF,
                $this->accountMapping['shif_payable'],
                'SHIF Payable',
                "SHIF Insurance - {$payrollRun->month}/{$payrollRun->year}"
            ));
        }

        // Housing Levy payable
        $totalHousingLevy = $payslips->sum('housing_levy');
        if ($totalHousingLevy > 0) {
            $entries->push($this->createGLEntry(
                $payrollRun,
                null,
                'credit',
                $totalHousingLevy,
                $this->accountMapping['housing_levy_payable'],
                'Housing Levy Payable',
                "Housing Levy - {$payrollRun->month}/{$payrollRun->year}"
            ));
        }

        // Net salary (salary clearing account or bank)
        $totalNetSalary = $payslips->sum('net_salary');
        if ($totalNetSalary > 0) {
            $entries->push($this->createGLEntry(
                $payrollRun,
                null,
                'credit',
                $totalNetSalary,
                '1100', // Bank/Cash account - configurable
                'Bank - Salary Disbursement',
                "Net Salary - {$payrollRun->month}/{$payrollRun->year}"
            ));
        }

        // Save all entries
        foreach ($entries as $entry) {
            $entry->save();
        }

        return $entries;
    }

    /**
     * Generate GL entries for a single payslip
     */
    public function generateGLEntriesForPayslip(PayrollRun $payrollRun, Payslip $payslip): Collection
    {
        $entries = collect();

        // Individual salary expense
        $entries->push($this->createGLEntry(
            $payrollRun,
            $payslip,
            'debit',
            $payslip->gross_salary,
            $this->accountMapping['salary_expense'],
            'Salary Expense',
            "Salary - {$payslip->employee->full_name}"
        ));

        // Deduction GL entries
        if ($payslip->nssf_employee > 0) {
            $entries->push($this->createGLEntry(
                $payrollRun,
                $payslip,
                'credit',
                $payslip->nssf_employee,
                $this->accountMapping['nssf_payable'],
                'NSSF Payable',
                "NSSF - {$payslip->employee->full_name}"
            ));
        }

        if ($payslip->paye > 0) {
            $entries->push($this->createGLEntry(
                $payrollRun,
                $payslip,
                'credit',
                $payslip->paye,
                $this->accountMapping['paye_payable'],
                'PAYE Payable',
                "PAYE - {$payslip->employee->full_name}"
            ));
        }

        if ($payslip->shif > 0) {
            $entries->push($this->createGLEntry(
                $payrollRun,
                $payslip,
                'credit',
                $payslip->shif,
                $this->accountMapping['shif_payable'],
                'SHIF Payable',
                "SHIF - {$payslip->employee->full_name}"
            ));
        }

        if ($payslip->housing_levy > 0) {
            $entries->push($this->createGLEntry(
                $payrollRun,
                $payslip,
                'credit',
                $payslip->housing_levy,
                $this->accountMapping['housing_levy_payable'],
                'Housing Levy Payable',
                "Housing Levy - {$payslip->employee->full_name}"
            ));
        }

        // Save all entries
        foreach ($entries as $entry) {
            $entry->save();
        }

        return $entries;
    }

    /**
     * Create a GL entry instance
     */
    private function createGLEntry(
        PayrollRun $payrollRun,
        ?Payslip $payslip,
        string $entryType,
        float $amount,
        string $accountCode,
        string $accountName,
        string $description
    ): PayrollGLEntry {
        return new PayrollGLEntry([
            'payroll_run_id'    => $payrollRun->id,
            'payslip_id'        => $payslip?->id,
            'gl_account_code'   => $accountCode,
            'gl_account_name'   => $accountName,
            'entry_type'        => $entryType,
            'amount'            => $amount,
            'description'       => $description,
            'reference'         => "PAYROLL-{$payrollRun->month}-{$payrollRun->year}",
            'status'            => 'pending',
        ]);
    }

    /**
     * Post GL entries for a payroll run
     */
    public function postGLEntries(PayrollRun $payrollRun): bool
    {
        $entries = PayrollGLEntry::where('payroll_run_id', $payrollRun->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'posted',
            ]);

        return $entries > 0;
    }

    /**
     * Reverse GL entries for a payroll run
     */
    public function reverseGLEntries(PayrollRun $payrollRun): bool
    {
        $entries = PayrollGLEntry::where('payroll_run_id', $payrollRun->id)
            ->where('status', 'posted')
            ->get();

        foreach ($entries as $entry) {
            // Create reversing entry
            new PayrollGLEntry([
                'payroll_run_id'    => $payrollRun->id,
                'payslip_id'        => $entry->payslip_id,
                'gl_account_code'   => $entry->gl_account_code,
                'gl_account_name'   => $entry->gl_account_name,
                'entry_type'        => $entry->entry_type === 'debit' ? 'credit' : 'debit',
                'amount'            => $entry->amount,
                'description'       => "REVERSAL: {$entry->description}",
                'reference'         => "REVERSAL-{$entry->reference}",
                'status'            => 'posted',
            ])->save();

            // Mark original as reversed
            $entry->update(['status' => 'reversed']);
        }

        return true;
    }

    /**
     * Get GL account mapping
     */
    public function getAccountMapping(): array
    {
        return $this->accountMapping;
    }

    /**
     * Set custom GL account mapping
     */
    public function setAccountMapping(array $mapping): void
    {
        $this->accountMapping = array_merge($this->accountMapping, $mapping);
    }

    /**
     * Get GL entries for a payroll run
     */
    public function getGLEntriesForRun(PayrollRun $payrollRun): Collection
    {
        return $payrollRun->glEntries()->get();
    }

    /**
     * Get GL trial balance for payroll
     */
    public function getTrialBalance(PayrollRun $payrollRun): array
    {
        $entries = $this->getGLEntriesForRun($payrollRun);

        $balance = [];

        foreach ($entries as $entry) {
            $key = $entry->gl_account_code;
            if (!isset($balance[$key])) {
                $balance[$key] = [
                    'code'       => $entry->gl_account_code,
                    'name'       => $entry->gl_account_name,
                    'debit'      => 0,
                    'credit'     => 0,
                ];
            }

            if ($entry->entry_type === 'debit') {
                $balance[$key]['debit'] += $entry->amount;
            } else {
                $balance[$key]['credit'] += $entry->amount;
            }
        }

        return $balance;
    }
}
