<?php

declare(strict_types=1);

namespace App\Domain\Membership\Actions;

use App\Domain\Membership\Data\SeasonData;
use App\Domain\Membership\Models\Season;

final class CreateSeasonAction
{
    public function execute(SeasonData $data): Season
    {
        $season = Season::query()->create([
            'name' => $data->name,
            'slug' => $data->slug,
            'start_date' => $data->start_date,
            'end_date' => $data->end_date,
            'is_current' => $data->is_current,
            'is_registration_open' => $data->is_registration_open,
        ]);

        if ($data->is_current) {
            Season::query()
                ->where('id', '!=', $season->id)
                ->update(['is_current' => false]);
        }

        return $season->fresh();
    }
}
