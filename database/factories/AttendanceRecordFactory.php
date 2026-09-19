<?php

namespace Database\Factories;

use App\Models\AttendanceLocation;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceRecord>
 */
class AttendanceRecordFactory extends Factory
{
    protected $model = AttendanceRecord::class;

    public function definition(): array
    {
        $isComplete = fake()->boolean();

        return [
            'employee_id' => Employee::factory(),
            'attendance_location_id' => AttendanceLocation::factory(),
            'date' => fake()->date(),
            'check_in' => '09:00:00',
            'check_in_latitude' => fake()->latitude(-6, -2),
            'check_in_longitude' => fake()->longitude(106, 124),
            'check_out' => $isComplete ? '17:00:00' : null,
            'check_out_latitude' => $isComplete ? fake()->latitude(-6, -2) : null,
            'check_out_longitude' => $isComplete ? fake()->longitude(106, 124) : null,
            'status' => fake()->randomElement(['Hadir', 'Terlambat']),
            'notes' => null,
        ];
    }
}