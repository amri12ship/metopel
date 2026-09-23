<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_with_seeded_data(): void
    {
        file_put_contents(getcwd().'/public/hot', 'http://localhost:5173');

        try {
            $this->seed();

            $admin = User::where('email', 'admin@gmail.com')->firstOrFail();

            $response = $this->actingAs($admin)->get('/admin/dashboard');

            $response->assertOk();
            $response->assertSee('Ringkasan data sistem');
        } finally {
            @unlink(getcwd().'/public/hot');
        }
    }
}