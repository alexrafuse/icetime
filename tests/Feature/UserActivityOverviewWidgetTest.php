<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Permission;
use App\Filament\Widgets\UserActivityOverview;
use Database\Seeders\RolesAndPermissionsSeeder;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserActivityOverviewWidgetTest extends TestCase
{
    use RefreshDatabase;

    private User $userWithPermission;

    private User $userWithoutPermission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        // Create user with VIEW_MEMBERSHIPS permission
        $this->userWithPermission = User::factory()->create(['email' => 'user@test.com']);
        $this->userWithPermission->givePermissionTo(Permission::VIEW_MEMBERSHIPS->value);

        // Create user without VIEW_MEMBERSHIPS permission
        $this->userWithoutPermission = User::factory()->create(['email' => 'noview@test.com']);
    }

    public function test_widget_not_visible_without_view_memberships_permission(): void
    {
        $this->actingAs($this->userWithoutPermission);

        $this->assertFalse(UserActivityOverview::canView());
    }

    public function test_widget_visible_with_view_memberships_permission(): void
    {
        $this->actingAs($this->userWithPermission);

        $this->assertTrue(UserActivityOverview::canView());
    }

    public function test_widget_appears_on_dashboard_with_permission(): void
    {
        $this->actingAs($this->userWithPermission);

        $response = $this->get(route('filament.admin.pages.dashboard'));

        $response->assertSuccessful()
            ->assertSeeLivewire(UserActivityOverview::class);
    }

    public function test_widget_does_not_appear_on_dashboard_without_permission(): void
    {
        $this->actingAs($this->userWithoutPermission);

        $response = $this->get(route('filament.admin.pages.dashboard'));

        $response->assertSuccessful()
            ->assertDontSeeLivewire(UserActivityOverview::class);
    }

    public function test_widget_renders_successfully(): void
    {
        $this->actingAs($this->userWithPermission);

        Livewire::test(UserActivityOverview::class)
            ->assertSuccessful();
    }
}
