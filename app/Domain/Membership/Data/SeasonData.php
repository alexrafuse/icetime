<?php

declare(strict_types=1);

namespace App\Domain\Membership\Data;

use App\Domain\Membership\Models\Season;
use Carbon\Carbon;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class SeasonData extends Data
{
    public function __construct(
        public int|Optional $id,
        public string $name,
        public string $slug,
        public Carbon $start_date,
        public Carbon $end_date,
        public bool $is_current,
        public bool $is_registration_open,
        public Carbon|Optional $created_at,
        public Carbon|Optional $updated_at,
    ) {}

    public static function fromModel(Season $season): self
    {
        return new self(
            id: $season->id,
            name: $season->name,
            slug: $season->slug,
            start_date: $season->start_date,
            end_date: $season->end_date,
            is_current: $season->is_current,
            is_registration_open: $season->is_registration_open,
            created_at: $season->created_at,
            updated_at: $season->updated_at,
        );
    }
}
