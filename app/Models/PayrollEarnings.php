<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollEarnings extends Model
{
    protected $fillable = ['payroll_run_id', 'employee_id', 'earnings_type_id', 'amount'];
    protected $casts = ['amount' => 'decimal:2'];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function earningsType(): BelongsTo
    {
        return $this->belongsTo(EarningsType::class);
    }
}
