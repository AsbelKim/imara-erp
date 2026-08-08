<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Deduction types (e.g., PAYE, NSSF, Loan, SACCO)
        Schema::create('deduction_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // PAYE, NSSF, Loan, SACCO, etc.
            $table->string('code')->unique(); // PAYE, NSSF_EMP, LOAN, SACCO, etc.
            $table->enum('calculation_type', ['fixed', 'percentage', 'formula', 'statutory']);
            $table->text('description')->nullable();
            $table->boolean('is_statutory')->default(false); // PAYE, NSSF are statutory
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Employee deductions (per employee per deduction type)
        Schema::create('employee_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deduction_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2); // fixed amount or percentage
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->string('reference')->nullable(); // loan ID, SACCO name, etc.
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'deduction_type_id', 'effective_from']);
        });

        // Payroll run deductions (actual amounts per payroll period)
        Schema::create('payroll_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deduction_type_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['payroll_run_id', 'employee_id', 'deduction_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_deductions');
        Schema::dropIfExists('employee_deductions');
        Schema::dropIfExists('deduction_types');
    }
};
