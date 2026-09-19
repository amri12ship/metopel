<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'employee_number' => 'EMP-'.fake()->unique()->numerify('####'),
            'phone' => fake()->phoneNumber(),
            'position' => fake()->jobTitle(),
            'department' => fake()->word(),
            'join_date' => fake()->date(),
            'status' => Employee::STATUS_ACTIVE,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Employee::STATUS_INACTIVE,
        ]);
    }
}