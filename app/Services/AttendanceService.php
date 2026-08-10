<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * AttendanceService handles attendance tracking and reporting
 */
class AttendanceService
{
    /**
     * Record check-in for an employee
     */
    public function checkIn(Employee $employee, ?Carbon $time = null): AttendanceRecord
    {
        $time = $time ?? now();
        $date = $time->toDateString();

        $record = AttendanceRecord::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'date'        => $date,
            ],
            [
                'check_in' => $time,
                'status'   => 'present',
            ]
        );

        if (!$record->wasRecentlyCreated) {
            $record->update(['check_in' => $time]);
        }

        return $record;
    }

    /**
     * Record check-out for an employee
     */
    public function checkOut(Employee $employee, ?Carbon $time = null): AttendanceRecord
    {
        $time = $time ?? now();
        $date = $time->toDateString();

        $record = AttendanceRecord::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'date'        => $date,
            ],
            [
                'status' => 'present',
            ]
        );

        $record->update(['check_out' => $time]);
        $record->calculateHours();
        $record->save();

        return $record;
    }

    /**
     * Mark attendance for a date
     */
    public function markAttendance(
        Employee $employee,
        Carbon $date,
        string $status,
        ?string $remarks = null
    ): AttendanceRecord {
        return AttendanceRecord::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'date'        => $date,
            ],
            [
                'status'  => $status,
                'remarks' => $remarks,
            ]
        );
    }

    /**
     * Get attendance for a date range
     */
    public function getAttendanceForRange(
        Employee $employee,
        Carbon $from,
        Carbon $to
    ): Collection {
        return AttendanceRecord::where('employee_id', $employee->id)
            ->dateRange($from, $to)
            ->orderBy('date')
            ->get();
    }

    /**
     * Get attendance summary for a period
     */
    public function getAttendanceSummary(
        Employee $employee,
        Carbon $from,
        Carbon $to
    ): array {
        $records = $this->getAttendanceForRange($employee, $from, $to);

        $summary = [
            'total_days'       => $records->count(),
            'present_days'     => $records->where('status', 'present')->count(),
            'absent_days'      => $records->where('status', 'absent')->count(),
            'late_days'        => $records->where('status', 'late')->count(),
            'early_leave_days' => $records->where('status', 'early_leave')->count(),
            'on_leave_days'    => $records->where('status', 'on_leave')->count(),
            'total_hours'      => $records->sum('hours_worked'),
            'total_overtime'   => $records->sum('overtime_hours'),
        ];

        return $summary;
    }

    /**
     * Get department attendance report
     */
    public function getDepartmentAttendanceReport(
        int $departmentId,
        Carbon $from,
        Carbon $to
    ): array {
        $employees = Employee::where('department_id', $departmentId)
            ->where('status', 'active')
            ->get();

        $report = [];

        foreach ($employees as $employee) {
            $report[$employee->id] = $this->getAttendanceSummary($employee, $from, $to);
        }

        return $report;
    }

    /**
     * Mark late arrival
     */
    public function markLate(Employee $employee, Carbon $date, Carbon $checkInTime): AttendanceRecord
    {
        $record = AttendanceRecord::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'date'        => $date,
            ]
        );

        $record->update([
            'check_in' => $checkInTime,
            'status'   => 'late',
        ]);

        return $record;
    }

    /**
     * Mark early leave
     */
    public function markEarlyLeave(Employee $employee, Carbon $date, Carbon $checkOutTime): AttendanceRecord
    {
        $record = AttendanceRecord::where('employee_id', $employee->id)
            ->where('date', $date)
            ->firstOrFail();

        $record->update([
            'check_out' => $checkOutTime,
            'status'    => 'early_leave',
        ]);

        $record->calculateHours();
        $record->save();

        return $record;
    }

    /**
     * Calculate overtime hours for an employee in a period
     */
    public function calculateOvertimeHours(
        Employee $employee,
        Carbon $from,
        Carbon $to,
        float $dailyHourLimit = 8
    ): float {
        $records = $this->getAttendanceForRange($employee, $from, $to);

        return $records->reduce(function ($carry, $record) use ($dailyHourLimit) {
            $overtimeHours = max(0, ($record->hours_worked ?? 0) - $dailyHourLimit);
            return $carry + $overtimeHours;
        }, 0);
    }
}
