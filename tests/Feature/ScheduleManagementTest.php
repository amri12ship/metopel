<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Shift Pagi',
            'day' => 'Senin',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'late_tolerance' => 15,
            'is_active' => 1,
        ], $overrides);
    }

    public function test_admin_can_create_work_schedule(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.schedules.store'), $this->validPayload())
            ->assertRedirect();

        $this->assertDatabaseHas('work_schedules', [
            'name' => 'Shift Pagi',
            'day' => 'Senin',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'late_tolerance' => 15,
        ]);
    }

    public function test_schedule_validation_rejects_negative_tolerance_and_invalid_times(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.schedules.store'), $this->validPayload([
                'late_tolerance' => -1,
                'end_time' => '03:00',
            ]))
            ->assertSessionHasErrors(['late_tolerance', 'end_time']);
    }

    public function test_admin_can_update_work_schedule(): void
    {
        $schedule = WorkSchedule::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('admin.schedules.update', $schedule), [
                'name' => 'Shift Malam',
                'day' => 'Sabtu',
                'start_time' => '21:00',
                'end_time' => '23:00',
                'late_tolerance' => 5,
                'is_active' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('work_schedules', [
            'id' => $schedule->id,
            'name' => 'Shift Malam',
            'late_tolerance' => 5,
        ]);
    }

    public function test_admin_can_toggle_and_delete_schedule(): void
    {
        $schedule = WorkSchedule::factory()->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.schedules.toggle', $schedule))
            ->assertRedirect();

        $this->assertFalse((bool) $schedule->fresh()->is_active);

        $this->actingAs($this->admin())
            ->delete(route('admin.schedules.destroy', $schedule))
            ->assertRedirect();

        $this->assertDatabaseMissing('work_schedules', ['id' => $schedule->id]);
    }
}