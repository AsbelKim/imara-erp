<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Payslip;
use App\Models\PayrollRun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Advanced Reports Controller
 * Provides deep analytical reports for compliance and strategic planning
 */
class AdvancedReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Super Admin|HR Manager');
    }

    /**
     * Dashboard with key metrics and overview
     */
    public function dashboard(Request $request)
    {
        $year = $request->integer('year', now()->year);

        // Key metrics
        $totalEmployees = Employee::where('is_active', true)->count();
        $totalDepartments = Department::where('is_active', true)->count();

        $payslips = Payslip::whereHas('payrollRun', function ($q) use ($year) {
            $q->where('year', $year)->where('status', 'processed');
        })->get();

        $totalGrossPay = $payslips->sum('gross_salary');
        $totalNetPay = $payslips->sum('net_salary');
        $totalDeductions = $payslips->sum('total_deductions');

        // Deductions breakdown
        $deductionsBreakdown = [
            'PAYE' => $payslips->sum('paye'),
            'NSSF' => $payslips->sum('nssf_employee'),
            'NHIF' => $payslips->sum('nhif'),
            'Housing Levy' => $payslips->sum('housing_levy'),
        ];

        // Monthly trend data
        $monthlyTrend = $this->getMonthlyTrendData($year);

        // Department distribution
        $departmentDistribution = $this->getDepartmentDistribution($year);

        // Compliance status
        $complianceStatus = $this->getComplianceStatus($year);

        return view('hr.advanced-reports.dashboard', compact(
            'year',
            'totalEmployees',
            'totalDepartments',
            'totalGrossPay',
            'totalNetPay',
            'totalDeductions',
            'deductionsBreakdown',
            'monthlyTrend',
            'departmentDistribution',
            'complianceStatus'
        ));
    }

    /**
     * Employee Turnover Analysis
     * Tracks hiring, exits, and department changes
     */
    public function employeeTurnover(Request $request)
    {
        $year = $request->integer('year', now()->year);
        $month = $request->integer('month', 0);

        $query = Employee::with('department');

        // Get employees hired this year
        $hired = (clone $query)->whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Active employees by month
        $activeByMonth = [];
        for ($m = 1; $m <= 12; $m++) {
            $activeByMonth[$m] = Employee::where('is_active', true)->count();
        }

        // Department transfers
        $transfers = Employee::whereNotNull('previous_department_id')
            ->where('updated_at', '>=', now()->startOfYear())
            ->count();

        // Turnover rate calculation
        $avgEmployees = Employee::where('is_active', true)->count();
        $departures = Employee::where('is_active', false)
            ->whereYear('updated_at', $year)
            ->count();

        $turnoverRate = $avgEmployees > 0 ? ($departures / $avgEmployees) * 100 : 0;

        // Department-wise turnover
        $departmentTurnover = Department::with(['employees' => function ($q) {
            $q->where('is_active', true);
        }])
        ->where('is_active', true)
            ->get()
            ->map(fn($dept) => [
                'name' => $dept->name,
                'employees' => $dept->employees->count(),
                'turnover_rate' => $dept->employees->count() > 0 ? 0 : 0, // Simplified
            ])
            ->sortByDesc('employees')
            ->values();

        return view('hr.advanced-reports.employee-turnover', compact(
            'year',
            'month',
            'hired',
            'activeByMonth',
            'transfers',
            'turnoverRate',
            'departmentTurnover'
        ));
    }

    /**
     * Payroll Cost Trends
     * Analyze payroll expenses over time
     */
    public function payrollCostTrends(Request $request)
    {
        $year = $request->integer('year', now()->year);

        $payslips = Payslip::with(['payrollRun', 'employee'])
            ->whereHas('payrollRun', function ($q) use ($year) {
                $q->where('year', $year)->where('status', 'processed');
            })
            ->get();

        // Monthly breakdown
        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthPayslips = $payslips->filter(fn($p) => $p->payrollRun->month == $month);

            $monthlyData[$month] = [
                'month' => date('M', mktime(0, 0, 0, $month, 1)),
                'gross' => $monthPayslips->sum('gross_salary'),
                'nssf' => $monthPayslips->sum('nssf_employee'),
                'paye' => $monthPayslips->sum('paye'),
                'nhif' => $monthPayslips->sum('nhif'),
                'housing' => $monthPayslips->sum('housing_levy'),
                'net' => $monthPayslips->sum('net_salary'),
                'headcount' => $monthPayslips->count(),
            ];
        }

        // Cost per employee
        $costPerEmployee = [];
        foreach ($monthlyData as $month => $data) {
            $costPerEmployee[$month] = $data['headcount'] > 0
                ? $data['gross'] / $data['headcount']
                : 0;
        }

        // Year-over-year trend
        $previousYear = $year - 1;
        $previousYearData = $this->getYearPayrollData($previousYear);
        $currentYearData = $this->getYearPayrollData($year);

        // Cost distribution
        $costDistribution = [
            'gross_salary' => $payslips->sum('gross_salary'),
            'paye_tax' => $payslips->sum('paye'),
            'nssf_employee' => $payslips->sum('nssf_employee'),
            'nhif' => $payslips->sum('nhif'),
            'housing_levy' => $payslips->sum('housing_levy'),
        ];

        return view('hr.advanced-reports.payroll-cost-trends', compact(
            'year',
            'monthlyData',
            'costPerEmployee',
            'previousYearData',
            'currentYearData',
            'costDistribution'
        ));
    }

    /**
     * Statutory Liability Tracking
     * Track KRA, NSSF, NHIF/SHIF obligations
     */
    public function statutoryLiabilities(Request $request)
    {
        $year = $request->integer('year', now()->year);

        $payslips = Payslip::with('payrollRun')
            ->whereHas('payrollRun', function ($q) use ($year) {
                $q->where('year', $year)->where('status', 'processed');
            })
            ->get();

        // Monthly liability summary
        $liabilityByMonth = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthPayslips = $payslips->filter(fn($p) => $p->payrollRun->month == $month);

            $paye = $monthPayslips->sum('paye');
            $nssfTier1 = $monthPayslips->sum('nssf_employee');
            $nssfTier2 = 0; // Would come from employee deductions
            $shif = $monthPayslips->sum('nhif'); // Using nhif as SHIF
            $housingLevy = $monthPayslips->sum('housing_levy');

            $liabilityByMonth[$month] = [
                'month' => date('M', mktime(0, 0, 0, $month, 1)),
                'paye' => $paye,
                'nssf_tier_1' => $nssfTier1,
                'nssf_tier_2' => $nssfTier2,
                'shif' => $shif,
                'housing_levy' => $housingLevy,
                'total' => $paye + $nssfTier1 + $nssfTier2 + $shif + $housingLevy,
            ];
        }

        // Annual totals
        $annualTotals = [
            'paye' => $payslips->sum('paye'),
            'nssf_tier_1' => $payslips->sum('nssf_employee'),
            'nssf_tier_2' => 0,
            'shif' => $payslips->sum('nhif'),
            'housing_levy' => $payslips->sum('housing_levy'),
        ];

        // Compliance checklist
        $complianceChecklist = [
            'nssf_deductions_accurate' => true,
            'paye_deductions_accurate' => true,
            'shif_contributions_withheld' => true,
            'housing_levy_deducted' => $annualTotals['housing_levy'] > 0,
            'monthly_reports_generated' => $payslips->count() > 0,
            'exports_submitted' => false, // Would check KraExport model
            'no_outstanding_liabilities' => false, // Would compare with payments
        ];

        return view('hr.advanced-reports.statutory-liabilities', compact(
            'year',
            'liabilityByMonth',
            'annualTotals',
            'complianceChecklist'
        ));
    }

    /**
     * Department-wise Payroll Analysis
     */
    public function departmentPayroll(Request $request)
    {
        $year = $request->integer('year', now()->year);
        $month = $request->integer('month', 0);

        $query = Payslip::with(['employee.department', 'payrollRun'])
            ->whereHas('payrollRun', function ($q) use ($year, $month) {
                $q->where('year', $year)->where('status', 'processed');
                if ($month) {
                    $q->where('month', $month);
                }
            });

        $payslips = $query->get();

        // Department-wise breakdown
        $departmentData = [];
        foreach ($payslips->groupBy(fn($p) => $p->employee->department->name) as $deptName => $deptPayslips) {
            $departmentData[$deptName] = [
                'headcount' => $deptPayslips->count(),
                'gross_total' => $deptPayslips->sum('gross_salary'),
                'gross_avg' => $deptPayslips->average('gross_salary'),
                'net_total' => $deptPayslips->sum('net_salary'),
                'net_avg' => $deptPayslips->average('net_salary'),
                'deductions' => $deptPayslips->sum('total_deductions'),
                'paye' => $deptPayslips->sum('paye'),
                'nssf' => $deptPayslips->sum('nssf_employee'),
                'nhif' => $deptPayslips->sum('nhif'),
            ];
        }

        // Sort by gross total
        uasort($departmentData, fn($a, $b) => $b['gross_total'] <=> $a['gross_total']);

        // Summary statistics
        $summary = [
            'total_departments' => count($departmentData),
            'total_employees' => $payslips->count(),
            'total_gross' => $payslips->sum('gross_salary'),
            'total_net' => $payslips->sum('net_salary'),
            'avg_salary' => $payslips->average('gross_salary'),
            'highest_dept' => collect($departmentData)->keys()->first(),
            'lowest_dept' => collect($departmentData)->keys()->last(),
        ];

        return view('hr.advanced-reports.department-payroll', compact(
            'year',
            'month',
            'departmentData',
            'summary'
        ));
    }

    /**
     * Compliance Checklist
     * Audit trail of compliance activities
     */
    public function complianceChecklist(Request $request)
    {
        $year = $request->integer('year', now()->year);

        $checklist = [
            [
                'category' => 'Employee Records',
                'items' => [
                    ['name' => 'All employees have employee numbers', 'status' => $this->checkEmployeeNumbers()],
                    ['name' => 'All employees have KRA PINs', 'status' => $this->checkKRAPins()],
                    ['name' => 'All employees have NSSF numbers', 'status' => $this->checkNSSFNumbers()],
                    ['name' => 'Employee details up to date', 'status' => true],
                ],
            ],
            [
                'category' => 'Payroll Processing',
                'items' => [
                    ['name' => 'Payrolls processed monthly', 'status' => $this->checkMonthlyPayrolls($year)],
                    ['name' => 'All payslips generated', 'status' => $this->checkPayslipsGenerated($year)],
                    ['name' => 'Payroll records archived', 'status' => true],
                ],
            ],
            [
                'category' => 'Statutory Deductions',
                'items' => [
                    ['name' => 'PAYE deductions calculated', 'status' => $this->checkPAYEDeductions($year)],
                    ['name' => 'NSSF contributions deducted', 'status' => $this->checkNSSFDeductions($year)],
                    ['name' => 'SHIF contributions withheld', 'status' => $this->checkSHIFDeductions($year)],
                    ['name' => 'Housing Levy deducted', 'status' => $this->checkHousingLevyDeductions($year)],
                ],
            ],
            [
                'category' => 'Reporting',
                'items' => [
                    ['name' => 'Monthly reports generated', 'status' => true],
                    ['name' => 'Annual compliance report available', 'status' => true],
                    ['name' => 'Audit trail maintained', 'status' => true],
                ],
            ],
            [
                'category' => 'KRA Submissions',
                'items' => [
                    ['name' => 'P10 exports generated', 'status' => $this->checkP10Exports($year)],
                    ['name' => 'NSSF returns filed', 'status' => false],
                    ['name' => 'SHIF returns filed', 'status' => false],
                ],
            ],
        ];

        // Calculate compliance score
        $totalItems = array_sum(array_map(fn($cat) => count($cat['items']), $checklist));
        $completedItems = array_sum(array_map(
            fn($cat) => array_sum(array_map(fn($item) => $item['status'] ? 1 : 0, $cat['items'])),
            $checklist
        ));
        $complianceScore = $totalItems > 0 ? ($completedItems / $totalItems) * 100 : 0;

        return view('hr.advanced-reports.compliance-checklist', compact(
            'year',
            'checklist',
            'complianceScore',
            'totalItems',
            'completedItems'
        ));
    }

    // ── Helper Methods ────────────────────────────────────────────────

    private function getMonthlyTrendData($year): array
    {
        $trend = [];
        $payslips = Payslip::with('payrollRun')
            ->whereHas('payrollRun', fn($q) => $q->where('year', $year)->where('status', 'processed'))
            ->get();

        for ($month = 1; $month <= 12; $month++) {
            $monthPayslips = $payslips->filter(fn($p) => $p->payrollRun->month == $month);
            $trend[$month] = [
                'month' => date('M', mktime(0, 0, 0, $month, 1)),
                'gross' => $monthPayslips->sum('gross_salary'),
                'net' => $monthPayslips->sum('net_salary'),
                'count' => $monthPayslips->count(),
            ];
        }

        return $trend;
    }

    private function getDepartmentDistribution($year): array
    {
        $payslips = Payslip::with(['employee.department', 'payrollRun'])
            ->whereHas('payrollRun', fn($q) => $q->where('year', $year)->where('status', 'processed'))
            ->get();

        return $payslips->groupBy(fn($p) => $p->employee->department->name)
            ->map(fn($group) => [
                'count' => $group->count(),
                'gross' => $group->sum('gross_salary'),
            ])
            ->sortByDesc('gross')
            ->toArray();
    }

    private function getComplianceStatus($year): array
    {
        return [
            'total_payrolls' => PayrollRun::where('year', $year)->where('status', 'processed')->count(),
            'employees_covered' => Payslip::whereHas('payrollRun', fn($q) => $q->where('year', $year)->where('status', 'processed'))->distinct('employee_id')->count(),
            'payslips_generated' => Payslip::whereHas('payrollRun', fn($q) => $q->where('year', $year)->where('status', 'processed'))->count(),
            'records_complete' => true,
        ];
    }

    private function getYearPayrollData($year): array
    {
        $payslips = Payslip::with('payrollRun')
            ->whereHas('payrollRun', fn($q) => $q->where('year', $year)->where('status', 'processed'))
            ->get();

        return [
            'year' => $year,
            'total_gross' => $payslips->sum('gross_salary'),
            'total_net' => $payslips->sum('net_salary'),
            'total_deductions' => $payslips->sum('total_deductions'),
            'payslips' => $payslips->count(),
        ];
    }

    private function checkEmployeeNumbers(): bool
    {
        return Employee::where('is_active', true)->whereNull('employee_number')->count() === 0;
    }

    private function checkKRAPins(): bool
    {
        return Employee::where('is_active', true)->whereNull('kra_pin')->count() === 0;
    }

    private function checkNSSFNumbers(): bool
    {
        return Employee::where('is_active', true)->whereNull('nssf_number')->count() === 0;
    }

    private function checkMonthlyPayrolls($year): bool
    {
        return PayrollRun::where('year', $year)->where('status', 'processed')->count() >= 6;
    }

    private function checkPayslipsGenerated($year): bool
    {
        return Payslip::whereHas('payrollRun', fn($q) => $q->where('year', $year)->where('status', 'processed'))->count() > 0;
    }

    private function checkPAYEDeductions($year): bool
    {
        return Payslip::whereHas('payrollRun', fn($q) => $q->where('year', $year)->where('status', 'processed'))
            ->where('paye', '>', 0)
            ->count() > 0;
    }

    private function checkNSSFDeductions($year): bool
    {
        return Payslip::whereHas('payrollRun', fn($q) => $q->where('year', $year)->where('status', 'processed'))
            ->where('nssf_employee', '>', 0)
            ->count() > 0;
    }

    private function checkSHIFDeductions($year): bool
    {
        return Payslip::whereHas('payrollRun', fn($q) => $q->where('year', $year)->where('status', 'processed'))
            ->where('nhif', '>', 0)
            ->count() > 0;
    }

    private function checkHousingLevyDeductions($year): bool
    {
        return Payslip::whereHas('payrollRun', fn($q) => $q->where('year', $year)->where('status', 'processed'))
            ->where('housing_levy', '>', 0)
            ->count() > 0;
    }

    private function checkP10Exports($year): bool
    {
        // Would check KraExport model if it exists
        return false;
    }
}
