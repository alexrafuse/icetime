<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Membership\Models\Season;
use Domain\Board\Models\Sponsor;
use Domain\Board\Models\Sponsorship;
use Domain\Board\Models\SponsorshipLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SponsorshipPaymentTrackingTest extends TestCase
{
    use RefreshDatabase;

    private function createSeason(array $attributes = []): Season
    {
        return Season::create(array_merge([
            'name' => fake()->words(2, true),
            'slug' => fake()->unique()->slug(),
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
        ], $attributes));
    }

    public function test_sponsorship_has_payment_tracking_fields(): void
    {
        $sponsor = Sponsor::factory()->create();
        $level = SponsorshipLevel::factory()->create();
        $season = $this->createSeason();

        $sponsorship = Sponsorship::create([
            'sponsor_id' => $sponsor->id,
            'sponsorship_level_id' => $level->id,
            'season_id' => $season->id,
            'amount_cents' => 100000,
            'start_date' => '2024-09-01',
            'end_date' => '2025-04-30',
            'is_confirmed' => true,
            'is_paid' => true,
            'reference' => 'INV-2024-001',
        ]);

        $this->assertDatabaseHas('sponsorships', [
            'id' => $sponsorship->id,
            'is_confirmed' => true,
            'is_paid' => true,
            'reference' => 'INV-2024-001',
        ]);

        $sponsorship->refresh();
        $this->assertTrue($sponsorship->is_confirmed);
        $this->assertTrue($sponsorship->is_paid);
        $this->assertEquals('INV-2024-001', $sponsorship->reference);
    }

    public function test_sponsorship_payment_fields_default_to_false(): void
    {
        $sponsor = Sponsor::factory()->create();
        $level = SponsorshipLevel::factory()->create();
        $season = $this->createSeason();

        $sponsorship = Sponsorship::create([
            'sponsor_id' => $sponsor->id,
            'sponsorship_level_id' => $level->id,
            'season_id' => $season->id,
            'amount_cents' => 100000,
            'start_date' => '2024-09-01',
            'end_date' => '2025-04-30',
        ]);

        $sponsorship->refresh();
        $this->assertFalse($sponsorship->is_confirmed);
        $this->assertFalse($sponsorship->is_paid);
        $this->assertNull($sponsorship->reference);
    }

    public function test_sponsor_current_season_sponsorship_relationship(): void
    {
        $sponsor = Sponsor::factory()->create();
        $level = SponsorshipLevel::factory()->create();

        $oldSeason = $this->createSeason([
            'name' => 'Old Season',
            'is_current' => false,
        ]);
        $currentSeason = $this->createSeason([
            'name' => 'Current Season',
            'is_current' => true,
        ]);

        Sponsorship::create([
            'sponsor_id' => $sponsor->id,
            'sponsorship_level_id' => $level->id,
            'season_id' => $oldSeason->id,
            'amount_cents' => 50000,
            'start_date' => '2023-09-01',
            'end_date' => '2024-04-30',
        ]);

        $currentSponsorship = Sponsorship::create([
            'sponsor_id' => $sponsor->id,
            'sponsorship_level_id' => $level->id,
            'season_id' => $currentSeason->id,
            'amount_cents' => 100000,
            'start_date' => '2024-09-01',
            'end_date' => '2025-04-30',
        ]);

        $loaded = $sponsor->currentSeasonSponsorship;

        $this->assertNotNull($loaded);
        $this->assertEquals($currentSponsorship->id, $loaded->id);
    }

    public function test_sponsor_current_season_sponsorship_returns_null_when_no_current_season(): void
    {
        $sponsor = Sponsor::factory()->create();
        $level = SponsorshipLevel::factory()->create();
        $season = $this->createSeason(['is_current' => false]);

        Sponsorship::create([
            'sponsor_id' => $sponsor->id,
            'sponsorship_level_id' => $level->id,
            'season_id' => $season->id,
            'amount_cents' => 100000,
            'start_date' => '2024-09-01',
            'end_date' => '2025-04-30',
        ]);

        $this->assertNull($sponsor->currentSeasonSponsorship);
    }
}
