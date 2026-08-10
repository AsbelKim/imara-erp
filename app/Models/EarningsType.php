<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EarningsType extends Model
{
    protected $fillable = ['name', 'code', 'calculation_type', 'description', 'is_taxable', 'is_active'];

    public function employeeEarnings(): HasMany
    {
        return $this->hasMany(EmployeeEarnings::class);
    }
}
