<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\SpareAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpareAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_spare_availability_can_be_created(): void
    {
        $spareAvailability = SpareAvailability::factory()->create();

        $this->assertDatabaseHas('spare_availabilities', [
            'id' => $spareAvailability->id,
        ]);
    }

    public function test_spare_availability_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $spareAvailability = SpareAvailability::factory()
            ->for($user)
            ->create();

        $this->assertTrue($spareAvailability->user->is($user));
    }

    public function test_can_filter_active_spares(): void
    {
        SpareAvailability::factory()->create(['is_active' => false]);
        $activeSpare = SpareAvailability::factory()->create(['is_active' => true]);

        $activeSpares = SpareAvailability::where('is_active', true)->get();

        $this->assertCount(1, $activeSpares);
        $this->assertTrue($activeSpares->first()->is($activeSpare));
    }

    public function test_can_filter_spares_by_day(): void
    {
        SpareAvailability::factory()->create([
            'monday' => false,
            'tuesday' => true,
        ]);
        
        $mondaySpare = SpareAvailability::factory()->create([
            'monday' => true,
            'tuesday' => false,
        ]);

        $mondaySpares = SpareAvailability::where('monday', true)->get();

        $this->assertCount(1, $mondaySpares);
        $this->assertTrue($mondaySpares->first()->is($mondaySpare));
    }
} 