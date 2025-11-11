<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\SpareAvailabilityResource;
use Database\Seeders\RolesAndPermissionsSeeder;
use Domain\Facility\Models\SpareAvailability;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SpareAvailabilityResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $member;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create(['email' => 'hey@alexrafuse.com']);
        $this->admin->assignRole('admin');

        $this->member = User::factory()->create();
        $this->member->assignRole('member');

        $this->staff = User::factory()->create();
        $this->staff->assignRole('staff');
    }

    public function test_member_can_only_see_their_own_availability(): void
    {
        $this->actingAs($this->member);

        $ownAvailability = SpareAvailability::factory()->create([
            'user_id' => $this->member->id,
        ]);

        $otherAvailability = SpareAvailability::factory()->create();

        $response = $this->get(route('filament.admin.resources.spare-availabilities.index'));

        $response->assertSuccessful();
        $response->assertSee($ownAvailability->user->name);
        $response->assertDontSee($otherAvailability->user->name);
    }

    public function test_staff_can_manage_all_availabilities(): void
    {
        $this->actingAs($this->staff);

        $availability = SpareAvailability::factory()->create();

        Livewire::test(SpareAvailabilityResource\Pages\EditSpareAvailability::class, [
            'record' => $availability->id,
        ])
            ->fillForm([
                'notes' => 'Updated by staff',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('spare_availabilities', [
            'id' => $availability->id,
            'notes' => 'Updated by staff',
        ]);
    }

    // ... add more role-based tests
}
