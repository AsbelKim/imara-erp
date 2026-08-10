<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PredictionHistory extends Model
{
    protected $fillable = [
        'hr_prediction_id',
        'prediction_value',
        'confidence_score',
        'notes',
        'recorded_at',
    ];

    protected $casts = [
        'prediction_value' => 'decimal:2',
        'confidence_score' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function hrPrediction(): BelongsTo
    {
        return $this->belongsTo(HRPrediction::class);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('recorded_at', '>=', now()->subDays($days));
    }
}
