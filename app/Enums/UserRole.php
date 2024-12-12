<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case MEMBER = 'member';
    case STAFF = 'staff';
    case ADMIN = 'admin';
} 