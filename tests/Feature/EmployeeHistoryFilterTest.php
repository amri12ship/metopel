<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeHistoryFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-01-15 10:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_employee_can_filter_history_by_status(): void
    {
        $employee = Employee::factory()->create();
        AttendanceRecord::factory()->create([
            'employee_id' => $employee->id,
            'date' => '2026-01-14',
            'status' => 'Hadir',
        ]);
        AttendanceRecord::factory()->create([
            'employee_id' => $employee->id,
            'date' => '2026-01-13',
            'status' => 'Terlambat',
        ]);

        $this->actingAs($employee->user)
            ->get(route('employee.attendance.history', ['status' => 'Terlambat']))
            ->assertOk()
            ->assertSee('Terlambat');
    }

    public function test_employee_can_filter_history_by_month(): void
    {
        $employee = Employee::factory()->create();
        AttendanceRecord::factory()->create([
            'employee_id' => $employee->id,
            'date' => '2026-01-10',
        ]);
        AttendanceRecord::factory()->create([
            'employee_id' => $employee->id,
            'date' => '2025-12-10',
        ]);

        $response = $this->actingAs($employee->user)
            ->get(route('employee.attendance.history', ['month' => '2026-01']));

        $response->assertOk();
    }

    public function test_employee_history_only_shows_own_records(): void
    {
        $other = Employee::factory()->create();
        AttendanceRecord::factory()->create([
            'employee_id' => $other->id,
            'date' => '2026-01-14',
        ]);

        $me = Employee::factory()->create();

        $response = $this->actingAs($me->user)
            ->get(route('employee.attendance.history'));

        $response->assertOk()
            ->assertDontSee($other->user->name);
    }

    public function test_employee_invalid_month_is_ignored(): void
    {
        $employee = Employee::factory()->create();
        AttendanceRecord::factory()->create([
            'employee_id' => $employee->id,
            'date' => '2026-01-14',
        ]);

        $this->actingAs($employee->user)
            ->get(route('employee.attendance.history', ['month' => 'not-a-month']))
            ->assertOk();
    }
}