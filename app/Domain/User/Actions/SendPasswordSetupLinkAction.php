<?php

declare(strict_types=1);

namespace App\Domain\User\Actions;

use App\Domain\User\Data\PasswordSetupData;
use App\Domain\User\Mail\PasswordSetupLinkMail;
use Carbon\Carbon;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

final class SendPasswordSetupLinkAction
{
    public function execute(User $user, bool $force = false): void
    {
        // Check if user already has a valid setup link and force is not set
        if (! $force && $this->hasValidSetupToken($user)) {
            throw new \RuntimeException("User {$user->email} already has a valid password setup link. Use --force to resend.");
        }

        // Generate secure random token (64 characters)
        $token = Str::random(64);

        // Set expiration to 7 days from now
        $expiresAt = Carbon::now()->addDays(7);

        // Hash and store token
        $user->update([
            'temporary_password' => Hash::make($token),
            'temporary_password_expires_at' => $expiresAt,
        ]);

        // Build magic link URL
        $setupUrl = config('app.url').'/admin/set-password?token='.urlencode($token).'&email='.urlencode($user->email);

        // Create data DTO for email
        $data = new PasswordSetupData(
            email: $user->email,
            name: $user->name,
            setup_url: $setupUrl,
            expires_at: $expiresAt,
        );

        // Send email
        Mail::to($user->email)->send(new PasswordSetupLinkMail($data));
    }

    private function hasValidSetupToken(User $user): bool
    {
        return $user->temporary_password !== null
            && $user->temporary_password_expires_at !== null
            && $user->temporary_password_expires_at->isFuture();
    }
}
