<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KraExport extends Model
{
    protected $fillable = [
        'user_id',
        'export_type',
        'year',
        'month',
        'file_name',
        'file_path',
        'record_count',
        'total_amount',
        'status',
        'notes',
        'exported_at',
        'submitted_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'exported_at' => 'datetime',
        'submitted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created this export
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: filter by export type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('export_type', $type);
    }

    /**
     * Scope: filter by year and month
     */
    public function scopeForPeriod($query, int $year, ?int $month = null)
    {
        $query->where('year', $year);
        if ($month) {
            $query->where('month', $month);
        }
        return $query;
    }

    /**
     * Scope: filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get human-readable export type label
     */
    public function getTypeLabel(): string
    {
        return match ($this->export_type) {
            'p10_list' => 'P10 Payroll List',
            'nssf_contributions' => 'NSSF Contributions',
            'shif_contributions' => 'SHIF Contributions',
            'paye_summary' => 'PAYE Summary',
            default => ucfirst(str_replace('_', ' ', $this->export_type)),
        };
    }

    /**
     * Get human-readable status label
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'generated' => 'Generated',
            'submitted' => 'Submitted to KRA',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get period label (Month Year)
     */
    public function getPeriodLabel(): string
    {
        if ($this->month) {
            $monthName = date('F', mktime(0, 0, 0, $this->month, 1));
            return "{$monthName} {$this->year}";
        }
        return (string) $this->year;
    }
}
