<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'loan_number',
        'principal_amount',
        'outstanding_balance',
        'interest_rate',
        'term_months',
        'monthly_installment',
        'start_date',
        'end_date',
        'status',
        'reason',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'principal_amount'      => 'decimal:2',
        'outstanding_balance'   => 'decimal:2',
        'interest_rate'         => 'decimal:2',
        'monthly_installment'   => 'decimal:2',
        'start_date'            => 'date',
        'end_date'              => 'date',
        'approved_at'           => 'datetime',
    ];

    /**
     * Get employee
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get approver
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get loan repayments
     */
    public function repayments(): HasMany
    {
        return $this->hasMany(LoanRepayment::class);
    }

    /**
     * Get active loans
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get completed loans
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Calculate remaining balance based on repayments
     */
    public function getRemainingBalance(): float
    {
        $totalPaid = $this->repayments()
            ->where('status', '!=', 'waived')
            ->sum('total_payment');

        return $this->principal_amount - $totalPaid;
    }

    /**
     * Get next installment
     */
    public function getNextInstallment(): ?LoanRepayment
    {
        return $this->repayments()
            ->where('status', '!=', 'paid')
            ->orderBy('installment_number')
            ->first();
    }

    /**
     * Calculate interest accrued
     */
    public function calculateInterestAccrued(): float
    {
        if (!$this->interest_rate || $this->status === 'completed') {
            return 0;
        }

        $monthsElapsed = $this->start_date->diffInMonths(now());
        $monthlyInterest = ($this->principal_amount * $this->interest_rate) / 100 / 12;

        return $monthlyInterest * $monthsElapsed;
    }
}
