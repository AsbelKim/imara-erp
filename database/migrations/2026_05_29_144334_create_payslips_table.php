<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->decimal('basic_salary', 12, 2);
            $table->decimal('gross_salary', 12, 2);
            // NSSF
            $table->decimal('nssf_employee', 10, 2)->default(0);
            $table->decimal('nssf_employer', 10, 2)->default(0);
            // NHIF
            $table->decimal('nhif', 10, 2)->default(0);
            // PAYE
            $table->decimal('taxable_income', 12, 2)->default(0);
            $table->decimal('paye', 10, 2)->default(0);
            // Housing Levy
            $table->decimal('housing_levy', 10, 2)->default(0);
            // Totals
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);
            $table->timestamps();
            $table->unique(['payroll_run_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
