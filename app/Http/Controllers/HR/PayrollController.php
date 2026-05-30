<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Services\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function __construct(private PayrollService $payrollService) {}

    public function index()
    {
        $runs = PayrollRun::orderByDesc('year')->orderByDesc('month')->paginate(12);
        return view('hr.payroll.index', compact('runs'));
    }

    public function create()
    {
        return view('hr.payroll.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'month' => 'required|integer|between:1,12',
            'year'  => 'required|integer|min:2020|max:2099',
        ]);

        if (PayrollRun::where('month', $data['month'])->where('year', $data['year'])->exists()) {
            return back()->withErrors(['month' => 'A payroll run for this period already exists.']);
        }

        $run = PayrollRun::create(['month' => $data['month'], 'year' => $data['year']]);

        $this->payrollService->processRun($run);

        return redirect()->route('hr.payroll.show', $run)
            ->with('success', 'Payroll processed successfully.');
    }

    public function show(PayrollRun $payroll)
    {
        $payroll->load('payslips.employee.department');
        return view('hr.payroll.show', compact('payroll'));
    }

    public function void(Request $request, PayrollRun $payroll)
    {
        abort_unless($payroll->isVoidable(), 422, 'Only processed payroll runs can be voided.');

        $request->validate([
            'void_reason' => 'required|string|max:500',
        ]);

        $payroll->payslips()->delete();

        $payroll->update([
            'status'      => 'voided',
            'voided_by'   => auth()->id(),
            'voided_at'   => now(),
            'void_reason' => $request->void_reason,
        ]);

        return redirect()->route('hr.payroll.index')
            ->with('success', "Payroll run for {$payroll->period_label} has been voided.");
    }

    public function downloadPayslip(PayrollRun $payrollRun, Employee $employee)
    {
        $payslip = Payslip::where('payroll_run_id', $payrollRun->id)
            ->where('employee_id', $employee->id)
            ->with(['employee.department', 'payrollRun'])
            ->firstOrFail();

        $pdf = Pdf::loadView('hr.payroll.payslip-pdf', compact('payslip'));
        $filename = "payslip_{$employee->employee_number}_{$payrollRun->year}_{$payrollRun->month}.pdf";

        return $pdf->download($filename);
    }

}
