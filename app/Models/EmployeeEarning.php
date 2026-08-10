<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class EmployeeEarning extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'earnings_type_id',
        'amount',
        'percentage',
        'effective_from',
        'effective_to',
        'reason',
        'is_active',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to'   => 'date',
        'amount'         => 'decimal:2',
        'percentage'     => 'decimal:2',
        'is_active'      => 'boolean',
    ];

    /**
     * Get employee
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get earnings type
     */
    public function earningsType(): BelongsTo
    {
        return $this->belongsTo(EarningsType::class);
    }

    /**
     * Get active earnings for a specific date
     */
    public function scopeActiveOn($query, ?Carbon $date = null)
    {
        $date = $date ?? now();

        return $query->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $date);
            })
            ->where('is_active', true);
    }

    /**
     * Scope to active records
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
