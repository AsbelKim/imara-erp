<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Payslip;
use Barryvdh\DomPDF\Facade\Pdf;

class PortalPayslipController extends Controller
{
    public function index()
    {
        $employee = $this->employee();

        $payslips = Payslip::where('employee_id', $employee->id)
            ->with('payrollRun')
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('portal.payslips.index', compact('employee', 'payslips'));
    }

    public function show(Payslip $payslip)
    {
        $employee = $this->employee();
        abort_unless($payslip->employee_id === $employee->id, 403);

        $payslip->load(['employee.department', 'payrollRun']);

        return view('portal.payslips.show', compact('payslip', 'employee'));
    }

    public function download(Payslip $payslip)
    {
        $employee = $this->employee();
        abort_unless($payslip->employee_id === $employee->id, 403);

        $payslip->load(['employee.department', 'payrollRun']);
        $pdf      = Pdf::loadView('hr.payroll.payslip-pdf', compact('payslip'));
        $run      = $payslip->payrollRun;
        $filename = "payslip_{$employee->employee_number}_{$run->year}_{$run->month}.pdf";

        return $pdf->download($filename);
    }

    private function employee()
    {
        $emp = auth()->user()->employee;
        abort_unless($emp, 403, 'No employee profile linked.');
        return $emp;
    }
}
