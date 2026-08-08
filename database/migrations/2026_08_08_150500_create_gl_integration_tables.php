<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // GL mapping for payroll deductions/earnings
        Schema::create('payroll_gl_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('earnings_or_deduction_code')->unique(); // PAYE, NSSF, BASIC, HOUSING, etc.
            $table->string('account_code'); // GL account code
            $table->string('account_name');
            $table->enum('account_type', ['expense', 'liability', 'asset', 'income']);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // GL entries generated from payroll
        Schema::create('payroll_gl_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->string('account_code');
            $table->string('account_name');
            $table->enum('entry_type', ['debit', 'credit']);
            $table->decimal('amount', 14, 2);
            $table->text('description')->nullable();
            $table->string('reference')->nullable(); // Payroll/2026/August
            $table->timestamp('posting_date');
            $table->boolean('is_posted')->default(false);
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
        });

        // Statutory liability tracking
        Schema::create('statutory_liabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->string('liability_type'); // PAYE_PAYABLE, NSSF_PAYABLE, SHIF_PAYABLE, AHL_PAYABLE
            $table->decimal('employee_amount', 12, 2)->default(0);
            $table->decimal('employer_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->enum('payment_status', ['pending', 'partially_paid', 'paid'])->default('pending');
            $table->date('due_date')->nullable(); // KRA: 9th of following month
            $table->date('paid_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statutory_liabilities');
        Schema::dropIfExists('payroll_gl_entries');
        Schema::dropIfExists('payroll_gl_mappings');
    }
};
