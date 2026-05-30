<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\PayrollRun;
use App\Models\Payslip;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // ── Payroll Report ────────────────────────────────────────────────────

    public function payroll(Request $request)
    {
        $months      = $this->monthOptions();
        $years       = $this->yearOptions();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        $runs = PayrollRun::where('status', 'processed')
            ->orderByDesc('year')->orderByDesc('month')
            ->get();

        $selectedYear   = $request->integer('year', now()->year);
        $selectedMonth  = $request->integer('month', 0);
        $selectedDept   = $request->integer('department_id', 0);

        $query = Payslip::with(['employee.department', 'payrollRun'])
            ->whereHas('payrollRun', function ($q) use ($selectedYear, $selectedMonth) {
                $q->where('status', 'processed')->where('year', $selectedYear);
                if ($selectedMonth) $q->where('month', $selectedMonth);
            });

        if ($selectedDept) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $selectedDept));
        }

        $payslips = $query->get();

        $summary = [
            'gross'      => $payslips->sum('gross_salary'),
            'nssf'       => $payslips->sum('nssf_employee'),
            'nhif'       => $payslips->sum('nhif'),
            'paye'       => $payslips->sum('paye'),
            'housing'    => $payslips->sum('housing_levy'),
            'deductions' => $payslips->sum('total_deductions'),
            'net'        => $payslips->sum('net_salary'),
            'headcount'  => $payslips->count(),
        ];

        $byDept = $payslips->groupBy(fn($p) => $p->employee->department->name)
            ->map(fn($group) => [
                'count'  => $group->count(),
                'gross'  => $group->sum('gross_salary'),
                'net'    => $group->sum('net_salary'),
            ])->sortKeys();

        if ($request->get('export') === 'csv') {
            return $this->exportPayrollCsv($payslips, $selectedYear, $selectedMonth);
        }

        return view('hr.reports.payroll', compact(
            'payslips', 'summary', 'byDept',
            'months', 'years', 'departments', 'runs',
            'selectedYear', 'selectedMonth', 'selectedDept'
        ));
    }

    // ── Leave Report ──────────────────────────────────────────────────────

    public function leave(Request $request)
    {
        $years       = $this->yearOptions();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $leaveTypes  = LeaveType::where('is_active', true)->orderBy('name')->get();

        $selectedYear   = $request->integer('year', now()->year);
        $selectedDept   = $request->integer('department_id', 0);
        $selectedType   = $request->integer('leave_type_id', 0);
        $selectedStatus = $request->get('status', '');

        $query = LeaveRequest::with(['employee.department', 'leaveType'])
            ->whereYear('start_date', $selectedYear);

        if ($selectedDept) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $selectedDept));
        }
        if ($selectedType)   $query->where('leave_type_id', $selectedType);
        if ($selectedStatus) $query->where('status', $selectedStatus);

        $leaves = $query->orderBy('start_date', 'desc')->get();

        $summary = [
            'total'    => $leaves->count(),
            'approved' => $leaves->where('status', 'approved')->count(),
            'pending'  => $leaves->where('status', 'pending')->count(),
            'rejected' => $leaves->where('status', 'rejected')->count(),
            'days'     => $leaves->where('status', 'approved')->sum('days_requested'),
        ];

        $byType = $leaves->where('status', 'approved')
            ->groupBy(fn($l) => $l->leaveType->name)
            ->map(fn($g) => ['count' => $g->count(), 'days' => $g->sum('days_requested')])
            ->sortKeys();

        if ($request->get('export') === 'csv') {
            return $this->exportLeaveCsv($leaves, $selectedYear);
        }

        return view('hr.reports.leave', compact(
            'leaves', 'summary', 'byType',
            'years', 'departments', 'leaveTypes',
            'selectedYear', 'selectedDept', 'selectedType', 'selectedStatus'
        ));
    }

    // ── CSV exports ───────────────────────────────────────────────────────

    private function exportPayrollCsv($payslips, int $year, int $month)
    {
        $period = $month ? date('F_Y', mktime(0, 0, 0, $month, 1, $year)) : $year;
        $filename = "payroll_report_{$period}.csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($payslips) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['#', 'Employee', 'Emp No.', 'Department', 'Period', 'Gross', 'NSSF', 'NHIF', 'PAYE', 'Housing Levy', 'Total Deductions', 'Net Pay']);
            foreach ($payslips as $i => $p) {
                fputcsv($out, [
                    $i + 1,
                    $p->employee->full_name,
                    $p->employee->employee_number,
                    $p->employee->department->name,
                    $p->payrollRun->period_label,
                    number_format($p->gross_salary, 2),
                    number_format($p->nssf_employee, 2),
                    number_format($p->nhif, 2),
                    number_format($p->paye, 2),
                    number_format($p->housing_levy, 2),
                    number_format($p->total_deductions, 2),
                    number_format($p->net_salary, 2),
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportLeaveCsv($leaves, int $year)
    {
        $filename = "leave_report_{$year}.csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($leaves) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['#', 'Employee', 'Emp No.', 'Department', 'Leave Type', 'Start Date', 'End Date', 'Days', 'Status', 'Reason']);
            foreach ($leaves as $i => $l) {
                fputcsv($out, [
                    $i + 1,
                    $l->employee->full_name,
                    $l->employee->employee_number,
                    $l->employee->department->name,
                    $l->leaveType->name,
                    $l->start_date->format('d/m/Y'),
                    $l->end_date->format('d/m/Y'),
                    $l->days_requested,
                    ucfirst($l->status),
                    $l->reason ?? '',
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function monthOptions(): array
    {
        return [
            1=>'January',2=>'February',3=>'March',4=>'April',
            5=>'May',6=>'June',7=>'July',8=>'August',
            9=>'September',10=>'October',11=>'November',12=>'December',
        ];
    }

    private function yearOptions(): array
    {
        $start = 2024;
        $end   = now()->year + 1;
        return array_combine(range($start, $end), range($start, $end));
    }
}
