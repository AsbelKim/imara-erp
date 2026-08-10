<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kra_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('export_type'); // p10_list, nssf_contributions, shif_contributions, paye_summary
            $table->integer('year');
            $table->integer('month')->nullable();
            $table->string('file_name')->nullable();
            $table->text('file_path')->nullable();
            $table->integer('record_count')->default(0);
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->string('status')->default('generated'); // generated, submitted, approved, rejected
            $table->text('notes')->nullable();
            $table->timestamp('exported_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['export_type', 'year', 'month']);
            $table->index(['status', 'exported_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kra_exports');
    }
};
