<?php

declare(strict_types=1);

namespace App\Domain\User\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class PasswordSetupData extends Data
{
    public function __construct(
        public string $email,
        public string $name,
        public string $setup_url,
        public Carbon $expires_at,
    ) {}
}
