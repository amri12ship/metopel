<?php

namespace Tests\Feature;

use App\Models\AttendanceLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LocationManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Kantor Pusat Test',
            'address' => 'Jl. Test No. 1',
            'latitude' => 3.5952,
            'longitude' => 98.6722,
            'radius' => 100,
            'is_active' => 1,
        ], $overrides);
    }

    public function test_admin_can_create_location_with_random_public_token(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.locations.store'), $this->validPayload())
            ->assertRedirect();

        $location = AttendanceLocation::where('name', 'Kantor Pusat Test')->first();

        $this->assertNotNull($location);
        $this->assertNotNull($location->public_token);
        $this->assertEquals(40, Str::length($location->public_token));
        $this->assertNotEquals((string) $location->id, $location->public_token);
    }

    public function test_location_validation_rejects_invalid_coordinates_and_radius(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.locations.store'), $this->validPayload([
                'latitude' => 95,
                'longitude' => 200,
                'radius' => 0,
            ]))
            ->assertSessionHasErrors(['latitude', 'longitude', 'radius']);
    }

    public function test_admin_can_update_location_coordinates_and_radius(): void
    {
        $location = AttendanceLocation::factory()->create();
        $oldToken = $location->public_token;

        $this->actingAs($this->admin())
            ->put(route('admin.locations.update', $location), [
                'name' => 'Lokasi Dipindahkan',
                'address' => 'Jl. Baru No. 5',
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'radius' => 500,
                'is_active' => 1,
            ])
            ->assertRedirect();

        $location->refresh();

        $this->assertEquals(-6.2, $location->latitude);
        $this->assertEquals(106.816666, $location->longitude);
        $this->assertEquals(500, $location->radius);
        $this->assertEquals($oldToken, $location->public_token);
    }

    public function test_admin_can_toggle_location_active_status(): void
    {
        $location = AttendanceLocation::factory()->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.locations.toggle', $location))
            ->assertRedirect();

        $this->assertFalse((bool) $location->fresh()->is_active);

        $this->actingAs($this->admin())
            ->patch(route('admin.locations.toggle', $location))
            ->assertRedirect();

        $this->assertTrue((bool) $location->fresh()->is_active);
    }

    public function test_generate_qr_regenerates_token_and_invalidates_old_token(): void
    {
        $location = AttendanceLocation::factory()->create();
        $oldToken = $location->public_token;

        $this->actingAs($this->admin())
            ->post(route('admin.locations.generate-qr', $location))
            ->assertRedirect();

        $location->refresh();

        $this->assertNotEquals($oldToken, $location->public_token);

        $this->get(route('absensi.scan', $oldToken))->assertNotFound();
        $this->get(route('absensi.scan', $location->public_token))->assertOk();
    }

    public function test_public_scan_page_shows_location_info(): void
    {
        $location = AttendanceLocation::factory()->create();

        $response = $this->get(route('absensi.scan', $location->public_token));

        $response->assertOk()->assertSee($location->name);
    }

    public function test_public_scan_page_flags_inactive_location(): void
    {
        $location = AttendanceLocation::factory()->inactive()->create();

        $response = $this->get(route('absensi.scan', $location->public_token));

        $response->assertOk()->assertSee('Nonaktif');
    }

    public function test_unknown_token_returns_404(): void
    {
        $this->get(route('absensi.scan', 'invalid-token-123'))->assertNotFound();
    }

    public function test_qr_page_renders_svg_qr_code_for_admin(): void
    {
        $location = AttendanceLocation::factory()->create();

        $response = $this->actingAs($this->admin())
            ->get(route('admin.locations.qr', $location));

        $response->assertOk()->assertSee($location->name)->assertSee('Generate QR Baru');
    }

    public function test_create_location_page_renders_interactive_map(): void
    {
        $response = $this->actingAs($this->admin())
            ->get(route('admin.locations.create'));

        $response->assertOk()
            ->assertSee('Deteksi Lokasi Saya')
            ->assertSee('id="location-map"', false)
            ->assertSee('Kembalikan Lokasi Tersimpan')
            ->assertSee('Fokus Lokasi');
    }

    public function test_edit_location_page_renders_map_with_saved_coordinates(): void
    {
        $location = AttendanceLocation::factory()->create([
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'radius' => 500,
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.locations.edit', $location));

        $response->assertOk()
            ->assertSee('id="location-map"', false)
            ->assertSee('data-lat="-6.2"', false)
            ->assertSee('data-lng="106.816666"', false)
            ->assertSee('data-radius="500"', false);
    }

    public function test_show_location_page_renders_readonly_map(): void
    {
        $location = AttendanceLocation::factory()->create();

        $response = $this->actingAs($this->admin())
            ->get(route('admin.locations.show', $location));

        $response->assertOk()
            ->assertSee('id="location-map"', false)
            ->assertSee('data-readonly="true"', false)
            ->assertDontSee('Deteksi Lokasi Saya');
    }

    public function test_employee_cannot_access_location_management_pages(): void
    {
        $employee = User::factory()->create();
        $location = AttendanceLocation::factory()->create();

        $this->actingAs($employee)
            ->get(route('admin.locations.index'))->assertForbidden();
        $this->actingAs($employee)
            ->get(route('admin.locations.create'))->assertForbidden();
        $this->actingAs($employee)
            ->get(route('admin.locations.edit', $location))->assertForbidden();
        $this->actingAs($employee)
            ->post(route('admin.locations.store'), $this->validPayload())->assertForbidden();
        $this->actingAs($employee)
            ->put(route('admin.locations.update', $location), $this->validPayload())->assertForbidden();
        $this->actingAs($employee)
            ->post(route('admin.locations.generate-qr', $location))->assertForbidden();
    }

    public function test_guest_is_redirected_when_managing_locations(): void
    {
        $this->get(route('admin.locations.create'))->assertRedirect(route('login'));
        $this->post(route('admin.locations.store'), $this->validPayload())->assertRedirect(route('login'));
    }

    public function test_update_location_preserves_public_token_and_attendance_records(): void
    {
        $location = AttendanceLocation::factory()->create();
        $oldToken = $location->public_token;

        $this->actingAs($this->admin())
            ->put(route('admin.locations.update', $location), $this->validPayload([
                'name' => 'Kantor Digeser',
                'latitude' => -6.184000,
                'longitude' => 106.830000,
                'radius' => 250,
            ]))
            ->assertRedirect();

        $location->refresh();

        $this->assertEquals(-6.184, $location->latitude);
        $this->assertEquals(106.83, $location->longitude);
        $this->assertEquals(250, $location->radius);
        $this->assertEquals($oldToken, $location->public_token);
        $this->assertTrue($location->is_active);
    }

    public function test_radius_validation_rejects_non_positive_values(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.locations.store'), $this->validPayload([
                'radius' => -100,
            ]))
            ->assertSessionHasErrors(['radius']);

        $this->actingAs($this->admin())
            ->post(route('admin.locations.store'), $this->validPayload([
                'radius' => 0,
            ]))
            ->assertSessionHasErrors(['radius']);

        $this->actingAs($this->admin())
            ->post(route('admin.locations.store'), $this->validPayload([
                'radius' => 'abc',
            ]))
            ->assertSessionHasErrors(['radius']);
    }
}