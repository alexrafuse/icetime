<?php

namespace Database\Seeders;

use Domain\User\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Alex Rafuse',
            'email' => 'hello@stacked.dev',
            'password' => bcrypt('123456'),
        ]);

        $this->call([
            RolesAndPermissionsSeeder::class,
            SeasonsSeeder::class,
            ProductsSeeder::class,
            AreaAndAvailabilitySeeder::class,
            // RecurringPatternSeeder::class,
            LeagueSeeder::class,
            // BookingSeeder::class,
            MembershipSeeder::class,
            DrawDocumentsSeeder::class,
            CalendarImportSeeder::class,
        ]);
    }
}
