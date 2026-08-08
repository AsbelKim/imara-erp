<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    private AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->middleware(['auth', 'verified']);
        $this->attendanceService = $attendanceService;
    }

    /**
     * Display attendance records
     */
    public function index(Request $request)
    {
        $query = AttendanceRecord::query();

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('date')) {
            $query->where('date', $request->date);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('date', [$request->from_date, $request->to_date]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $records = $query->orderBy('date', 'desc')
                        ->paginate(50);

        $employees = Employee::where('status', 'active')->get();

        return view('hr.attendance.index', compact('records', 'employees'));
    }

    /**
     * Show check-in/check-out form
     */
    public function checkInOut()
    {
        return view('hr.attendance.check-in-out');
    }

    /**
     * Process check-in
     */
    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $this->attendanceService->checkIn($employee);

        return redirect()->back()->with('success', "{$employee->full_name} checked in successfully");
    }

    /**
     * Process check-out
     */
    public function checkOut(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $this->attendanceService->checkOut($employee);

        return redirect()->back()->with('success', "{$employee->full_name} checked out successfully");
    }

    /**
     * Mark attendance manually
     */
    public function mark(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'required|date',
            'status'      => 'required|in:present,absent,late,early_leave,on_leave,weekend,holiday',
            'remarks'     => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $this->attendanceService->markAttendance(
            $employee,
            Carbon::parse($validated['date']),
            $validated['status'],
            $validated['remarks']
        );

        return redirect()->back()->with('success', 'Attendance marked successfully');
    }

    /**
     * Show attendance summary for employee
     */
    public function summary(Employee $employee, Request $request)
    {
        $from = $request->from_date ? Carbon::parse($request->from_date) : now()->startOfMonth();
        $to = $request->to_date ? Carbon::parse($request->to_date) : now()->endOfMonth();

        $summary = $this->attendanceService->getAttendanceSummary($employee, $from, $to);
        $records = $this->attendanceService->getAttendanceForRange($employee, $from, $to);

        return view('hr.attendance.summary', compact('employee', 'summary', 'records', 'from', 'to'));
    }

    /**
     * Export attendance report
     */
    public function export(Request $request)
    {
        $from = $request->from_date ? Carbon::parse($request->from_date) : now()->startOfMonth();
        $to = $request->to_date ? Carbon::parse($request->to_date) : now()->endOfMonth();

        $records = AttendanceRecord::whereBetween('date', [$from, $to])
            ->with('employee')
            ->orderBy('date')
            ->get();

        // Export to CSV or Excel
        // TODO: Implement export functionality

        return response()->download('attendance-report.xlsx');
    }

    /**
     * Department attendance report
     */
    public function departmentReport(Request $request)
    {
        $from = $request->from_date ? Carbon::parse($request->from_date) : now()->startOfMonth();
        $to = $request->to_date ? Carbon::parse($request->to_date) : now()->endOfMonth();

        if (!$request->filled('department_id')) {
            return redirect()->back()->with('error', 'Please select a department');
        }

        $summary = $this->attendanceService->getDepartmentAttendanceReport(
            $request->department_id,
            $from,
            $to
        );

        return view('hr.attendance.department-report', compact('summary', 'from', 'to'));
    }
}
