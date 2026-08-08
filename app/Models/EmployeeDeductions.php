<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDeductions extends Model
{
    protected $fillable = ['employee_id', 'deduction_type_id', 'amount', 'effective_from', 'effective_to', 'reference', 'notes'];
    protected $casts = ['effective_from' => 'date', 'effective_to' => 'date', 'amount' => 'decimal:2'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function deductionType(): BelongsTo
    {
        return $this->belongsTo(DeductionType::class);
    }
}
