<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ReseedExceptUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:reseed-except-users {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reseed all data except users and user-related data';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->option('force')) {
            if (! $this->confirm('This will clear all data except users, user activities, spare availabilities, and memberships. Continue?')) {
                $this->info('Operation cancelled.');

                return self::FAILURE;
            }
        }

        $this->info('Starting reseed operation...');

        // Disable foreign key checks
        $this->info('Disabling foreign key checks...');
        $this->disableForeignKeyChecks();

        try {
            // Truncate tables in correct order
            $this->truncateTables();

            // Clear system tables
            $this->clearSystemTables();

            // Re-enable foreign key checks
            $this->info('Re-enabling foreign key checks...');
            $this->enableForeignKeyChecks();

            // Run the seeder
            $this->info('Running seeders...');
            $this->call('db:seed', ['--class' => 'Database\\Seeders\\ReseedWithoutUsersSeeder']);

            $this->newLine();
            $this->info('Reseed completed successfully!');

            return self::SUCCESS;

        } catch (\Exception $e) {
            // Re-enable foreign key checks even on error
            $this->enableForeignKeyChecks();

            $this->error('Reseed failed: '.$e->getMessage());
            $this->error($e->getTraceAsString());

            return self::FAILURE;
        }
    }

    protected function truncateTables(): void
    {
        $this->info('Truncating tables...');

        // Order matters - delete dependencies first
        $tables = [
            'notifications',
            'payments',
            'area_booking',
            'booking_area',
            'bookings',
            'recurring_patterns',
            'availabilities',
            'draw_documents',
            'areas',
            'products',
            'seasons',
            'role_has_permissions',
            'model_has_permissions',
            'model_has_roles',
            'permissions',
            'roles',
        ];

        foreach ($tables as $table) {
            $this->line("  - Truncating {$table}");
            \DB::table($table)->truncate();
        }
    }

    protected function clearSystemTables(): void
    {
        $this->info('Clearing system tables...');

        $systemTables = [
            'cache',
            'cache_locks',
            'failed_jobs',
            'jobs',
            'job_batches',
            'telescope_entries',
            'telescope_entries_tags',
            'telescope_monitoring',
        ];

        foreach ($systemTables as $table) {
            if (\Schema::hasTable($table)) {
                $this->line("  - Clearing {$table}");
                \DB::table($table)->truncate();
            }
        }
    }

    protected function disableForeignKeyChecks(): void
    {
        $driver = \DB::getDriverName();

        match ($driver) {
            'mysql' => \DB::statement('SET FOREIGN_KEY_CHECKS=0'),
            'sqlite' => \DB::statement('PRAGMA foreign_keys=OFF'),
            'pgsql' => \DB::statement('SET CONSTRAINTS ALL DEFERRED'),
            default => null,
        };
    }

    protected function enableForeignKeyChecks(): void
    {
        $driver = \DB::getDriverName();

        match ($driver) {
            'mysql' => \DB::statement('SET FOREIGN_KEY_CHECKS=1'),
            'sqlite' => \DB::statement('PRAGMA foreign_keys=ON'),
            'pgsql' => \DB::statement('SET CONSTRAINTS ALL IMMEDIATE'),
            default => null,
        };
    }
}
