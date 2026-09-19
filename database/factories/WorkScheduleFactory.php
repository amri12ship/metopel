<?php

namespace Database\Factories;

use App\Models\WorkSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkSchedule>
 */
class WorkScheduleFactory extends Factory
{
    protected $model = WorkSchedule::class;

    public function definition(): array
    {
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        return [
            'name' => 'Shift '.fake()->randomElement(['Pagi', 'Siang', 'Malam']),
            'day' => fake()->randomElement($days),
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'late_tolerance' => fake()->randomElement([0, 10, 15]),
            'is_active' => true,
        ];
    }
}