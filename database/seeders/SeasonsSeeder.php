<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Membership\Models\Season;
use Illuminate\Database\Seeder;

class SeasonsSeeder extends Seeder
{
    public function run(): void
    {
        Season::query()->create([
            'name' => '2025-2026',
            'slug' => '2025-2026',
            'start_date' => '2025-10-01',
            'end_date' => '2026-03-31',
            'is_current' => true,
            'is_registration_open' => true,
        ]);
    }
}
