<?php

namespace Database\Factories;

use App\Models\AttendanceLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceLocation>
 */
class AttendanceLocationFactory extends Factory
{
    protected $model = AttendanceLocation::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company().' '.fake()->randomElement(['Kantor', 'Cabang', 'Gudang']),
            'address' => fake()->address(),
            'latitude' => fake()->latitude(-6, -2),
            'longitude' => fake()->longitude(106, 124),
            'radius' => fake()->randomElement([50, 100, 200, 500]),
            'public_token' => fake()->sha256(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}