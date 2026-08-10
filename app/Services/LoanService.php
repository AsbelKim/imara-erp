<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Loan;
use App\Models\LoanRepayment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * LoanService handles employee loan management and repayment scheduling
 */
class LoanService
{
    /**
     * Create a new loan for an employee
     */
    public function createLoan(
        Employee $employee,
        float $principalAmount,
        float $monthlyInstallment,
        int $termMonths,
        float $interestRate = 0,
        ?string $reason = null,
        ?int $approvedBy = null
    ): Loan {
        $loanNumber = $this->generateLoanNumber();

        $loan = Loan::create([
            'employee_id'        => $employee->id,
            'loan_number'        => $loanNumber,
            'principal_amount'   => $principalAmount,
            'outstanding_balance' => $principalAmount,
            'interest_rate'      => $interestRate,
            'term_months'        => $termMonths,
            'monthly_installment' => $monthlyInstallment,
            'start_date'         => now(),
            'status'             => 'active',
            'reason'             => $reason,
            'approved_by'        => $approvedBy ?? auth()->id(),
            'approved_at'        => now(),
        ]);

        // Schedule repayments
        $this->scheduleRepayments($loan, $monthlyInstallment, $interestRate);

        return $loan;
    }

    /**
     * Schedule loan repayments
     */
    private function scheduleRepayments(Loan $loan, float $monthlyInstallment, float $interestRate): void
    {
        $dueDate = $loan->start_date->copy()->addMonth();
        $principalPerMonth = $loan->principal_amount / $loan->term_months;

        for ($i = 1; $i <= $loan->term_months; $i++) {
            $interestPayment = ($loan->principal_amount - ($principalPerMonth * ($i - 1))) * ($interestRate / 100 / 12);

            LoanRepayment::create([
                'loan_id'              => $loan->id,
                'installment_number'   => $i,
                'due_date'             => $dueDate,
                'principal_payment'    => $principalPerMonth,
                'interest_payment'     => $interestPayment,
                'total_payment'        => $principalPerMonth + $interestPayment,
                'status'               => 'pending',
            ]);

            $dueDate = $dueDate->addMonth();
        }
    }

    /**
     * Record a loan repayment
     */
    public function recordRepayment(LoanRepayment $repayment, ?Carbon $paymentDate = null, ?int $payslipId = null): LoanRepayment
    {
        $repayment->update([
            'payment_date' => $paymentDate ?? now(),
            'status'       => 'paid',
            'payslip_id'   => $payslipId,
        ]);

        // Update loan outstanding balance
        $loan = $repayment->loan;
        $loan->outstanding_balance = $loan->getRemainingBalance();
        $loan->save();

        // Close loan if all repayments are paid
        if ($loan->repayments()->where('status', '!=', 'paid')->where('status', '!=', 'waived')->count() === 0) {
            $loan->update([
                'status'   => 'completed',
                'end_date' => now(),
            ]);
        }

        return $repayment;
    }

    /**
     * Get active loans for an employee
     */
    public function getActiveLoans(Employee $employee): Collection
    {
        return $employee->loans()->where('status', 'active')->get();
    }

    /**
     * Get total monthly loan repayments for an employee
     */
    public function getTotalMonthlyRepayments(Employee $employee): float
    {
        return $this->getActiveLoans($employee)
            ->reduce(function ($carry, $loan) {
                return $carry + $loan->monthly_installment;
            }, 0);
    }

    /**
     * Get pending repayments for an employee in a specific month
     */
    public function getPendingRepaymentsForMonth(Employee $employee, Carbon $month): Collection
    {
        return LoanRepayment::whereHas('loan', function ($query) use ($employee) {
            $query->where('employee_id', $employee->id);
        })
            ->where('status', 'pending')
            ->whereMonth('due_date', $month->month)
            ->whereYear('due_date', $month->year)
            ->get();
    }

    /**
     * Check if a loan repayment is overdue
     */
    public function isRepaymentOverdue(LoanRepayment $repayment): bool
    {
        return $repayment->status === 'pending' && $repayment->due_date < now()->toDateString();
    }

    /**
     * Get overdue repayments for an employee
     */
    public function getOverdueRepayments(Employee $employee): Collection
    {
        return LoanRepayment::whereHas('loan', function ($query) use ($employee) {
            $query->where('employee_id', $employee->id);
        })
            ->where('status', 'pending')
            ->where('due_date', '<', now()->toDateString())
            ->get();
    }

    /**
     * Suspend a loan
     */
    public function suspendLoan(Loan $loan, ?string $reason = null): Loan
    {
        $loan->update([
            'status' => 'suspended',
        ]);

        AuditLogService::logAction(
            'suspended',
            $loan,
            "Loan suspended: {$reason}"
        );

        return $loan;
    }

    /**
     * Resume a suspended loan
     */
    public function resumeLoan(Loan $loan): Loan
    {
        $loan->update([
            'status' => 'active',
        ]);

        AuditLogService::logAction('resumed', $loan, 'Loan resumed');

        return $loan;
    }

    /**
     * Write off a loan repayment
     */
    public function writeOffRepayment(LoanRepayment $repayment, ?string $reason = null): LoanRepayment
    {
        $repayment->update([
            'status'   => 'waived',
            'remarks'  => $reason,
        ]);

        return $repayment;
    }

    /**
     * Generate unique loan number
     */
    private function generateLoanNumber(): string
    {
        $prefix = 'LN';
        $date = now()->format('Ymd');
        $count = Loan::whereDate('created_at', now())->count() + 1;

        return "{$prefix}-{$date}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get loan statement for an employee
     */
    public function getLoanStatement(Loan $loan): array
    {
        $repayments = $loan->repayments()->orderBy('installment_number')->get();

        $statement = [
            'loan' => $loan,
            'repayments' => [],
            'summary' => [
                'principal_amount'    => $loan->principal_amount,
                'paid_amount'         => $repayments->where('status', 'paid')->sum('total_payment'),
                'outstanding_balance' => $loan->outstanding_balance,
                'pending_installments' => $repayments->where('status', 'pending')->count(),
                'overdue_amount'      => $repayments->where('status', 'overdue')->sum('total_payment'),
            ],
        ];

        foreach ($repayments as $repayment) {
            $statement['repayments'][] = [
                'installment_number'  => $repayment->installment_number,
                'due_date'            => $repayment->due_date,
                'payment_date'        => $repayment->payment_date,
                'principal_payment'   => $repayment->principal_payment,
                'interest_payment'    => $repayment->interest_payment,
                'total_payment'       => $repayment->total_payment,
                'status'              => $repayment->status,
            ];
        }

        return $statement;
    }
}
