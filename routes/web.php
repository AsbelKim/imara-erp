<?php

use App\Http\Controllers\HR\DepartmentController;
use App\Http\Controllers\HR\EmployeeController;
use App\Http\Controllers\HR\LeaveController;
use App\Http\Controllers\HR\LeaveTypeController;
use App\Http\Controllers\HR\PayrollController;
use App\Http\Controllers\HR\ReportController;
use App\Http\Controllers\HR\StatutoryRateController;
use App\Http\Controllers\HR\AttendanceController;
use App\Http\Controllers\HR\LoanController;
use App\Http\Controllers\HR\AuditLogController;
use App\Http\Controllers\HR\KRAExportController;
use App\Http\Controllers\HR\AdvancedReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Portal\PortalController;
use App\Http\Controllers\Portal\PortalLeaveController;
use App\Http\Controllers\Portal\PortalPayslipController;
use App\Http\Controllers\Portal\PortalProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route(auth()->user()->hasRole('Employee') && ! auth()->user()->hasRole(['Super Admin', 'HR Manager']) ? 'portal.dashboard' : 'dashboard')
        : redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // ── HR Admin dashboard (Super Admin + HR Manager only) ──────────────
    Route::middleware(['employee.only'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::middleware('role:Super Admin|HR Manager')->prefix('hr')->name('hr.')->group(function () {
            Route::resource('departments', DepartmentController::class)->except('show');
            Route::get('employees/credentials', [EmployeeController::class, 'credentials'])->name('employees.credentials');
            Route::resource('employees', EmployeeController::class);
            Route::resource('leave-types', LeaveTypeController::class)->except('show');
            Route::get('leaves', [LeaveController::class, 'index'])->name('leaves.index');
            Route::patch('leaves/{leaveRequest}/approve', [LeaveController::class, 'approve'])->name('leaves.approve');
            Route::patch('leaves/{leaveRequest}/reject', [LeaveController::class, 'reject'])->name('leaves.reject');
            Route::resource('payroll', PayrollController::class)->only(['index', 'create', 'store', 'show']);
            Route::delete('payroll/{payroll}/void', [PayrollController::class, 'void'])->name('payroll.void')->middleware('role:Super Admin');
            Route::get('payroll/{payrollRun}/payslip/{employee}', [PayrollController::class, 'downloadPayslip'])->name('payroll.payslip');

            Route::get('reports/payroll', [ReportController::class, 'payroll'])->name('reports.payroll');
            Route::get('reports/leave',   [ReportController::class, 'leave'])->name('reports.leave');

            // ── Advanced Reports (Compliance, Trends, Analytics) ──
            Route::prefix('advanced-reports')->name('advanced-reports.')->group(function () {
                Route::get('dashboard', [AdvancedReportController::class, 'dashboard'])->name('dashboard');
                Route::get('employee-turnover', [AdvancedReportController::class, 'employeeTurnover'])->name('employee-turnover');
                Route::get('payroll-cost-trends', [AdvancedReportController::class, 'payrollCostTrends'])->name('payroll-cost-trends');
                Route::get('statutory-liabilities', [AdvancedReportController::class, 'statutoryLiabilities'])->name('statutory-liabilities');
                Route::get('department-payroll', [AdvancedReportController::class, 'departmentPayroll'])->name('department-payroll');
                Route::get('compliance-checklist', [AdvancedReportController::class, 'complianceChecklist'])->name('compliance-checklist');
            });

            // ── KRA Exports (P10, NSSF, SHIF, PAYE) ──
            Route::prefix('kra-exports')->name('kra-exports.')->group(function () {
                Route::get('/', [KRAExportController::class, 'index'])->name('index');
                Route::get('create', [KRAExportController::class, 'create'])->name('create');
                Route::get('{kraExport}', [KRAExportController::class, 'show'])->name('show');
                Route::get('{kraExport}/download', [KRAExportController::class, 'download'])->name('download');
                Route::post('generate-p10', [KRAExportController::class, 'generateP10'])->name('generate-p10');
                Route::post('generate-nssf', [KRAExportController::class, 'generateNSSF'])->name('generate-nssf');
                Route::post('generate-shif', [KRAExportController::class, 'generateSHIF'])->name('generate-shif');
                Route::post('generate-paye', [KRAExportController::class, 'generatePAYE'])->name('generate-paye');
                Route::post('{kraExport}/mark-submitted', [KRAExportController::class, 'markSubmitted'])->name('mark-submitted');
                Route::delete('{kraExport}', [KRAExportController::class, 'destroy'])->name('destroy');
                Route::get('api/statistics', [KRAExportController::class, 'statistics'])->name('statistics');
            });

            // ── Audit Logs (Super Admin only) ──
            Route::middleware('role:Super Admin')->prefix('audit-logs')->name('audit-logs.')->group(function () {
                Route::get('/', [AuditLogController::class, 'index'])->name('index');
                Route::get('{auditLog}', [AuditLogController::class, 'show'])->name('show');
                Route::get('user/{user}/activity', [AuditLogController::class, 'userActivity'])->name('user-activity');
                Route::post('export', [AuditLogController::class, 'export'])->name('export');
                Route::get('api/statistics', [AuditLogController::class, 'statistics'])->name('statistics');
                Route::post('api/model-history', [AuditLogController::class, 'modelHistory'])->name('model-history');
                Route::post('purge', [AuditLogController::class, 'purge'])->name('purge');
            });

            // ── New features: Statutory Rates, Attendance, Loans ──
            Route::resource('statutory-rates', StatutoryRateController::class);

            Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
            Route::get('attendance/check-in-out', [AttendanceController::class, 'checkInOut'])->name('attendance.check-in-out');
            Route::post('attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
            Route::post('attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
            Route::post('attendance/mark', [AttendanceController::class, 'mark'])->name('attendance.mark');
            Route::get('attendance/employee/{employee}/summary', [AttendanceController::class, 'summary'])->name('attendance.summary');
            Route::get('attendance/report/export', [AttendanceController::class, 'export'])->name('attendance.export');
            Route::get('attendance/department/report', [AttendanceController::class, 'departmentReport'])->name('attendance.department-report');

            Route::resource('loans', LoanController::class);
            Route::post('loans/{loan}/suspend', [LoanController::class, 'suspend'])->name('loans.suspend');
            Route::post('loans/{loan}/resume', [LoanController::class, 'resume'])->name('loans.resume');
            Route::post('loans/repayments/{repayment}/record', [LoanController::class, 'recordRepayment'])->name('loans.record-repayment');
            Route::get('employees/{employee}/loans', [LoanController::class, 'employeeLoans'])->name('loans.employee');
            Route::get('employees/{employee}/loans/statement', [LoanController::class, 'employeeStatement'])->name('loans.employee-statement');
        });
    });

    // ── Employee Self-Service Portal (Employee role only) ───────────────
    Route::prefix('employee')->name('portal.')->middleware('role:Employee')->group(function () {

        // Password change (allowed before force-password check)
        Route::get('change-password',   [PortalProfileController::class, 'changePasswordForm'])->name('password.change');
        Route::post('change-password',  [PortalProfileController::class, 'changePassword'])->name('password.update');

        // Everything below requires password to already be changed
        Route::middleware('force.password')->group(function () {
            Route::get('dashboard', [PortalController::class, 'dashboard'])->name('dashboard');

            Route::get('profile',            [PortalProfileController::class, 'show'])->name('profile.show');

            Route::get('leaves',             [PortalLeaveController::class, 'index'])->name('leaves.index');
            Route::post('leaves',            [PortalLeaveController::class, 'store'])->name('leaves.store');
            Route::delete('leaves/{leaveRequest}', [PortalLeaveController::class, 'cancel'])->name('leaves.cancel');

            Route::get('payslips',                    [PortalPayslipController::class, 'index'])->name('payslips.index');
            Route::get('payslips/{payslip}',          [PortalPayslipController::class, 'show'])->name('payslips.show');
            Route::get('payslips/{payslip}/download', [PortalPayslipController::class, 'download'])->name('payslips.download');
        });
    });

    // Breeze profile — HR Admin and Super Admin only
    Route::middleware('role:Super Admin|HR Manager')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

require __DIR__.'/auth.php';
