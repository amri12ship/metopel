<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_attendance_index(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.attendance.index'))
            ->assertOk()
            ->assertSee('Data Absensi');
    }

    public function test_admin_attendance_index_shows_records(): void
    {
        $admin = User::factory()->admin()->create();
        $employee = Employee::factory()->create(['employee_number' => 'EMP-1000']);
        AttendanceRecord::factory()->create([
            'employee_id' => $employee->id,
            'date' => now()->startOfMonth()->toDateString(),
        ]);
        AttendanceRecord::factory()->create([
            'employee_id' => $employee->id,
            'date' => now()->subMonth()->toDateString(),
        ]);
        AttendanceRecord::factory()->create([
            'employee_id' => $employee->id,
            'date' => now()->subMonths(2)->toDateString(),
        ]);

        $employeeName = $employee->user->name;

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.index'))
            ->assertOk()
            ->assertSee($employeeName);
    }

    public function test_admin_can_filter_attendance_by_today(): void
    {
        $admin = User::factory()->admin()->create();
        $employee = Employee::factory()->create();
        AttendanceRecord::factory()->create([
            'employee_id' => $employee->id,
            'date' => now()->toDateString(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.attendance.index', ['filter' => 'hari_ini']))
            ->assertOk()
            ->assertSee($employee->user->name);
    }

    public function test_admin_can_view_attendance_detail(): void
    {
        $admin = User::factory()->admin()->create();
        $record = AttendanceRecord::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.attendance.show', $record))
            ->assertOk()
            ->assertSee($record->employee->user->name)
            ->assertSee('Check-in');
    }

    public function test_employee_cannot_access_admin_attendance(): void
    {
        $employee = User::factory()->create();

        $this->actingAs($employee)
            ->get(route('admin.attendance.index'))
            ->assertForbidden();

        $this->actingAs($employee)
            ->get(route('admin.attendance.show', AttendanceRecord::factory()->create()))
            ->assertForbidden();
    }
}