<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\Payslip;
use App\Services\LeaveService;

class PortalController extends Controller
{
    public function __construct(private LeaveService $leaveService) {}

    public function dashboard()
    {
        $employee = auth()->user()->employee;

        if (! $employee) {
            return view('portal.no-profile');
        }

        $leaveBalances = $this->leaveService->balances($employee);

        $latestPayslip = Payslip::where('employee_id', $employee->id)
            ->with('payrollRun')
            ->latest()
            ->first();

        $recentLeaves = LeaveRequest::where('employee_id', $employee->id)
            ->with('leaveType')
            ->latest()
            ->take(3)
            ->get();

        return view('portal.dashboard', compact('employee', 'leaveBalances', 'latestPayslip', 'recentLeaves'));
    }
}
