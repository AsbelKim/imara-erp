<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('statutory_rates', function (Blueprint $table) {
            $table->id();
            $table->string('rate_type'); // paye, nssf_tier1, nssf_tier2, shif, housing_levy, etc.
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month')->default(1); // effective from which month
            $table->decimal('percentage', 5, 2)->nullable(); // for percentage-based rates
            $table->decimal('fixed_amount', 12, 2)->nullable(); // for flat rates
            $table->decimal('ceiling', 12, 2)->nullable(); // max applicable to
            $table->decimal('floor', 12, 2)->nullable(); // min applicable to
            $table->decimal('relief_amount', 12, 2)->nullable(); // e.g., PAYE personal relief
            $table->text('description')->nullable();
            $table->timestamp('effective_date');
            $table->timestamps();
            $table->unique(['rate_type', 'year', 'month']);
        });

        // PAYE bands table
        Schema::create('paye_bands', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month')->default(1);
            $table->unsignedSmallInteger('band_order');
            $table->decimal('upper_limit', 12, 2);
            $table->decimal('rate', 5, 2);
            $table->timestamps();
            $table->unique(['year', 'month', 'band_order']);
        });

        // NHIF/SHIF bands table
        Schema::create('shif_bands', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month')->default(1);
            $table->decimal('upper_limit', 12, 2);
            $table->decimal('amount', 10, 2);
            $table->timestamps();
            $table->unique(['year', 'month', 'upper_limit']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paye_bands');
        Schema::dropIfExists('shif_bands');
        Schema::dropIfExists('statutory_rates');
    }
};
