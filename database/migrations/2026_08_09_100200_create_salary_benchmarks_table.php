<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('salary_benchmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('job_title');
            $table->string('job_grade')->nullable(); // e.g., Level 1, Level 2, etc.
            $table->string('experience_level'); // junior, mid, senior, lead
            $table->enum('education_level', ['high_school', 'diploma', 'degree', 'masters', 'phd'])->nullable();
            $table->decimal('market_minimum', 12, 2);
            $table->decimal('market_average', 12, 2);
            $table->decimal('market_maximum', 12, 2);
            $table->decimal('company_average', 12, 2)->nullable(); // Running average in company
            $table->integer('sample_size')->default(0); // How many data points were used
            $table->date('data_source_date'); // When market data was sourced
            $table->string('data_source')->nullable(); // e.g., 'internal', 'industry_report', etc.
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['department_id', 'job_title', 'job_grade', 'experience_level', 'education_level']);
        });

        Schema::create('salary_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('salary_benchmark_id')->constrained()->cascadeOnDelete();
            $table->string('status'); // draft, recommended, approved, implemented, rejected
            $table->decimal('current_salary', 12, 2);
            $table->decimal('recommended_salary', 12, 2);
            $table->decimal('salary_increase', 12, 2);
            $table->decimal('increase_percentage', 8, 2);
            $table->text('justification');
            $table->json('comparison_data'); // JSON object with market comparisons
            $table->text('recommendation_notes')->nullable();
            $table->integer('years_of_experience')->nullable();
            $table->timestamp('recommended_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('implemented_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->text('approval_notes')->nullable();
            $table->timestamps();
            $table->index('employee_id');
            $table->index('status');
            $table->index('recommended_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_recommendations');
        Schema::dropIfExists('salary_benchmarks');
    }
};
