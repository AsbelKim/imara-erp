<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryBenchmark extends Model
{
    protected $fillable = [
        'department_id',
        'job_title',
        'job_grade',
        'experience_level',
        'education_level',
        'market_minimum',
        'market_average',
        'market_maximum',
        'company_average',
        'sample_size',
        'data_source_date',
        'data_source',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'market_minimum' => 'decimal:2',
        'market_average' => 'decimal:2',
        'market_maximum' => 'decimal:2',
        'company_average' => 'decimal:2',
        'sample_size' => 'integer',
        'is_active' => 'boolean',
        'data_source_date' => 'date',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(SalaryRecommendation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByJobTitle($query, string $jobTitle)
    {
        return $query->where('job_title', $jobTitle);
    }

    public function scopeByDepartment($query, int $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByExperience($query, string $level)
    {
        return $query->where('experience_level', $level);
    }

    public function scopeRecent($query, int $days = 180)
    {
        return $query->where('data_source_date', '>=', now()->subDays($days));
    }

    public function isWithinRange(float $salary): bool
    {
        return $salary >= $this->market_minimum && $salary <= $this->market_maximum;
    }

    public function getRangePercentile(float $salary): float
    {
        if ($this->market_maximum === $this->market_minimum) {
            return 50;
        }

        $percentile = (($salary - $this->market_minimum) / ($this->market_maximum - $this->market_minimum)) * 100;
        return min(100, max(0, $percentile));
    }

    public function getSalaryGap(float $salary): array
    {
        $difference = $salary - $this->market_average;

        return [
            'difference' => round($difference, 2),
            'percentage' => $this->market_average > 0 ? round(($difference / $this->market_average) * 100, 2) : 0,
            'status' => $difference > 0 ? 'above_market' : ($difference < 0 ? 'below_market' : 'at_market'),
        ];
    }
}
