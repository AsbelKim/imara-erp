<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Services\LeaveService;
use Illuminate\Http\Request;

class PortalLeaveController extends Controller
{
    public function __construct(private LeaveService $leaveService) {}

    public function index()
    {
        $employee      = $this->employee();
        $leaveBalances = $this->leaveService->balances($employee);
        $leaveTypes    = LeaveType::where('is_active', true)->orderBy('name')->get();

        $leaves = LeaveRequest::where('employee_id', $employee->id)
            ->with('leaveType')
            ->latest()
            ->paginate(10);

        return view('portal.leaves.index', compact('employee', 'leaveBalances', 'leaveTypes', 'leaves'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date'    => ['required', 'date', 'after_or_equal:today'],
            'end_date'      => ['required', 'date', 'after_or_equal:start_date'],
            'reason'        => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->leaveService->apply($this->employee(), $data);
            return back()->with('success', 'Leave request submitted. You will be notified once reviewed.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function cancel(LeaveRequest $leaveRequest)
    {
        $employee = $this->employee();

        abort_unless($leaveRequest->employee_id === $employee->id, 403);
        abort_unless($leaveRequest->status === 'pending', 403);

        $leaveRequest->update(['status' => 'cancelled']);

        return back()->with('success', 'Leave request cancelled.');
    }

    private function employee()
    {
        $emp = auth()->user()->employee;
        abort_unless($emp, 403, 'No employee profile linked.');
        return $emp;
    }
}
