<?php

declare(strict_types=1);

namespace Tests\Feature;

use Database\Seeders\RolesAndPermissionsSeeder;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelSmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create(['email' => 'admin@test.com']);
        $this->admin->assignRole('admin');
    }

    public function test_admin_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('filament.admin.pages.dashboard'));

        $response->assertOk();
    }

    public function test_admin_can_access_all_resource_index_pages(): void
    {
        $resources = [
            'areas',
            'availabilities',
            'bookings',
            'draw-documents',
            'permissions',
            'products',
            'recurring-patterns',
            'roles',
            'seasons',
            'spare-availabilities',
            'users',
        ];

        foreach ($resources as $resource) {
            $response = $this->actingAs($this->admin)
                ->get(route("filament.admin.resources.{$resource}.index"));

            $response->assertOk();
        }
    }

    public function test_admin_can_access_all_resource_create_pages(): void
    {
        $resources = [
            'areas',
            'availabilities',
            'bookings',
            'draw-documents',
            'permissions',
            'products',
            'recurring-patterns',
            'roles',
            'seasons',
            'spare-availabilities',
            'users',
        ];

        foreach ($resources as $resource) {
            $response = $this->actingAs($this->admin)
                ->get(route("filament.admin.resources.{$resource}.create"));

            $response->assertOk();
        }
    }

    public function test_admin_can_access_booking_calendar_page(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('filament.admin.pages.booking-calendar'));

        $response->assertOk();
    }
}
