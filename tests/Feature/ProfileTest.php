<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_name_and_email(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin.profile@example.com',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.profile.update'), [
                'name' => 'Admin Baru',
                'email' => 'admin.baru@example.com',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $admin->refresh();

        $this->assertEquals('Admin Baru', $admin->name);
        $this->assertEquals('admin.baru@example.com', $admin->email);
    }

    public function test_admin_can_change_password_with_valid_current_password(): void
    {
        $admin = User::factory()->admin()->create([
            'password' => 'oldpassword1',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.profile.update'), [
                'name' => $admin->name,
                'email' => $admin->email,
                'current_password' => 'oldpassword1',
                'password' => 'newpassword1',
                'password_confirmation' => 'newpassword1',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('newpassword1', $admin->fresh()->password));
    }

    public function test_admin_cannot_change_password_with_wrong_current_password(): void
    {
        $admin = User::factory()->admin()->create([
            'password' => 'oldpassword1',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.profile.update'), [
                'name' => $admin->name,
                'email' => $admin->email,
                'current_password' => 'salah-lama',
                'password' => 'newpassword1',
                'password_confirmation' => 'newpassword1',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('oldpassword1', $admin->fresh()->password));
    }
}