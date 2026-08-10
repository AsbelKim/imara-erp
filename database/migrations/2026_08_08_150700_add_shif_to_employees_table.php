<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Add SHIF number (Social Health Insurance Fund) - only if not exists
            if (!Schema::hasColumn('employees', 'shif_number')) {
                $table->string('shif_number')->nullable()->after('nhif_number');
            }

            // Add job grade/level
            if (!Schema::hasColumn('employees', 'job_grade')) {
                $table->string('job_grade')->nullable()->after('job_title');
            }

            // Add reporting manager
            if (!Schema::hasColumn('employees', 'reporting_manager_id')) {
                $table->foreignId('reporting_manager_id')->nullable()->after('department_id')->constrained('employees')->nullOnDelete();
            }

            // Add contract dates
            if (!Schema::hasColumn('employees', 'contract_start_date')) {
                $table->date('contract_start_date')->nullable()->after('hire_date');
            }
            if (!Schema::hasColumn('employees', 'contract_end_date')) {
                $table->date('contract_end_date')->nullable()->after('contract_start_date');
            }

            // Add probation period tracking
            if (!Schema::hasColumn('employees', 'probation_period_months')) {
                $table->unsignedSmallInteger('probation_period_months')->nullable()->after('contract_end_date');
            }
            if (!Schema::hasColumn('employees', 'probation_end_date')) {
                $table->date('probation_end_date')->nullable()->after('probation_period_months');
            }

            // Add branch/location
            if (!Schema::hasColumn('employees', 'branch_location')) {
                $table->string('branch_location')->nullable()->after('address');
            }

            // Add employment status field
            if (!Schema::hasColumn('employees', 'employment_status')) {
                $table->enum('employment_status', ['active', 'on_leave', 'suspended', 'terminated'])->default('active')->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['reporting_manager_id']);
            $cols = [
                'shif_number', 'job_grade', 'reporting_manager_id',
                'contract_start_date', 'contract_end_date',
                'probation_period_months', 'probation_end_date',
                'branch_location', 'employment_status',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('employees', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
