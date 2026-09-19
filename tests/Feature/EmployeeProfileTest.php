<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_access_profile(): void
    {
        $employee = Employee::factory()->create();

        $this->actingAs($employee->user)
            ->get(route('employee.profile.show'))
            ->assertOk()
            ->assertSee('Profil');
    }

    public function test_employee_can_update_profile(): void
    {
        $password = 'oldsecret1';
        $employee = Employee::factory()->create([
            'user_id' => User::factory()->create([
                'password' => $password,
                'email' => 'before@test.com',
            ]),
        ]);

        $this->actingAs($employee->user)
            ->put(route('employee.profile.update'), [
                'name' => 'Nama Baru',
                'email' => 'after@test.com',
                'current_password' => $password,
                'password' => 'newsecret1',
                'password_confirmation' => 'newsecret1',
            ])
            ->assertRedirect(route('employee.profile.show'));

        $this->assertDatabaseHas('users', [
            'id' => $employee->user->id,
            'name' => 'Nama Baru',
            'email' => 'after@test.com',
        ]);
    }

    public function test_employee_cannot_update_profile_with_wrong_password(): void
    {
        $employee = Employee::factory()->create();

        $this->actingAs($employee->user)
            ->put(route('employee.profile.update'), [
                'name' => 'Nama',
                'email' => $employee->user->email,
                'current_password' => 'salah',
                'password' => 'newsecret1',
                'password_confirmation' => 'newsecret1',
            ])
            ->assertSessionHasErrors('current_password');
    }
}