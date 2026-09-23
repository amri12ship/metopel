<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('attendance_records')) {
            return;
        }

        if (! Schema::hasIndex('attendance_records', 'attendance_records_employee_date_unique')) {
            Schema::table('attendance_records', function (Blueprint $table) {
                $table->unique(['employee_id', 'date'], 'attendance_records_employee_date_unique');
            });
        }

        if (! Schema::hasIndex('attendance_records', 'attendance_records_date_index')) {
            Schema::table('attendance_records', function (Blueprint $table) {
                $table->index('date', 'attendance_records_date_index');
            });
        }

        if (! Schema::hasIndex('attendance_records', 'attendance_records_status_index')) {
            Schema::table('attendance_records', function (Blueprint $table) {
                $table->index('status', 'attendance_records_status_index');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('attendance_records')) {
            return;
        }

        if (Schema::hasIndex('attendance_records', 'attendance_records_employee_date_unique')) {
            Schema::table('attendance_records', function (Blueprint $table) {
                $table->dropUnique('attendance_records_employee_date_unique');
            });
        }

        if (Schema::hasIndex('attendance_records', 'attendance_records_date_index')) {
            Schema::table('attendance_records', function (Blueprint $table) {
                $table->dropIndex('attendance_records_date_index');
            });
        }

        if (Schema::hasIndex('attendance_records', 'attendance_records_status_index')) {
            Schema::table('attendance_records', function (Blueprint $table) {
                $table->dropIndex('attendance_records_status_index');
            });
        }
    }
};