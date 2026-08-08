<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatutoryLiability extends Model
{
    protected $fillable = [
        'payroll_run_id', 'liability_type', 'employee_amount', 'employer_amount',
        'total_amount', 'payment_status', 'due_date', 'paid_date', 'notes',
    ];

    protected $casts = [
        'employee_amount' => 'decimal:2',
        'employer_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }
}
