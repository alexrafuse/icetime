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
            'first_name' => 'Alex',
            'last_name' => 'Rafuse',
            'middle_initial' => null,
            'date_of_birth' => '1990-01-01',
            'gender' => 'Male',
            'phone' => '555-123-4567',
            'secondary_phone' => null,
            'secondary_email' => null,
            'street_address' => '123 Main Street',
            'unit' => null,
            'city' => 'Halifax',
            'province_state' => 'NS',
            'postal_zip_code' => 'B3H 1A1',
            'emergency_contact_name' => 'Emergency Contact',
            'emergency_contact_phone' => '555-987-6543',
            'show_contact_info' => true,
        ]);

        $this->call([
            RolesAndPermissionsSeeder::class,
            SeasonsSeeder::class,
            ProductsSeeder::class,
            AreaAndAvailabilitySeeder::class,
            // RecurringPatternSeeder::class,
            // BookingSeeder::class,
            MembershipSeeder::class,
            DrawDocumentsSeeder::class,
            CalendarImportSeeder::class,
            LeagueSeeder::class,

        ]);
    }
}
