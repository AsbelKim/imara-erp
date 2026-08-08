<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Services\LoanService;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    private LoanService $loanService;

    public function __construct(LoanService $loanService)
    {
        $this->middleware(['auth', 'verified']);
        $this->loanService = $loanService;
    }

    /**
     * Display all loans
     */
    public function index(Request $request)
    {
        $query = Loan::query();

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $loans = $query->with('employee')
                      ->orderBy('created_at', 'desc')
                      ->paginate(50);

        $employees = Employee::where('status', 'active')->get();

        return view('hr.loans.index', compact('loans', 'employees'));
    }

    /**
     * Show create loan form
     */
    public function create(Employee $employee = null)
    {
        $employees = Employee::where('status', 'active')->get();

        return view('hr.loans.create', compact('employee', 'employees'));
    }

    /**
     * Store new loan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id'          => 'required|exists:employees,id',
            'principal_amount'     => 'required|numeric|min:1000',
            'monthly_installment'  => 'required|numeric|min:100',
            'term_months'          => 'required|integer|min:1|max:60',
            'interest_rate'        => 'nullable|numeric|min:0|max:20',
            'reason'               => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        $loan = $this->loanService->createLoan(
            $employee,
            $validated['principal_amount'],
            $validated['monthly_installment'],
            $validated['term_months'],
            $validated['interest_rate'] ?? 0,
            $validated['reason'],
            auth()->id()
        );

        return redirect()->route('loans.show', $loan)
                       ->with('success', 'Loan created successfully');
    }

    /**
     * Show loan details
     */
    public function show(Loan $loan)
    {
        $loan->load('employee', 'approvedBy', 'repayments');
        $statement = $this->loanService->getLoanStatement($loan);

        return view('hr.loans.show', compact('loan', 'statement'));
    }

    /**
     * Show edit loan form
     */
    public function edit(Loan $loan)
    {
        return view('hr.loans.edit', compact('loan'));
    }

    /**
     * Update loan
     */
    public function update(Request $request, Loan $loan)
    {
        if ($loan->status === 'active') {
            return redirect()->back()->with('error', 'Cannot edit active loans');
        }

        $validated = $request->validate([
            'reason' => 'nullable|string',
        ]);

        $loan->update($validated);

        return redirect()->route('loans.show', $loan)
                       ->with('success', 'Loan updated successfully');
    }

    /**
     * Suspend loan
     */
    public function suspend(Request $request, Loan $loan)
    {
        $this->loanService->suspendLoan($loan, $request->reason);

        return redirect()->back()->with('success', 'Loan suspended successfully');
    }

    /**
     * Resume loan
     */
    public function resume(Loan $loan)
    {
        $this->loanService->resumeLoan($loan);

        return redirect()->back()->with('success', 'Loan resumed successfully');
    }

    /**
     * Record repayment
     */
    public function recordRepayment(Request $request, LoanRepayment $repayment)
    {
        $validated = $request->validate([
            'payment_date' => 'required|date',
        ]);

        $this->loanService->recordRepayment($repayment, $validated['payment_date']);

        return redirect()->back()->with('success', 'Repayment recorded successfully');
    }

    /**
     * Show employee loans
     */
    public function employeeLoans(Employee $employee)
    {
        $loans = $this->loanService->getActiveLoans($employee);
        $totalMonthlyRepayments = $this->loanService->getTotalMonthlyRepayments($employee);

        return view('hr.loans.employee', compact('employee', 'loans', 'totalMonthlyRepayments'));
    }

    /**
     * Loan statement for employee
     */
    public function employeeStatement(Employee $employee)
    {
        $loans = $employee->loans()->get();

        $statements = [];
        foreach ($loans as $loan) {
            $statements[$loan->id] = $this->loanService->getLoanStatement($loan);
        }

        return view('hr.loans.employee-statement', compact('employee', 'statements'));
    }
}
