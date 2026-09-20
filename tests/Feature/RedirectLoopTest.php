<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedirectLoopTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_admin_hitting_root_goes_to_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/')
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_authenticated_employee_hitting_root_goes_to_employee_dashboard(): void
    {
        $employee = User::factory()->create(['role' => User::ROLE_EMPLOYEE]);

        $this->actingAs($employee)
            ->get('/')
            ->assertRedirect(route('employee.dashboard'));
    }

    public function test_guest_hitting_root_goes_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }
}