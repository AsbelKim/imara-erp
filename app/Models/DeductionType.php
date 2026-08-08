<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeductionType extends Model
{
    protected $fillable = ['name', 'code', 'calculation_type', 'description', 'is_statutory', 'is_active'];

    public function employeeDeductions(): HasMany
    {
        return $this->hasMany(EmployeeDeductions::class);
    }
}
