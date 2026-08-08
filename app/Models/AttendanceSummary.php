<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSummary extends Model
{
    protected $fillable = [
        'employee_id', 'year', 'month', 'total_days', 'present_days', 'absent_days',
        'late_days', 'half_days', 'total_overtime_hours',
    ];

    protected $casts = ['total_overtime_hours' => 'decimal:2'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
