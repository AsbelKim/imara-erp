<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryRecommendation extends Model
{
    protected $fillable = [
        'employee_id',
        'salary_benchmark_id',
        'status',
        'current_salary',
        'recommended_salary',
        'salary_increase',
        'increase_percentage',
        'justification',
        'comparison_data',
        'recommendation_notes',
        'years_of_experience',
        'recommended_at',
        'approved_at',
        'implemented_at',
        'approved_by',
        'approval_notes',
    ];

    protected $casts = [
        'current_salary' => 'decimal:2',
        'recommended_salary' => 'decimal:2',
        'salary_increase' => 'decimal:2',
        'increase_percentage' => 'decimal:2',
        'comparison_data' => 'json',
        'recommended_at' => 'datetime',
        'approved_at' => 'datetime',
        'implemented_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function benchmark(): BelongsTo
    {
        return $this->belongsTo(SalaryBenchmark::class, 'salary_benchmark_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeRecommended($query)
    {
        return $query->where('status', 'recommended');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeImplemented($query)
    {
        return $query->where('status', 'implemented');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'recommended', 'approved']);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isImplemented(): bool
    {
        return $this->status === 'implemented';
    }

    public function approve(int $userId, string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $userId,
            'approval_notes' => $notes,
        ]);
    }

    public function implement(): void
    {
        $this->update([
            'status' => 'implemented',
            'implemented_at' => now(),
        ]);
    }

    public function reject(): void
    {
        $this->update(['status' => 'rejected']);
    }

    public function getComparisonFactor(string $key, $default = null)
    {
        return $this->comparison_data[$key] ?? $default;
    }
}
