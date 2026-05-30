<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\PayrollRun;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalEmployees   = Employee::where('status', 'active')->count();
        $pendingLeaves    = LeaveRequest::where('status', 'pending')->count();
        $lastPayroll      = PayrollRun::orderByDesc('year')->orderByDesc('month')->first();
        $recentLeaves     = LeaveRequest::with(['employee', 'leaveType'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalEmployees', 'pendingLeaves', 'lastPayroll', 'recentLeaves'
        ));
    }
}
