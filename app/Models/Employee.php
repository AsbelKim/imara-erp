<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'department_id', 'user_id', 'employee_number', 'first_name', 'last_name',
        'email', 'phone', 'national_id', 'kra_pin', 'nssf_number', 'nhif_number',
        'gender', 'date_of_birth', 'hire_date', 'termination_date',
        'employment_type', 'status', 'basic_salary', 'bank_name', 'bank_account', 'address',
        'job_title', 'emergency_contact_name', 'emergency_contact_phone', 'profile_photo',
    ];

    protected $casts = [
        'date_of_birth'    => 'date',
        'hire_date'        => 'date',
        'termination_date' => 'date',
        'basic_salary'     => 'decimal:2',
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function employeeEarnings(): HasMany
    {
        return $this->hasMany(EmployeeEarning::class);
    }

    public function employeeDeductions(): HasMany
    {
        return $this->hasMany(EmployeeDeduction::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }
}
