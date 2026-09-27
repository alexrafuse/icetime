<?php

declare(strict_types=1);

namespace Domain\Board\Enums;

enum DonationType: string
{
    case MONETARY = 'monetary';
    case IN_KIND = 'in_kind';
    case EQUIPMENT = 'equipment';
    case SERVICE = 'service';

    public function getLabel(): string
    {
        return match ($this) {
            self::MONETARY => 'Monetary',
            self::IN_KIND => 'In Kind',
            self::EQUIPMENT => 'Equipment',
            self::SERVICE => 'Service',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::MONETARY => 'success',
            self::IN_KIND => 'info',
            self::EQUIPMENT => 'warning',
            self::SERVICE => 'primary',
        };
    }
}
