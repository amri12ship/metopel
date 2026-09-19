<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->unique(['employee_id', 'date'], 'attendance_records_employee_date_unique');
            $table->index('date', 'attendance_records_date_index');
            $table->index('status', 'attendance_records_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropUnique('attendance_records_employee_date_unique');
            $table->dropIndex('attendance_records_date_index');
            $table->dropIndex('attendance_records_status_index');
        });
    }
};