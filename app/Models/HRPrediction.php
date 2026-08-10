<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HRPrediction extends Model
{
    protected $fillable = [
        'employee_id',
        'prediction_type',
        'prediction_value',
        'confidence_score',
        'factors',
        'interpretation',
        'prediction_date',
        'forecast_period_start',
        'forecast_period_end',
        'recommendation',
        'status',
    ];

    protected $casts = [
        'prediction_value' => 'decimal:2',
        'confidence_score' => 'decimal:2',
        'factors' => 'json',
        'prediction_date' => 'date',
        'forecast_period_start' => 'date',
        'forecast_period_end' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(PredictionHistory::class);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('prediction_type', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeHighConfidence($query, float $threshold = 70)
    {
        return $query->where('confidence_score', '>=', $threshold);
    }

    public function scopeTurnoverRisk($query)
    {
        return $query->where('prediction_type', 'turnover')->orderByDesc('prediction_value');
    }

    public function scopeHighRisk($query, float $threshold = 60)
    {
        return $query->where('prediction_type', 'turnover')
            ->where('prediction_value', '>=', $threshold);
    }

    public function isHighRisk(): bool
    {
        return $this->prediction_type === 'turnover' && $this->prediction_value >= 60;
    }

    public function isHighConfidence(): bool
    {
        return $this->confidence_score >= 70;
    }

    public function getFactor(string $key, $default = null)
    {
        return $this->factors[$key] ?? $default;
    }

    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }
}
