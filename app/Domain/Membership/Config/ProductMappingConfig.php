<?php

declare(strict_types=1);

namespace App\Domain\Membership\Config;

class ProductMappingConfig
{
    /**
     * Maps curling.io product names to product identifiers.
     * This is used when curlingio_id is not available in the CSV.
     */
    public static function getNameMappings(): array
    {
        return [
            // Adult Learn to Curl
            '2025 Adult Learn to Curl Program: Oct 20-Dec 15' => [
                'curlingio_id' => 9754,
                'slug' => '2025-adult-learn-to-curl-program-oct-20-dec-15',
            ],

            // Green League
            '2025-2026 Green League First Half Oct-Dec 2025' => [
                'curlingio_id' => 9755,
                'slug' => '2025-2026-green-league-first-half-oct-dec-2025',
            ],
            '2025-2026 Green League Full Year October-March' => [
                'curlingio_id' => 9756,
                'slug' => '2025-2026-green-league-full-year-october-march',
            ],

            // Active Memberships
            '2025-2026 Membership:  Active' => [
                'curlingio_id' => 9757,
                'slug' => '2025-2026-membership-active',
            ],
            '2025-2026 Membership: Active' => [
                'curlingio_id' => 9757,
                'slug' => '2025-2026-membership-active',
            ],
            '2025-2026 Membership:  Active 2 Adults (same address)' => [
                'curlingio_id' => 9758,
                'slug' => '2025-2026-membership-active-2-adults-same-address',
            ],
            '2025-2026 Membership: Active 2 Adults (same address)' => [
                'curlingio_id' => 9758,
                'slug' => '2025-2026-membership-active-2-adults-same-address',
            ],

            // Half Year - Active
            '2025-2026 Membership:  Half Year - Active' => [
                'curlingio_id' => 9761,
                'slug' => '2025-2026-membership-half-year-active',
            ],
            '2025-2026 Membership: Half Year - Active' => [
                'curlingio_id' => 9761,
                'slug' => '2025-2026-membership-half-year-active',
            ],

            // One Evening League Only
            '2025-2026 Membership:  One Evening League Only' => [
                'curlingio_id' => 9763,
                'slug' => '2025-2026-membership-one-evening-league-only',
            ],
            '2025-2026 Membership: One Evening League Only' => [
                'curlingio_id' => 9763,
                'slug' => '2025-2026-membership-one-evening-league-only',
            ],

            // New Member - Active
            '2025-2026 Membership:  New Member - Active' => [
                'curlingio_id' => 9760,
                'slug' => '2025-2026-membership-new-member-active',
            ],
            '2025-2026 Membership: New Member - Active' => [
                'curlingio_id' => 9760,
                'slug' => '2025-2026-membership-new-member-active',
            ],
            '2025-2026 Membership:  New Members - Active 2 Adults (same address)' => [
                'curlingio_id' => 9759,
                'slug' => '2025-2026-membership-new-members-active-2-adults-same-address',
            ],
            '2025-2026 Membership: New Members - Active 2 Adults (same address)' => [
                'curlingio_id' => 9759,
                'slug' => '2025-2026-membership-new-members-active-2-adults-same-address',
            ],

            // New Member Half Year - Active
            '2025-2026 Membership:  New Member Half Year - Active' => [
                'curlingio_id' => 9762,
                'slug' => '2025-2026-membership-new-member-half-year-active',
            ],
            '2025-2026 Membership: New Member Half Year - Active' => [
                'curlingio_id' => 9762,
                'slug' => '2025-2026-membership-new-member-half-year-active',
            ],

            // New Member One Evening League Only
            '2025-2026 Membership:  New Member One Evening League Only' => [
                'curlingio_id' => 9764,
                'slug' => '2025-2026-membership-new-member-one-evening-league-only',
            ],
            '2025-2026 Membership: New Member One Evening League Only' => [
                'curlingio_id' => 9764,
                'slug' => '2025-2026-membership-new-member-one-evening-league-only',
            ],

            // Stick Curling League
            '2025-2026 Membership:  Stick Curling League' => [
                'curlingio_id' => 9765,
                'slug' => '2025-2026-membership-stick-curling-league',
            ],
            '2025-2026 Membership: Stick Curling League' => [
                'curlingio_id' => 9765,
                'slug' => '2025-2026-membership-stick-curling-league',
            ],

            // Student Curling
            '2025-2026 Membership:  Student Curling in Evening League' => [
                'curlingio_id' => 9767,
                'slug' => '2025-2026-membership-student-curling-in-evening-league',
            ],
            '2025-2026 Membership: Student Curling in Evening League' => [
                'curlingio_id' => 9767,
                'slug' => '2025-2026-membership-student-curling-in-evening-league',
            ],
            '2025-2026 Membership:  New Student Curling in Evening League' => [
                'curlingio_id' => 9767,
                'slug' => '2025-2026-membership-new-student-curling-in-evening-league',
            ],
            '2025-2026 Membership: New Student Curling in Evening League' => [
                'curlingio_id' => 9767,
                'slug' => '2025-2026-membership-new-student-curling-in-evening-league',
            ],

            // Social
            '2025-2026 Membership:  Social' => [
                'curlingio_id' => 9768,
                'slug' => '2025-2026-membership-social',
            ],
            '2025-2026 Membership: Social' => [
                'curlingio_id' => 9768,
                'slug' => '2025-2026-membership-social',
            ],

            // Addons
            '2025-2026 Locker Rental' => [
                'curlingio_id' => 9769,
                'slug' => '2025-2026-locker-rental',
            ],
            '2025-2026 Key Fob' => [
                'curlingio_id' => 9770,
                'slug' => '2025-2026-key-fob',
            ],
        ];
    }

    /**
     * Get curlingio_id for a given product name
     */
    public static function getCurlingioIdForName(string $name): ?int
    {
        $mappings = self::getNameMappings();

        return $mappings[$name]['curlingio_id'] ?? null;
    }

    /**
     * Get slug for a given product name
     */
    public static function getSlugForName(string $name): ?string
    {
        $mappings = self::getNameMappings();

        return $mappings[$name]['slug'] ?? null;
    }

    /**
     * Normalize product name for fuzzy matching
     */
    public static function normalizeName(string $name): string
    {
        // Remove multiple spaces
        $name = preg_replace('/\s+/', ' ', $name);

        // Trim
        $name = trim($name);

        // Convert to lowercase for case-insensitive comparison
        return strtolower($name);
    }
}
