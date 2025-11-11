<?php

declare(strict_types=1);

namespace App\Enums;

enum FrequencyType: string
{
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
}
