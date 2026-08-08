<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Earnings types (e.g., Housing Allowance, Transport, Commission)
        Schema::create('earnings_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Housing Allowance, Transport Allowance, etc.
            $table->string('code')->unique(); // HOUSING, TRANSPORT, etc.
            $table->enum('calculation_type', ['fixed', 'percentage']); // flat amount or % of salary
            $table->text('description')->nullable();
            $table->boolean('is_taxable')->default(true); // included in PAYE calculation
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Employee earnings (per employee per earning type)
        Schema::create('employee_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('earnings_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2); // fixed amount or percentage
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'earnings_type_id', 'effective_from']);
        });

        // Payroll run earnings (actual amounts per payroll period)
        Schema::create('payroll_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('earnings_type_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->timestamps();
            $table->unique(['payroll_run_id', 'employee_id', 'earnings_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_earnings');
        Schema::dropIfExists('employee_earnings');
        Schema::dropIfExists('earnings_types');
    }
};
