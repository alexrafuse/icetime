<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\User\Actions\SendPasswordSetupLinkAction;
use Domain\User\Models\User;
use Illuminate\Console\Command;

class SendPasswordSetupLinksCommand extends Command
{
    protected $signature = 'users:send-password-setup-links
                            {emails : Comma-separated list of email addresses}
                            {--dry-run : Show what would happen without actually sending emails}
                            {--force : Resend even if user already has a valid setup link}';

    protected $description = 'Send password setup links to users by email address';

    public function __construct(
        private readonly SendPasswordSetupLinkAction $sendPasswordSetupLinkAction,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');

        // Parse email addresses from input
        $emails = collect(explode(',', $this->argument('emails')))
            ->map(fn (string $email) => trim($email))
            ->filter()
            ->unique()
            ->values();

        if ($emails->isEmpty()) {
            $this->error('No valid email addresses provided.');

            return self::FAILURE;
        }

        $this->info('Finding users...');

        // Find users by email
        $users = User::query()
            ->whereIn('email', $emails)
            ->get();

        // Check for missing users
        $foundEmails = $users->pluck('email');
        $missingEmails = $emails->diff($foundEmails);

        if ($missingEmails->isNotEmpty()) {
            $this->warn('The following email addresses were not found:');
            $missingEmails->each(fn (string $email) => $this->warn("  - {$email}"));
            $this->newLine();
        }

        if ($users->isEmpty()) {
            $this->error('No users found with the provided email addresses.');

            return self::FAILURE;
        }

        // Filter out users who already have valid setup links (unless --force is set)
        if (! $force) {
            $usersWithValidLinks = $users->filter(function (User $user) {
                return $user->temporary_password !== null
                    && $user->temporary_password_expires_at !== null
                    && $user->temporary_password_expires_at->isFuture();
            });

            if ($usersWithValidLinks->isNotEmpty()) {
                $this->warn('The following users already have valid password setup links:');
                $usersWithValidLinks->each(fn (User $user) => $this->warn("  - {$user->email} (expires {$user->temporary_password_expires_at->format('Y-m-d H:i:s')})"));
                $this->warn('Use --force to resend password setup links to these users.');
                $this->newLine();

                // Remove these users from the list
                $users = $users->filter(fn (User $user) => ! $usersWithValidLinks->contains($user));
            }
        }

        if ($users->isEmpty()) {
            $this->info('No users need password setup links sent.');

            return self::SUCCESS;
        }

        $this->info("Found {$users->count()} user(s) to send password setup links to.");
        $this->newLine();

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No emails will be sent');
            $this->newLine();
        }

        // Display users in a table
        $this->table(
            ['ID', 'Name', 'Email', 'Current Setup Link'],
            $users->map(fn (User $user) => [
                $user->id,
                $user->name,
                $user->email,
                $user->temporary_password ? 'Yes (expires '.$user->temporary_password_expires_at->format('Y-m-d').')' : 'None',
            ])->toArray()
        );

        if (! $isDryRun) {
            $confirmed = $this->confirm(
                "Send password setup links to these {$users->count()} user(s)?",
                true
            );

            if (! $confirmed) {
                $this->warn('Operation cancelled.');

                return self::FAILURE;
            }

            $this->info('Sending password setup links...');
            $progressBar = $this->output->createProgressBar($users->count());
            $progressBar->start();

            $sent = 0;
            $failed = 0;
            $failures = [];

            foreach ($users as $user) {
                try {
                    $this->sendPasswordSetupLinkAction->execute($user, $force);
                    $sent++;
                } catch (\Exception $e) {
                    $failed++;
                    $failures[] = [
                        'user' => $user->email,
                        'error' => $e->getMessage(),
                    ];
                }
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);

            // Display results
            if ($sent > 0) {
                $this->info("Successfully sent password setup links to {$sent} user(s).");
            }

            if ($failed > 0) {
                $this->error("Failed to send password setup links to {$failed} user(s):");
                foreach ($failures as $failure) {
                    $this->error("  - {$failure['user']}: {$failure['error']}");
                }

                return self::FAILURE;
            }
        } else {
            $this->info("Would send password setup links to {$users->count()} user(s).");
        }

        return self::SUCCESS;
    }
}
