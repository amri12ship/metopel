<?php

namespace Tests\Feature;

use App\Models\AttendanceLocation;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-01-15 07:55:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_employee_can_access_dashboard(): void
    {
        $employee = Employee::factory()->create();

        $this->actingAs($employee->user)
            ->get(route('employee.dashboard'))
            ->assertOk()
            ->assertSee('ABSEN MASUK');
    }

    public function test_admin_cannot_access_employee_pages(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('employee.dashboard'))
            ->assertForbidden();
    }

    public function test_guest_cannot_access_employee_pages(): void
    {
        $this->get(route('employee.dashboard'))->assertRedirect(route('login'));
    }

    public function test_employee_login_redirects_to_employee_dashboard(): void
    {
        $user = User::factory()->create(['password' => 'secret123']);

        $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertRedirect(route('employee.dashboard'));
    }

    public function test_employee_can_check_in_within_radius(): void
    {
        $employee = Employee::factory()->create();
        $location = AttendanceLocation::factory()->create([
            'latitude' => 3.5952,
            'longitude' => 98.6722,
            'radius' => 100,
        ]);
        $this->attachSchedule($employee);

        $response = $this->actingAs($employee->user)
            ->postJson(route('employee.attendance.check-in'), [
                'token' => $location->public_token,
                'latitude' => 3.5953,
                'longitude' => 98.6723,
            ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertTrue(
            AttendanceRecord::where('employee_id', $employee->id)
                ->where('attendance_location_id', $location->id)
                ->whereDate('date', '2026-01-15')
                ->where('status', AttendanceService::STATUS_HADIR)
                ->exists(),
        );
    }

    public function test_employee_gets_terlambat_status_past_tolerance(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-15 08:30:00'));

        $employee = Employee::factory()->create();
        $location = AttendanceLocation::factory()->create([
            'latitude' => 3.5952,
            'longitude' => 98.6722,
            'radius' => 100,
        ]);
        $this->attachSchedule($employee);

        $response = $this->actingAs($employee->user)
            ->postJson(route('employee.attendance.check-in'), [
                'token' => $location->public_token,
                'latitude' => 3.5952,
                'longitude' => 98.6722,
            ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $employee->id,
            'status' => AttendanceService::STATUS_TERLAMBAT,
        ]);
    }

    public function test_employee_cannot_check_in_outside_radius(): void
    {
        $employee = Employee::factory()->create();
        $location = AttendanceLocation::factory()->create([
            'latitude' => 3.5952,
            'longitude' => 98.6722,
            'radius' => 100,
        ]);
        $this->attachSchedule($employee);

        $response = $this->actingAs($employee->user)
            ->postJson(route('employee.attendance.check-in'), [
                'token' => $location->public_token,
                'latitude' => 3.7000,
                'longitude' => 98.8000,
            ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertDatabaseCount('attendance_records', 0);
    }

    public function test_employee_cannot_check_in_with_invalid_token(): void
    {
        $employee = Employee::factory()->create();
        $this->attachSchedule($employee);

        $response = $this->actingAs($employee->user)
            ->postJson(route('employee.attendance.check-in'), [
                'token' => 'token-tidak-ada',
                'latitude' => 3.5952,
                'longitude' => 98.6722,
            ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertDatabaseCount('attendance_records', 0);
    }

    public function test_employee_cannot_check_in_at_inactive_location(): void
    {
        $employee = Employee::factory()->create();
        $location = AttendanceLocation::factory()->inactive()->create([
            'latitude' => 3.5952,
            'longitude' => 98.6722,
            'radius' => 100,
        ]);
        $this->attachSchedule($employee);

        $response = $this->actingAs($employee->user)
            ->postJson(route('employee.attendance.check-in'), [
                'token' => $location->public_token,
                'latitude' => 3.5952,
                'longitude' => 98.6722,
            ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_inactive_employee_cannot_check_in(): void
    {
        $employee = Employee::factory()->inactive()->create();
        $location = AttendanceLocation::factory()->create([
            'latitude' => 3.5952,
            'longitude' => 98.6722,
            'radius' => 100,
        ]);

        $response = $this->actingAs($employee->user)
            ->postJson(route('employee.attendance.check-in'), [
                'token' => $location->public_token,
                'latitude' => 3.5952,
                'longitude' => 98.6722,
            ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_employee_cannot_check_in_without_schedule(): void
    {
        $employee = Employee::factory()->create();
        $location = AttendanceLocation::factory()->create([
            'latitude' => 3.5952,
            'longitude' => 98.6722,
            'radius' => 100,
        ]);

        $response = $this->actingAs($employee->user)
            ->postJson(route('employee.attendance.check-in'), [
                'token' => $location->public_token,
                'latitude' => 3.5952,
                'longitude' => 98.6722,
            ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertDatabaseCount('attendance_records', 0);
    }

    public function test_employee_cannot_check_in_twice_on_same_day(): void
    {
        $employee = Employee::factory()->create();
        $location = AttendanceLocation::factory()->create([
            'latitude' => 3.5952,
            'longitude' => 98.6722,
            'radius' => 100,
        ]);
        $this->attachSchedule($employee);

        $payload = [
            'token' => $location->public_token,
            'latitude' => 3.5952,
            'longitude' => 98.6722,
        ];

        $this->actingAs($employee->user)
            ->postJson(route('employee.attendance.check-in'), $payload)
            ->assertOk();

        $this->actingAs($employee->user)
            ->postJson(route('employee.attendance.check-in'), $payload)
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertDatabaseCount('attendance_records', 1);
    }

    public function test_employee_cannot_check_out_before_check_in(): void
    {
        $employee = Employee::factory()->create();
        $location = AttendanceLocation::factory()->create([
            'latitude' => 3.5952,
            'longitude' => 98.6722,
            'radius' => 100,
        ]);
        $this->attachSchedule($employee);

        $response = $this->actingAs($employee->user)
            ->postJson(route('employee.attendance.check-out'), [
                'token' => $location->public_token,
                'latitude' => 3.5952,
                'longitude' => 98.6722,
            ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_employee_can_check_in_then_check_out(): void
    {
        $employee = Employee::factory()->create();
        $location = AttendanceLocation::factory()->create([
            'latitude' => 3.5952,
            'longitude' => 98.6722,
            'radius' => 100,
        ]);
        $this->attachSchedule($employee);

        $payload = [
            'token' => $location->public_token,
            'latitude' => 3.5952,
            'longitude' => 98.6722,
        ];

        $this->actingAs($employee->user)
            ->postJson(route('employee.attendance.check-in'), $payload)
            ->assertOk();

        Carbon::setTestNow(Carbon::parse('2026-01-15 17:10:00'));

        $this->actingAs($employee->user)
            ->postJson(route('employee.attendance.check-out'), $payload)
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $employee->id,
            'check_out' => '17:10:00',
        ]);
    }

    public function test_employee_cannot_check_out_twice(): void
    {
        $employee = Employee::factory()->create();
        $location = AttendanceLocation::factory()->create([
            'latitude' => 3.5952,
            'longitude' => 98.6722,
            'radius' => 100,
        ]);
        $this->attachSchedule($employee);

        $payload = [
            'token' => $location->public_token,
            'latitude' => 3.5952,
            'longitude' => 98.6722,
        ];

        $this->actingAs($employee->user)
            ->postJson(route('employee.attendance.check-in'), $payload)
            ->assertOk();

        Carbon::setTestNow(Carbon::parse('2026-01-15 17:10:00'));

        $this->actingAs($employee->user)
            ->postJson(route('employee.attendance.check-out'), $payload)
            ->assertOk();

        $this->actingAs($employee->user)
            ->postJson(route('employee.attendance.check-out'), $payload)
            ->assertStatus(422);

        $this->assertDatabaseCount('attendance_records', 1);
    }

    public function test_employee_can_view_own_history(): void
    {
        $employee = Employee::factory()->create();
        $location = AttendanceLocation::factory()->create();
        AttendanceRecord::factory()->create([
            'employee_id' => $employee->id,
            'attendance_location_id' => $location->id,
            'date' => '2026-01-15',
        ]);

        $this->actingAs($employee->user)
            ->get(route('employee.attendance.history'))
            ->assertOk()
            ->assertSee($location->name);
    }

    public function test_employee_cannot_view_others_record_detail(): void
    {
        $other = Employee::factory()->create();
        $location = AttendanceLocation::factory()->create();
        $record = AttendanceRecord::factory()->create([
            'employee_id' => $other->id,
            'attendance_location_id' => $location->id,
            'date' => '2026-01-15',
        ]);

        $me = Employee::factory()->create();

        $this->actingAs($me->user)
            ->get(route('employee.attendance.show', $record))
            ->assertNotFound();
    }

    private function attachSchedule(Employee $employee): void
    {
        $day = now()->locale('id')->isoFormat('dddd');
        $schedule = WorkSchedule::factory()->create([
            'day' => $day,
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'late_tolerance' => 15,
        ]);

        $employee->workSchedules()->attach($schedule);
    }
}