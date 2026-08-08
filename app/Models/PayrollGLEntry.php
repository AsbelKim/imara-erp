<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollGLEntry extends Model
{
    protected $table = 'payroll_gl_entries';
    protected $fillable = [
        'payroll_run_id', 'account_code', 'account_name', 'entry_type', 'amount',
        'description', 'reference', 'posting_date', 'is_posted', 'posted_by', 'posted_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'posting_date' => 'datetime',
        'posted_at' => 'datetime',
        'is_posted' => 'boolean',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}
