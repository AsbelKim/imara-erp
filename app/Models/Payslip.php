<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payslip extends Model
{
    protected $fillable = [
        'payroll_run_id', 'employee_id',
        'basic_salary', 'gross_salary',
        'nssf_employee', 'nssf_employer',
        'nhif', 'taxable_income', 'paye',
        'housing_levy', 'total_deductions', 'net_salary',
    ];

    protected $casts = [
        'basic_salary'     => 'decimal:2',
        'gross_salary'     => 'decimal:2',
        'nssf_employee'    => 'decimal:2',
        'nssf_employer'    => 'decimal:2',
        'nhif'             => 'decimal:2',
        'taxable_income'   => 'decimal:2',
        'paye'             => 'decimal:2',
        'housing_levy'     => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary'       => 'decimal:2',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
