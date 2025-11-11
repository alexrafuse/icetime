<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

/**
 * Trait for Filament resources to display record count badges
 *
 * Provides standard implementation for displaying the total count
 * of records as a navigation badge with danger color.
 */
trait HasCountBadge
{
    /**
     * Get the navigation badge showing total record count
     */
    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    /**
     * Get the navigation badge color
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
