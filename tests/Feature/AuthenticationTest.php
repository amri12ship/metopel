<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_login_page_can_be_rendered(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Login');
    }

    public function test_admin_can_login_and_reach_dashboard(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@test.com',
            'password' => 'secret123',
        ]);

        $this->post(route('login.attempt'), [
            'email' => 'admin@test.com',
            'password' => 'secret123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@test.com',
            'password' => 'secret123',
        ]);

        $this->post(route('login.attempt'), [
            'email' => 'admin@test.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_employee_cannot_access_admin_pages(): void
    {
        $employee = User::factory()->create();

        $this->actingAs($employee)
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this->actingAs($employee)
            ->get(route('admin.employees.index'))
            ->assertForbidden();
    }

    public function test_guest_cannot_access_admin_pages(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_admin_can_logout(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}