<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Departments
        $departments = [
            ['name' => 'Human Resources',   'code' => 'HR',  'description' => 'HR & People Management'],
            ['name' => 'Finance',            'code' => 'FIN', 'description' => 'Finance & Accounts'],
            ['name' => 'Information Technology', 'code' => 'ICT', 'description' => 'IT & Systems'],
            ['name' => 'Operations',         'code' => 'OPS', 'description' => 'Day-to-day operations'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['code' => $dept['code']], $dept);
        }

        $hrDept  = Department::where('code', 'HR')->first();
        $finDept = Department::where('code', 'FIN')->first();
        $ictDept = Department::where('code', 'ICT')->first();
        $opsDept = Department::where('code', 'OPS')->first();

        // Leave Types
        $leaveTypes = [
            ['name' => 'Annual Leave',      'days_per_year' => 21, 'requires_approval' => true,  'is_active' => true],
            ['name' => 'Sick Leave',         'days_per_year' => 10, 'requires_approval' => false, 'is_active' => true],
            ['name' => 'Maternity Leave',    'days_per_year' => 90, 'requires_approval' => true,  'is_active' => true],
            ['name' => 'Paternity Leave',    'days_per_year' => 14, 'requires_approval' => true,  'is_active' => true],
            ['name' => 'Compassionate Leave','days_per_year' => 3,  'requires_approval' => true,  'is_active' => true],
        ];

        foreach ($leaveTypes as $lt) {
            LeaveType::firstOrCreate(['name' => $lt['name']], $lt);
        }

        $hrRole  = Role::where('name', 'HR Manager')->first();
        $empRole = Role::where('name', 'Employee')->first();

        // Employees with linked user accounts
        $employees = [
            [
                'first_name' => 'Jane', 'last_name' => 'Wanjiku',
                'email' => 'jane.wanjiku@imaralogic.co.ke',
                'department_id' => $hrDept->id, 'basic_salary' => 85000,
                'kra_pin' => 'A001234567B', 'nssf_number' => 'NS00112233',
                'nhif_number' => 'NH00112233', 'hire_date' => '2022-03-01',
                'gender' => 'female', 'employment_type' => 'full_time', 'role' => 'HR Manager',
            ],
            [
                'first_name' => 'Brian', 'last_name' => 'Otieno',
                'email' => 'brian.otieno@imaralogic.co.ke',
                'department_id' => $ictDept->id, 'basic_salary' => 120000,
                'kra_pin' => 'A002345678C', 'nssf_number' => 'NS00223344',
                'nhif_number' => 'NH00223344', 'hire_date' => '2021-06-15',
                'gender' => 'male', 'employment_type' => 'full_time', 'role' => 'Employee',
            ],
            [
                'first_name' => 'Grace', 'last_name' => 'Muthoni',
                'email' => 'grace.muthoni@imaralogic.co.ke',
                'department_id' => $finDept->id, 'basic_salary' => 65000,
                'kra_pin' => 'A003456789D', 'nssf_number' => 'NS00334455',
                'nhif_number' => 'NH00334455', 'hire_date' => '2023-01-10',
                'gender' => 'female', 'employment_type' => 'full_time', 'role' => 'Employee',
            ],
            [
                'first_name' => 'Kevin', 'last_name' => 'Kamau',
                'email' => 'kevin.kamau@imaralogic.co.ke',
                'department_id' => $opsDept->id, 'basic_salary' => 42000,
                'kra_pin' => 'A004567890E', 'nssf_number' => 'NS00445566',
                'nhif_number' => 'NH00445566', 'hire_date' => '2023-07-20',
                'gender' => 'male', 'employment_type' => 'full_time', 'role' => 'Employee',
            ],
            [
                'first_name' => 'Amina', 'last_name' => 'Hassan',
                'email' => 'amina.hassan@imaralogic.co.ke',
                'department_id' => $ictDept->id, 'basic_salary' => 95000,
                'kra_pin' => 'A005678901F', 'nssf_number' => 'NS00556677',
                'nhif_number' => 'NH00556677', 'hire_date' => '2020-11-05',
                'gender' => 'female', 'employment_type' => 'full_time', 'role' => 'Employee',
            ],
        ];

        $empNumber = 1;
        foreach ($employees as $data) {
            $roleSlug = $data['role'];
            unset($data['role']);

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                ['name' => "{$data['first_name']} {$data['last_name']}", 'password' => Hash::make('Password@1234')]
            );

            $role = Role::where('name', $roleSlug)->first();
            if ($role && !$user->hasRole($role)) {
                $user->assignRole($role);
            }

            $emp = Employee::firstOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'employee_number' => 'EMP' . str_pad($empNumber, 4, '0', STR_PAD_LEFT),
                    'status' => 'active',
                    'user_id' => $user->id,
                ])
            );

            $empNumber++;
        }

        // Sample pending leave request
        $annualLeave = LeaveType::where('name', 'Annual Leave')->first();
        $brian = Employee::where('email', 'brian.otieno@imaralogic.co.ke')->first();

        LeaveRequest::firstOrCreate(
            ['employee_id' => $brian->id, 'leave_type_id' => $annualLeave->id, 'start_date' => '2026-06-10'],
            [
                'end_date'       => '2026-06-14',
                'days_requested' => 5,
                'reason'         => 'Family vacation',
                'status'         => 'pending',
            ]
        );
    }
}
