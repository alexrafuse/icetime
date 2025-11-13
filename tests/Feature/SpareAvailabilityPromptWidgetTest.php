<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Permission;
use App\Filament\Widgets\SpareAvailabilityPrompt;
use Database\Seeders\RolesAndPermissionsSeeder;
use Domain\Facility\Models\SpareAvailability;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SpareAvailabilityPromptWidgetTest extends TestCase
{
    use RefreshDatabase;

    private User $userWithPermission;

    private User $userWithoutPermission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        // Create user with VIEW_SPARES permission
        $this->userWithPermission = User::factory()->create(['email' => 'user@test.com']);
        $this->userWithPermission->givePermissionTo(Permission::VIEW_SPARES->value);

        // Create user without VIEW_SPARES permission
        $this->userWithoutPermission = User::factory()->create(['email' => 'noview@test.com']);
    }

    public function test_widget_not_visible_without_view_spares_permission(): void
    {
        $this->actingAs($this->userWithoutPermission);

        $this->assertFalse(SpareAvailabilityPrompt::canView());
    }

    public function test_widget_visible_with_view_spares_permission(): void
    {
        $this->actingAs($this->userWithPermission);

        $this->assertTrue(SpareAvailabilityPrompt::canView());
    }

    public function test_widget_shows_set_availability_when_user_has_no_record(): void
    {
        $this->actingAs($this->userWithPermission);

        Livewire::test(SpareAvailabilityPrompt::class)
            ->assertSuccessful()
            ->assertSee('Set your spare availability')
            ->assertSee('Let others know when you')
            ->assertSee('available to spare')
            ->assertSee('Set Availability');
    }

    public function test_widget_shows_need_spare_when_user_has_record(): void
    {
        $this->actingAs($this->userWithPermission);

        // Create spare availability for user
        SpareAvailability::factory()->create([
            'user_id' => $this->userWithPermission->id,
            'is_active' => true,
        ]);

        Livewire::test(SpareAvailabilityPrompt::class)
            ->assertSuccessful()
            ->assertSee('Need a spare tonight?')
            ->assertSee('View Spare List')
            ->assertDontSee('Set your spare availability');
    }

    public function test_widget_links_to_spare_list_when_has_record(): void
    {
        $this->actingAs($this->userWithPermission);

        SpareAvailability::factory()->create([
            'user_id' => $this->userWithPermission->id,
            'is_active' => true,
        ]);

        $response = $this->get(route('filament.admin.pages.dashboard'));

        $response->assertSuccessful()
            ->assertSee(route('filament.admin.resources.spare-availabilities.index', absolute: false));
    }

    public function test_widget_appears_on_dashboard_with_permission(): void
    {
        $this->actingAs($this->userWithPermission);

        $response = $this->get(route('filament.admin.pages.dashboard'));

        $response->assertSuccessful()
            ->assertSeeLivewire(SpareAvailabilityPrompt::class);
    }

    public function test_widget_does_not_appear_on_dashboard_without_permission(): void
    {
        $this->actingAs($this->userWithoutPermission);

        $response = $this->get(route('filament.admin.pages.dashboard'));

        $response->assertSuccessful()
            ->assertDontSeeLivewire(SpareAvailabilityPrompt::class);
    }

    public function test_widget_renders_successfully(): void
    {
        $this->actingAs($this->userWithPermission);

        Livewire::test(SpareAvailabilityPrompt::class)
            ->assertSuccessful();
    }
}
