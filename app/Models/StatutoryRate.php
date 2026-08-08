<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatutoryRate extends Model
{
    protected $fillable = [
        'rate_type', 'year', 'month', 'percentage', 'fixed_amount',
        'ceiling', 'floor', 'relief_amount', 'description', 'effective_date',
    ];

    protected $casts = [
        'effective_date' => 'datetime',
        'percentage' => 'decimal:2',
        'fixed_amount' => 'decimal:2',
        'ceiling' => 'decimal:2',
        'floor' => 'decimal:2',
        'relief_amount' => 'decimal:2',
    ];

    public static function getRate($rateType, $year, $month = 1)
    {
        return self::where('rate_type', $rateType)
            ->where('year', $year)
            ->where('month', '<=', $month)
            ->orderByDesc('month')
            ->first();
    }
}
