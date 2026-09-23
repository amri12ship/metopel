<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Aligns the legacy "employees" table (pre-existing in production) with the
     * schema expected by the current application without dropping data.
     */
    public function up(): void
    {
        if (! Schema::hasTable('employees')) {
            return;
        }

        if (! Schema::hasColumn('employees', 'employee_number')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('employee_number')->nullable()->after('user_id');
            });
        }

        if (! Schema::hasColumn('employees', 'department')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('department')->nullable()->after('position');
            });
        }

        if (! Schema::hasColumn('employees', 'join_date')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->date('join_date')->nullable()->after('department');
            });
        }

        if (! Schema::hasColumn('employees', 'status')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('status')->default('active')->after('join_date');
            });
        }

        if (Schema::hasColumn('employees', 'nik')) {
            DB::table('employees')
                ->whereNull('employee_number')
                ->update(['employee_number' => DB::raw('`nik`')]);
        }

        DB::table('employees')
            ->whereNull('status')
            ->update(['status' => 'active']);
    }

    /**
     * Reverse the migrations. No-op on databases that never had the legacy
     * "nik" column (fresh installs), so a rollback cannot hurt the standard schema.
     */
    public function down(): void
    {
        if (! Schema::hasTable('employees') || ! Schema::hasColumn('employees', 'nik')) {
            return;
        }

        foreach (['status', 'join_date', 'department', 'employee_number'] as $column) {
            if (Schema::hasColumn('employees', $column)) {
                Schema::table('employees', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
