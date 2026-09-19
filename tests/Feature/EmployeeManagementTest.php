<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'employee_number' => 'EMP-TEST-001',
            'name' => 'Test Karyawan',
            'email' => 'karyawan.test@example.com',
            'phone' => '081200000000',
            'position' => 'Staff',
            'department' => 'Umum',
            'join_date' => '2026-01-10',
            'status' => 'active',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    public function test_admin_can_create_employee_with_user_account(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.employees.store'), $this->validPayload())
            ->assertRedirect();

        $user = User::where('email', 'karyawan.test@example.com')->first();

        $this->assertNotNull($user);
        $this->assertEquals('employee', $user->role);
        $this->assertTrue(Hash::check('password123', $user->password));

        $employee = Employee::where('employee_number', 'EMP-TEST-001')->first();

        $this->assertNotNull($employee);
        $this->assertEquals($user->id, $employee->user_id);
        $this->assertDatabaseHas('employees', ['employee_number' => 'EMP-TEST-001']);
    }

    public function test_employee_number_and_email_must_be_unique(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.employees.store'), $this->validPayload())
            ->assertRedirect();

        $this->actingAs($this->admin())
            ->post(route('admin.employees.store'), $this->validPayload())
            ->assertSessionHasErrors(['employee_number', 'email']);
    }

    public function test_employee_number_and_password_are_required_on_create(): void
    {
        $payload = $this->validPayload();
        unset($payload['employee_number'], $payload['password'], $payload['password_confirmation']);

        $this->actingAs($this->admin())
            ->post(route('admin.employees.store'), $payload)
            ->assertSessionHasErrors(['employee_number', 'password']);
    }

    public function test_admin_can_update_employee(): void
    {
        $employee = Employee::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('admin.employees.update', $employee), [
                'employee_number' => $employee->employee_number,
                'name' => 'Nama Diubah',
                'email' => $employee->user->email,
                'status' => 'active',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $employee->user_id,
            'name' => 'Nama Diubah',
        ]);
    }

    public function test_admin_can_toggle_employee_status(): void
    {
        $employee = Employee::factory()->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.employees.toggle', $employee))
            ->assertRedirect();

        $this->assertEquals('inactive', $employee->fresh()->status);

        $this->actingAs($this->admin())
            ->patch(route('admin.employees.toggle', $employee))
            ->assertRedirect();

        $this->assertEquals('active', $employee->fresh()->status);
    }

    public function test_admin_can_delete_employee(): void
    {
        $employee = Employee::factory()->create();
        $userId = $employee->user_id;

        $this->actingAs($this->admin())
            ->delete(route('admin.employees.destroy', $employee))
            ->assertRedirect();

        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }
}