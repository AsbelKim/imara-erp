<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRepayment extends Model
{
    protected $fillable = [
        'employee_loan_id', 'installment_number', 'due_date', 'principal_amount',
        'interest_amount', 'installment_amount', 'amount_paid', 'paid_date', 'status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
        'principal_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    public function employeeLoan(): BelongsTo
    {
        return $this->belongsTo(EmployeeLoan::class);
    }
}
