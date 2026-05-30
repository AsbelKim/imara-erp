<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeaveTypeController extends Controller
{
    public function index()
    {
        $leaveTypes = LeaveType::withCount('leaveRequests')->orderBy('name')->get();
        return view('hr.leave-types.index', compact('leaveTypes'));
    }

    public function create()
    {
        return view('hr.leave-types.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:100|unique:leave_types,name',
            'days_per_year'     => 'required|integer|min:1|max:365',
            'requires_approval' => 'boolean',
            'is_active'         => 'boolean',
        ]);

        $data['requires_approval'] = $request->boolean('requires_approval', true);
        $data['is_active']         = $request->boolean('is_active', true);

        LeaveType::create($data);

        return redirect()->route('hr.leave-types.index')
            ->with('success', 'Leave type created.');
    }

    public function edit(LeaveType $leaveType)
    {
        return view('hr.leave-types.edit', compact('leaveType'));
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:100', Rule::unique('leave_types', 'name')->ignore($leaveType->id)],
            'days_per_year'     => 'required|integer|min:1|max:365',
            'requires_approval' => 'boolean',
            'is_active'         => 'boolean',
        ]);

        $data['requires_approval'] = $request->boolean('requires_approval');
        $data['is_active']         = $request->boolean('is_active');

        $leaveType->update($data);

        return redirect()->route('hr.leave-types.index')
            ->with('success', 'Leave type updated.');
    }

    public function destroy(LeaveType $leaveType)
    {
        if ($leaveType->leaveRequests()->exists()) {
            return back()->with('error', 'Cannot delete a leave type with existing requests.');
        }
        $leaveType->delete();
        return redirect()->route('hr.leave-types.index')->with('success', 'Leave type deleted.');
    }
}
