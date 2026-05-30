<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('department')->withTrashed(false);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_number', 'like', "%{$search}%");
            });
        }

        if ($dept = $request->get('department_id')) {
            $query->where('department_id', $dept);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $employees   = $query->orderBy('first_name')->paginate(15)->withQueryString();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('hr.employees.index', compact('employees', 'departments'));
    }

    public function create()
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return view('hr.employees.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'department_id'   => 'required|exists:departments,id',
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'email'           => 'required|email|unique:employees,email',
            'phone'           => 'nullable|string|max:20',
            'job_title'       => 'nullable|string|max:100',
            'national_id'     => 'nullable|string|max:20',
            'kra_pin'         => 'nullable|string|max:20',
            'nssf_number'     => 'nullable|string|max:20',
            'nhif_number'     => 'nullable|string|max:20',
            'gender'          => 'nullable|in:male,female,other',
            'date_of_birth'   => 'nullable|date|before:today',
            'hire_date'       => 'required|date',
            'employment_type' => 'required|in:full_time,part_time,contract',
            'basic_salary'    => 'required|numeric|min:0',
            'bank_name'       => 'nullable|string|max:100',
            'bank_account'    => 'nullable|string|max:50',
            'address'         => 'nullable|string|max:500',
        ]);

        $data['employee_number'] = $this->generateEmployeeNumber();

        $defaultPassword = 'Imara@' . now()->year;

        $user = User::create([
            'name'                 => $data['first_name'] . ' ' . $data['last_name'],
            'email'                => $data['email'],
            'password'             => Hash::make($defaultPassword),
            'must_change_password' => true,
        ]);

        $user->assignRole(Role::where('name', 'Employee')->firstOrFail());

        $data['user_id'] = $user->id;

        Employee::create($data);

        return redirect()->route('hr.employees.credentials')
            ->with('new_employee_name', $data['first_name'] . ' ' . $data['last_name'])
            ->with('new_employee_email', $data['email'])
            ->with('new_employee_password', $defaultPassword);
    }

    public function credentials()
    {
        if (! session('new_employee_email')) {
            return redirect()->route('hr.employees.index');
        }

        return view('hr.employees.credentials');
    }

    public function show(Employee $employee)
    {
        $employee->load('department', 'leaveRequests.leaveType', 'payslips.payrollRun');
        return view('hr.employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return view('hr.employees.edit', compact('employee', 'departments'));
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'department_id'   => 'required|exists:departments,id',
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'email'           => ['required', 'email', Rule::unique('employees', 'email')->ignore($employee->id)],
            'phone'           => 'nullable|string|max:20',
            'job_title'       => 'nullable|string|max:100',
            'national_id'     => 'nullable|string|max:20',
            'kra_pin'         => 'nullable|string|max:20',
            'nssf_number'     => 'nullable|string|max:20',
            'nhif_number'     => 'nullable|string|max:20',
            'gender'          => 'nullable|in:male,female,other',
            'date_of_birth'   => 'nullable|date|before:today',
            'hire_date'       => 'required|date',
            'employment_type' => 'required|in:full_time,part_time,contract',
            'status'          => 'required|in:active,inactive,terminated',
            'basic_salary'    => 'required|numeric|min:0',
            'bank_name'       => 'nullable|string|max:100',
            'bank_account'    => 'nullable|string|max:50',
            'address'         => 'nullable|string|max:500',
        ]);

        $employee->update($data);

        return redirect()->route('hr.employees.show', $employee)
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('hr.employees.index')
            ->with('success', 'Employee removed successfully.');
    }

    private function generateEmployeeNumber(): string
    {
        $last = Employee::withTrashed()->orderBy('id', 'desc')->first();
        $next = $last ? (int) substr($last->employee_number, 3) + 1 : 1;
        return 'EMP' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
