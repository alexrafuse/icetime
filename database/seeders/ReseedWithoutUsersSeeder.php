<?php

namespace Database\Seeders;

use Domain\User\Models\User;
use Illuminate\Database\Seeder;

class ReseedWithoutUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder is designed to reseed all data while preserving existing users.
     * It skips user creation and uses existing users for all data relationships.
     */
    public function run(): void
    {
        // Verify we have at least one user
        $userCount = User::count();
        if ($userCount === 0) {
            $this->command->error('No users found in database. Cannot proceed with reseeding.');
            $this->command->info('Please run DatabaseSeeder first to create initial users.');

            return;
        }

        $this->command->info("Found {$userCount} existing user(s)");
        $this->command->info('Beginning reseed process...');

        // Run seeders in dependency order
        // Note: We skip MembershipSeeder as it creates additional test users
        $this->call([
            RolesAndPermissionsSeeder::class,
            SeasonsSeeder::class,
            ProductsSeeder::class,
            AreaAndAvailabilitySeeder::class,
            DrawDocumentsSeeder::class,
            CalendarImportSeeder::class,
            LeagueSeeder::class,
        ]);

        // Reassign admin role to first user
        $this->reassignAdminRole();

        $this->command->newLine();
        $this->command->info('✅ Reseed completed successfully!');
    }

    protected function reassignAdminRole(): void
    {
        $adminUser = User::first();

        if ($adminUser && ! $adminUser->hasRole('admin')) {
            $this->command->info('Assigning admin role to first user...');
            $adminUser->assignRole('admin');
        }
    }
}
