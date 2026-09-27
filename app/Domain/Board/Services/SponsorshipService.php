<?php

declare(strict_types=1);

namespace Domain\Board\Services;

use App\Domain\Membership\Models\Season;
use Domain\Board\Models\Sponsor;
use Domain\Board\Models\Sponsorship;
use Domain\Board\Models\SponsorshipLevel;
use Illuminate\Support\Collection;

final class SponsorshipService
{
    public function recordSponsorship(
        Sponsor $sponsor,
        SponsorshipLevel $level,
        Season $season,
        int $amountCents,
        array $additional = [],
    ): Sponsorship {
        return Sponsorship::create([
            'sponsor_id' => $sponsor->id,
            'sponsorship_level_id' => $level->id,
            'season_id' => $season->id,
            'amount_cents' => $amountCents,
            'start_date' => $additional['start_date'] ?? $season->start_date,
            'end_date' => $additional['end_date'] ?? $season->end_date,
            'notes' => $additional['notes'] ?? null,
        ]);
    }

    public function getSponsorHistory(Sponsor $sponsor): Collection
    {
        return $sponsor->sponsorships()
            ->with(['level', 'season'])
            ->orderByDesc('start_date')
            ->get();
    }

    public function getActiveSponsorsBySeason(Season $season): Collection
    {
        return Sponsorship::query()
            ->where('season_id', $season->id)
            ->with(['sponsor', 'level'])
            ->get();
    }
}
