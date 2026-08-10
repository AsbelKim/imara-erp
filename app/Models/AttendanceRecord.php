<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'employee_id', 'attendance_date', 'check_in', 'check_out', 'status',
        'hours_worked', 'overtime_hours', 'notes', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in' => 'time',
        'check_out' => 'time',
        'approved_at' => 'datetime',
        'hours_worked' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
