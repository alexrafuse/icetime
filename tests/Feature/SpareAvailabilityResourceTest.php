<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\SpareAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;
use App\Filament\Resources\SpareAvailabilityResource;

class SpareAvailabilityResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'hey@alexrafuse.com',
        ]));
    }

    public function test_can_view_spare_availabilities(): void
    {
        $spareAvailability = SpareAvailability::factory()->create();

        $response = $this->get(route('filament.admin.resources.spare-availabilities.index'));

        $response->assertSuccessful();
        $response->assertSee($spareAvailability->user->name);
    }

    public function test_can_create_spare_availability(): void
    {
        $user = User::factory()->create();

        Livewire::test(SpareAvailabilityResource\Pages\CreateSpareAvailability::class)
            ->fillForm([
                'user_id' => $user->id,
                'monday' => true,
                'tuesday' => false,
                'wednesday' => true,
                'thursday' => false,
                'friday' => true,
                'phone_number' => '1234567890',
                'sms_enabled' => true,
                'call_enabled' => false,
                'notes' => 'Test notes',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('spare_availabilities', [
            'user_id' => $user->id,
            'monday' => true,
            'wednesday' => true,
            'friday' => true,
            'phone_number' => '1234567890',
        ]);
    }

    public function test_can_edit_spare_availability(): void
    {
        $spareAvailability = SpareAvailability::factory()->create();

        Livewire::test(SpareAvailabilityResource\Pages\EditSpareAvailability::class, [
            'record' => $spareAvailability->id,
        ])
            ->fillForm([
                'monday' => true,
                'notes' => 'Updated notes',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('spare_availabilities', [
            'id' => $spareAvailability->id,
            'monday' => true,
            'notes' => 'Updated notes',
        ]);
    }

    public function test_can_delete_spare_availability(): void
    {
        $spareAvailability = SpareAvailability::factory()->create();

        Livewire::test(SpareAvailabilityResource\Pages\ListSpareAvailabilities::class)
            ->callTableAction('delete', $spareAvailability);

        $this->assertModelMissing($spareAvailability);
    }
} 