<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollAnomaly extends Model
{
    protected $table = 'payroll_anomalies';

    protected $fillable = [
        'employee_id',
        'payroll_run_id',
        'anomaly_type',
        'description',
        'expected_value',
        'actual_value',
        'variance_amount',
        'variance_percentage',
        'severity_score',
        'severity_level',
        'recommendation',
        'is_resolved',
        'resolution_notes',
        'detected_at',
        'resolved_at',
    ];

    protected $casts = [
        'expected_value' => 'decimal:2',
        'actual_value' => 'decimal:2',
        'variance_amount' => 'decimal:2',
        'variance_percentage' => 'decimal:2',
        'severity_score' => 'decimal:2',
        'is_resolved' => 'boolean',
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    public function scopeBySeverity($query, string $level)
    {
        return $query->where('severity_level', $level);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('anomaly_type', $type);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('detected_at', '>=', now()->subDays($days));
    }

    public function resolvable(): bool
    {
        return !$this->is_resolved;
    }

    public function resolve(string $notes = null): void
    {
        $this->update([
            'is_resolved' => true,
            'resolution_notes' => $notes,
            'resolved_at' => now(),
        ]);
    }

    public function isCritical(): bool
    {
        return $this->severity_level === 'critical';
    }

    public function isHigh(): bool
    {
        return in_array($this->severity_level, ['high', 'critical']);
    }
}
