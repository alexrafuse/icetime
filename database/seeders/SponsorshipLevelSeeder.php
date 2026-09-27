<?php

declare(strict_types=1);

namespace Database\Seeders;

use Domain\Board\Models\SponsorshipLevel;
use Illuminate\Database\Seeder;

class SponsorshipLevelSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            ['name' => 'House', 'description' => 'Top-tier sponsor', 'amount_cents' => 100000, 'sort_order' => 1],
            ['name' => 'Skip', 'description' => 'Premium sponsor', 'amount_cents' => 75000, 'sort_order' => 2],
            ['name' => 'Mate', 'description' => 'Mid-tier sponsor', 'amount_cents' => 50000, 'sort_order' => 3],
            ['name' => 'Second', 'description' => 'Supporting sponsor', 'amount_cents' => 35000, 'sort_order' => 4],
            ['name' => 'Lead', 'description' => 'Entry-level sponsor', 'amount_cents' => 25000, 'sort_order' => 5],
        ])->each(fn (array $level) => SponsorshipLevel::create($level));
    }
}
