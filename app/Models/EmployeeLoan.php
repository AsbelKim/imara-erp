<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeLoan extends Model
{
    protected $fillable = [
        'employee_id', 'loan_type', 'loan_amount', 'interest_rate', 'loan_term_months',
        'disbursement_date', 'expected_completion_date', 'actual_completion_date',
        'monthly_installment', 'total_repaid', 'outstanding_balance', 'status',
        'approved_by', 'approved_at', 'remarks',
    ];

    protected $casts = [
        'disbursement_date' => 'date',
        'expected_completion_date' => 'date',
        'actual_completion_date' => 'date',
        'approved_at' => 'datetime',
        'loan_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'monthly_installment' => 'decimal:2',
        'total_repaid' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(LoanRepayment::class);
    }

    public function guarantors(): HasMany
    {
        return $this->hasMany(LoanGuarantor::class);
    }
}
