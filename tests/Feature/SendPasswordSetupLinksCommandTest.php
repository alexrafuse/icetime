<?php

declare(strict_types=1);

namespace Tests\Feature;

use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendPasswordSetupLinksCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_sends_password_setup_link_to_user(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'test@example.com']);

        $this->artisan('users:send-password-setup-links', [
            'emails' => 'test@example.com',
            '--no-interaction' => true,
        ])
            ->expectsQuestion('Send password setup links to these 1 user(s)?', true)
            ->assertSuccessful();

        $user->refresh();

        $this->assertNotNull($user->temporary_password);
        $this->assertNotNull($user->temporary_password_expires_at);
        $this->assertTrue($user->temporary_password_expires_at->isFuture());

        Mail::assertSent(\App\Domain\User\Mail\PasswordSetupLinkMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_command_handles_multiple_emails(): void
    {
        Mail::fake();

        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        $this->artisan('users:send-password-setup-links', [
            'emails' => 'user1@example.com,user2@example.com',
            '--no-interaction' => true,
        ])
            ->expectsQuestion('Send password setup links to these 2 user(s)?', true)
            ->assertSuccessful();

        $user1->refresh();
        $user2->refresh();

        $this->assertNotNull($user1->temporary_password);
        $this->assertNotNull($user2->temporary_password);

        Mail::assertSent(\App\Domain\User\Mail\PasswordSetupLinkMail::class, 2);
    }

    public function test_command_dry_run_mode(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'test@example.com']);

        $this->artisan('users:send-password-setup-links', [
            'emails' => 'test@example.com',
            '--dry-run' => true,
        ])
            ->expectsOutputToContain('DRY RUN MODE')
            ->expectsOutputToContain('Would send password setup links to 1 user(s).')
            ->assertSuccessful();

        $user->refresh();

        $this->assertNull($user->temporary_password);
        Mail::assertNothingSent();
    }
}
