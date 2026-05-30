<?php

use App\Http\Controllers\HR\DepartmentController;
use App\Http\Controllers\HR\EmployeeController;
use App\Http\Controllers\HR\LeaveController;
use App\Http\Controllers\HR\LeaveTypeController;
use App\Http\Controllers\HR\PayrollController;
use App\Http\Controllers\HR\ReportController;
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
