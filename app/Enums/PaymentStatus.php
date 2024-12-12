<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentStatus: string
{
    case PAID = 'paid';
    case UNPAID = 'unpaid';
    case PENDING = 'pending';

    public function getColor(): string
    {
        return match($this) {
            self::PAID => 'success',
            self::UNPAID => 'danger',
            self::PENDING => 'warning',
        };
    }
} 