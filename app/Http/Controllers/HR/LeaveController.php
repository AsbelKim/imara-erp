<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Services\LeaveService;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function __construct(private LeaveService $leaveService) {}

    public function index(Request $request)
    {
        $query = LeaveRequest::with(['employee.department', 'leaveType'])
            ->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $leaves = $query->paginate(15)->withQueryString();
        return view('hr.leaves.index', compact('leaves'));
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        $this->leaveService->approve($leaveRequest, auth()->id());
        return back()->with('success', 'Leave request approved.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate(['rejection_reason' => 'required|string|max:300']);
        $this->leaveService->reject($leaveRequest, auth()->id(), $request->rejection_reason);
        return back()->with('success', 'Leave request rejected.');
    }
}
