<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\RoleEnum;
use Domain\User\Models\User;
use Illuminate\Console\Command;

class AssignMemberRoleToUsersCommand extends Command
{
    protected $signature = 'users:assign-member-role
                            {--dry-run : Show what would be changed without making changes}';

    protected $description = 'Assign member role to users who do not have any role assigned';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        $this->info('Finding users without roles...');

        // Find all users who don't have any role assigned
        $usersWithoutRoles = User::query()
            ->whereDoesntHave('roles')
            ->get();

        if ($usersWithoutRoles->isEmpty()) {
            $this->info('No users without roles found. All users already have roles assigned.');

            return self::SUCCESS;
        }

        $this->info("Found {$usersWithoutRoles->count()} users without roles.");
        $this->newLine();

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        $this->table(
            ['ID', 'Name', 'Email'],
            $usersWithoutRoles->map(fn (User $user) => [
                $user->id,
                $user->name,
                $user->email,
            ])->toArray()
        );

        if (! $isDryRun) {
            $confirmed = $this->confirm(
                "Assign 'member' role to these {$usersWithoutRoles->count()} users?",
                true
            );

            if (! $confirmed) {
                $this->warn('Operation cancelled.');

                return self::FAILURE;
            }

            $progressBar = $this->output->createProgressBar($usersWithoutRoles->count());
            $progressBar->start();

            foreach ($usersWithoutRoles as $user) {
                $user->assignRole(RoleEnum::MEMBER->value);
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);

            $this->info("Successfully assigned 'member' role to {$usersWithoutRoles->count()} users.");
        } else {
            $this->info("Would assign 'member' role to {$usersWithoutRoles->count()} users.");
        }

        return self::SUCCESS;
    }
}
