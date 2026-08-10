<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll_anomalies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->string('anomaly_type'); // salary_change, duplicate_payment, paye_mismatch, nssf_error, shif_error, housing_levy_error, compliance_violation
            $table->text('description');
            $table->decimal('expected_value', 12, 2)->nullable();
            $table->decimal('actual_value', 12, 2)->nullable();
            $table->decimal('variance_amount', 12, 2)->nullable();
            $table->decimal('variance_percentage', 8, 2)->nullable(); // Percentage difference
            $table->decimal('severity_score', 5, 2); // 0-100 scale
            $table->enum('severity_level', ['low', 'medium', 'high', 'critical']); // Based on severity_score
            $table->text('recommendation')->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->text('resolution_notes')->nullable();
            $table->timestamp('detected_at')->useCurrent();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'payroll_run_id']);
            $table->index('anomaly_type');
            $table->index('severity_level');
            $table->index('is_resolved');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_anomalies');
    }
};
