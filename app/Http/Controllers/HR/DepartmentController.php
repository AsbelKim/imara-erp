<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('employees')->orderBy('name')->get();
        return view('hr.departments.index', compact('departments'));
    }

    public function create()
    {
        return view('hr.departments.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:departments,name',
            'code'        => 'required|string|max:20|unique:departments,code',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        Department::create($data);

        return redirect()->route('hr.departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        return view('hr.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100', Rule::unique('departments', 'name')->ignore($department->id)],
            'code'        => ['required', 'string', 'max:20', Rule::unique('departments', 'code')->ignore($department->id)],
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $department->update($data);

        return redirect()->route('hr.departments.index')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        if ($department->employees()->exists()) {
            return back()->with('error', 'Cannot delete a department with active employees.');
        }

        $department->delete();

        return redirect()->route('hr.departments.index')
            ->with('success', 'Department deleted.');
    }
}
