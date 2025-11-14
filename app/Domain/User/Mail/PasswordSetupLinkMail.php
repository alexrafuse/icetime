<?php

declare(strict_types=1);

namespace App\Domain\User\Mail;

use App\Domain\User\Data\PasswordSetupData;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordSetupLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PasswordSetupData $data,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Set Up Your Password',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.password-setup-link',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
