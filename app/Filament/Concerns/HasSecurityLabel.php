<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

/**
 * Trait for Filament resources to add security indicator to navigation label
 *
 * Appends a lock icon to the navigation label to indicate
 * that the resource requires special permissions.
 */
trait HasSecurityLabel
{
    /**
     * Get the navigation label with security indicator
     */
    public static function getNavigationLabel(): string
    {
        $label = static::$navigationLabel
            ?? static::getTitleCasePluralModelLabel();

        return $label.' 🔒';
    }
}
