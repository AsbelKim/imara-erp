<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Employee loans and salary advances
        Schema::create('employee_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->enum('loan_type', ['salary_advance', 'personal_loan', 'emergency_advance', 'other']);
            $table->decimal('loan_amount', 12, 2);
            $table->decimal('interest_rate', 5, 2)->default(0); // percentage
            $table->unsignedSmallInteger('loan_term_months'); // repayment period in months
            $table->date('disbursement_date');
            $table->date('expected_completion_date');
            $table->date('actual_completion_date')->nullable();
            $table->decimal('monthly_installment', 12, 2);
            $table->decimal('total_repaid', 12, 2)->default(0);
            $table->decimal('outstanding_balance', 12, 2);
            $table->enum('status', ['pending', 'approved', 'disbursed', 'active', 'completed', 'written_off'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // Loan repayment schedule
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_loan_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('installment_number');
            $table->date('due_date');
            $table->decimal('principal_amount', 12, 2);
            $table->decimal('interest_amount', 12, 2);
            $table->decimal('installment_amount', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->date('paid_date')->nullable();
            $table->enum('status', ['pending', 'paid', 'partial', 'overdue'])->default('pending');
            $table->timestamps();
            $table->unique(['employee_loan_id', 'installment_number']);
        });

        // Guarantors for loans
        Schema::create('loan_guarantors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_loan_id')->constrained()->cascadeOnDelete();
            $table->string('guarantor_name');
            $table->string('guarantor_phone');
            $table->string('guarantor_id_number')->nullable();
            $table->string('relationship');
            $table->text('address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_guarantors');
        Schema::dropIfExists('loan_repayments');
        Schema::dropIfExists('employee_loans');
    }
};
