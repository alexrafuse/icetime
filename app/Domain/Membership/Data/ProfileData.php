<?php

declare(strict_types=1);

namespace App\Domain\Membership\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class ProfileData extends Data
{
    public function __construct(
        public string $full_name,
        public string $first_name,
        public string $last_name,
        public ?string $email = null,
        public ?string $curlingio_profile_id = null,
        public ?string $middle_initial = null,
        public ?Carbon $date_of_birth = null,
        public ?string $gender = null,
        public ?string $street_address = null,
        public ?string $unit = null,
        public ?string $city = null,
        public ?string $province_state = null,
        public ?string $postal_zip_code = null,
        public ?string $phone = null,
        public ?string $secondary_phone = null,
        public ?string $primary_email = null,
        public ?string $secondary_email = null,
        public bool $show_contact_info = false,
        public ?string $emergency_contact = null,
        public ?string $emergency_phone = null,
    ) {}

    public static function fromCsvRow(array $row): self
    {
        $firstName = trim($row['Curler First name'] ?? '');
        $lastName = trim($row['Curler Last name'] ?? '');
        $fullName = trim("{$firstName} {$lastName}");

        $dateOfBirth = null;
        if (! empty($row['Curler Date of birth'])) {
            try {
                $dateOfBirth = Carbon::parse($row['Curler Date of birth']);
            } catch (\Exception $e) {
                // Skip invalid dates
            }
        }

        $showContactInfo = false;
        if (isset($row['Curler Show contact info to members?'])) {
            $showContactInfo = strtolower($row['Curler Show contact info to members?']) === 'true';
        }

        return new self(
            full_name: $fullName,
            first_name: $firstName,
            last_name: $lastName,
            email: trim($row['Curler Primary email'] ?? $row['User Email'] ?? ''),
            curlingio_profile_id: ! empty($row['Profile ID']) ? (string) $row['Profile ID'] : null,
            middle_initial: ! empty($row['Curler Middle initial']) ? trim($row['Curler Middle initial']) : null,
            date_of_birth: $dateOfBirth,
            gender: ! empty($row['Curler Gender']) ? trim($row['Curler Gender']) : null,
            street_address: ! empty($row['Curler Street address']) ? trim($row['Curler Street address']) : null,
            unit: ! empty($row['Curler Unit']) ? trim($row['Curler Unit']) : null,
            city: ! empty($row['Curler City']) ? trim($row['Curler City']) : null,
            province_state: ! empty($row['Curler Province / State']) ? trim($row['Curler Province / State']) : null,
            postal_zip_code: ! empty($row['Curler Postal / Zip code']) ? trim($row['Curler Postal / Zip code']) : null,
            phone: ! empty($row['Curler Phone']) ? trim($row['Curler Phone']) : null,
            secondary_phone: ! empty($row['Curler Secondary phone']) ? trim($row['Curler Secondary phone']) : null,
            primary_email: ! empty($row['Curler Primary email']) ? trim($row['Curler Primary email']) : null,
            secondary_email: ! empty($row['Curler Secondary email']) ? trim($row['Curler Secondary email']) : null,
            show_contact_info: $showContactInfo,
            emergency_contact: ! empty($row['Curler Emergency contact']) ? trim($row['Curler Emergency contact']) : null,
            emergency_phone: ! empty($row['Curler Emergency phone']) ? trim($row['Curler Emergency phone']) : null,
        );
    }

    public static function fromSecondMemberCsvRow(array $row): self
    {
        $name = trim($row['2nd Member Name'] ?? '');
        $parts = explode(' ', $name);
        $lastName = count($parts) > 1 ? array_pop($parts) : '';
        $firstName = implode(' ', $parts);

        return new self(
            full_name: $name,
            first_name: $firstName,
            last_name: $lastName,
            email: trim($row['2nd Member Email'] ?? ''),
            curlingio_profile_id: null,
            middle_initial: null,
            date_of_birth: null,
            gender: null,
            street_address: null,
            unit: null,
            city: null,
            province_state: null,
            postal_zip_code: null,
            phone: ! empty($row['2nd Member Phone']) ? trim($row['2nd Member Phone']) : null,
            secondary_phone: null,
            primary_email: ! empty($row['2nd Member Email']) ? trim($row['2nd Member Email']) : null,
            secondary_email: null,
            show_contact_info: false,
            emergency_contact: null,
            emergency_phone: null,
        );
    }

    public static function fromProfileString(string $profileName, ?string $fallbackEmail = null, bool $isPrimary = true): self
    {
        $parts = explode(' ', trim($profileName));
        $lastName = array_pop($parts);
        $firstName = implode(' ', $parts);

        if ($isPrimary) {
            $email = $fallbackEmail;
        } else {
            $email = self::generatePlaceholderEmail($firstName, $lastName);
        }

        return new self(
            full_name: trim($profileName),
            first_name: $firstName,
            last_name: $lastName,
            email: $email,
        );
    }

    protected static function generatePlaceholderEmail(string $firstName, string $lastName): string
    {
        $slug = strtolower(str_replace(' ', '.', trim("{$firstName}.{$lastName}")));
        $slug = preg_replace('/[^a-z0-9.]/', '', $slug);

        return "{$slug}.pending@icetime.local";
    }
}
