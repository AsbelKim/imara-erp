<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hr_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('prediction_type'); // turnover, leave_forecast, absence
            $table->decimal('prediction_value', 8, 2); // 0-100 for percentage predictions or count for leave/absence
            $table->decimal('confidence_score', 5, 2); // 0-100 confidence level
            $table->json('factors'); // JSON object of factors influencing the prediction
            $table->text('interpretation')->nullable(); // Human-readable explanation
            $table->date('prediction_date');
            $table->date('forecast_period_start')->nullable();
            $table->date('forecast_period_end')->nullable();
            $table->text('recommendation')->nullable();
            $table->string('status')->default('active'); // active, archived
            $table->timestamps();
            $table->index(['employee_id', 'prediction_type']);
            $table->index('prediction_date');
            $table->index('status');
        });

        Schema::create('prediction_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_prediction_id')->constrained('hr_predictions')->cascadeOnDelete();
            $table->decimal('prediction_value', 8, 2);
            $table->decimal('confidence_score', 5, 2);
            $table->text('notes')->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();
            $table->index('hr_prediction_id');
            $table->index('recorded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prediction_history');
        Schema::dropIfExists('hr_predictions');
    }
};
