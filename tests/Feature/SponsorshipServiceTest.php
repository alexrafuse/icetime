<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Membership\Models\Season;
use Domain\Board\Models\Sponsor;
use Domain\Board\Models\Sponsorship;
use Domain\Board\Models\SponsorshipLevel;
use Domain\Board\Services\SponsorshipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SponsorshipServiceTest extends TestCase
{
    use RefreshDatabase;

    private SponsorshipService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SponsorshipService;
    }

    private function createSeason(array $attributes = []): Season
    {
        return Season::create(array_merge([
            'name' => fake()->words(2, true),
            'slug' => fake()->unique()->slug(),
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
        ], $attributes));
    }

    public function test_record_sponsorship(): void
    {
        $sponsor = Sponsor::factory()->create();
        $level = SponsorshipLevel::factory()->create();
        $season = $this->createSeason();

        $sponsorship = $this->service->recordSponsorship(
            $sponsor,
            $level,
            $season,
            250000,
        );

        $this->assertDatabaseHas('sponsorships', [
            'sponsor_id' => $sponsor->id,
            'sponsorship_level_id' => $level->id,
            'season_id' => $season->id,
            'amount_cents' => 250000,
        ]);
    }

    public function test_get_sponsor_history_ordered_by_date(): void
    {
        $sponsor = Sponsor::factory()->create();
        $level = SponsorshipLevel::factory()->create();

        $olderSeason = $this->createSeason(['name' => '2022-2023']);
        $newerSeason = $this->createSeason(['name' => '2023-2024']);

        Sponsorship::create([
            'sponsor_id' => $sponsor->id,
            'sponsorship_level_id' => $level->id,
            'season_id' => $olderSeason->id,
            'amount_cents' => 100000,
            'start_date' => '2023-09-01',
            'end_date' => '2024-04-30',
        ]);
        Sponsorship::create([
            'sponsor_id' => $sponsor->id,
            'sponsorship_level_id' => $level->id,
            'season_id' => $newerSeason->id,
            'amount_cents' => 100000,
            'start_date' => '2024-09-01',
            'end_date' => '2025-04-30',
        ]);

        $history = $this->service->getSponsorHistory($sponsor);

        $this->assertCount(2, $history);
        $this->assertTrue($history->first()->start_date->isAfter($history->last()->start_date));
    }

    public function test_sponsor_total_contributed_cents(): void
    {
        $sponsor = Sponsor::factory()->create();
        $level = SponsorshipLevel::factory()->create();
        $season = $this->createSeason();

        collect(range(1, 3))->each(fn () => Sponsorship::create([
            'sponsor_id' => $sponsor->id,
            'sponsorship_level_id' => $level->id,
            'season_id' => $season->id,
            'amount_cents' => 100000,
            'start_date' => '2024-09-01',
            'end_date' => '2025-04-30',
        ]));

        $this->assertEquals(300000, $sponsor->totalContributedCents());
    }

    public function test_sponsor_years_sponsoring(): void
    {
        $sponsor = Sponsor::factory()->create();
        $level = SponsorshipLevel::factory()->create();
        $season1 = $this->createSeason(['name' => '2022-2023']);
        $season2 = $this->createSeason(['name' => '2023-2024']);

        Sponsorship::create([
            'sponsor_id' => $sponsor->id,
            'sponsorship_level_id' => $level->id,
            'season_id' => $season1->id,
            'amount_cents' => 100000,
            'start_date' => '2022-09-01',
            'end_date' => '2023-04-30',
        ]);
        Sponsorship::create([
            'sponsor_id' => $sponsor->id,
            'sponsorship_level_id' => $level->id,
            'season_id' => $season2->id,
            'amount_cents' => 100000,
            'start_date' => '2023-09-01',
            'end_date' => '2024-04-30',
        ]);

        $this->assertEquals(2, $sponsor->yearsSponsoring());
    }
}
