<?php

declare(strict_types=1);

namespace App\Domain\Membership\Actions;

use App\Domain\Membership\Data\ProfileData;
use App\Domain\Membership\Services\ImportDataCache;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class CreateUserFromProfileAction
{
    public function __construct(
        private readonly ?ImportDataCache $cache = null,
    ) {}

    public function execute(ProfileData $profile): User
    {
        // Try cache first if available
        if ($this->cache) {
            return $this->executeWithCache($profile);
        }

        // Fallback to database queries (for non-import usage)
        return $this->executeWithDatabase($profile);
    }

    private function executeWithCache(ProfileData $profile): User
    {
        // Strategy 1: Try to find by curlingio_profile_id (most reliable)
        if ($profile->curlingio_profile_id) {
            $existingUser = $this->cache->findUserByCurlingioProfileId($profile->curlingio_profile_id);

            if ($existingUser) {
                $this->updateUserProfile($existingUser, $profile);

                return $existingUser;
            }
        }

        // Strategy 2: Fall back to email matching
        $existingUser = $this->cache->findUserByEmail($profile->email);

        if ($existingUser) {
            $this->updateUserProfile($existingUser, $profile);

            return $existingUser;
        }

        // Create new user
        $user = $this->createNewUser($profile);

        // Add to cache for future lookups
        $this->cache->addUser($user);

        return $user;
    }

    private function executeWithDatabase(ProfileData $profile): User
    {
        // Strategy 1: Try to find by curlingio_profile_id (most reliable)
        if ($profile->curlingio_profile_id) {
            $existingUser = User::query()
                ->where('curlingio_profile_id', $profile->curlingio_profile_id)
                ->first();

            if ($existingUser) {
                $this->updateUserProfile($existingUser, $profile);

                return $existingUser;
            }
        }

        // Strategy 2: Fall back to email matching
        $existingUser = User::query()
            ->where('email', $profile->email)
            ->first();

        if ($existingUser) {
            $this->updateUserProfile($existingUser, $profile);

            return $existingUser;
        }

        // Create new user
        return $this->createNewUser($profile);
    }

    private function createNewUser(ProfileData $profile): User
    {
        return User::query()->create([
            'name' => $profile->full_name,
            'email' => $profile->email,
            'password' => Hash::make(Str::random(32)),
            'email_verified_at' => null,
            'curlingio_profile_id' => $profile->curlingio_profile_id,
            'first_name' => $profile->first_name,
            'last_name' => $profile->last_name,
            'middle_initial' => $profile->middle_initial,
            'date_of_birth' => $profile->date_of_birth,
            'gender' => $profile->gender,
            'phone' => $profile->phone,
            'secondary_phone' => $profile->secondary_phone,
            'secondary_email' => $profile->secondary_email,
            'street_address' => $profile->street_address,
            'unit' => $profile->unit,
            'city' => $profile->city,
            'province_state' => $profile->province_state,
            'postal_zip_code' => $profile->postal_zip_code,
            'emergency_contact_name' => $profile->emergency_contact,
            'emergency_contact_phone' => $profile->emergency_phone,
            'show_contact_info' => $profile->show_contact_info,
        ]);
    }

    /**
     * Update existing user with latest profile data from curling.io
     */
    private function updateUserProfile(User $user, ProfileData $profile): void
    {
        $updates = [
            'name' => $profile->full_name,
            'first_name' => $profile->first_name,
            'last_name' => $profile->last_name,
        ];

        // Only update curlingio_profile_id if we have one and user doesn't
        if ($profile->curlingio_profile_id && ! $user->curlingio_profile_id) {
            $updates['curlingio_profile_id'] = $profile->curlingio_profile_id;
        }

        // Update other profile fields if they have values
        if ($profile->middle_initial) {
            $updates['middle_initial'] = $profile->middle_initial;
        }
        if ($profile->date_of_birth) {
            $updates['date_of_birth'] = $profile->date_of_birth;
        }
        if ($profile->gender) {
            $updates['gender'] = $profile->gender;
        }
        if ($profile->phone) {
            $updates['phone'] = $profile->phone;
        }
        if ($profile->secondary_phone) {
            $updates['secondary_phone'] = $profile->secondary_phone;
        }
        if ($profile->secondary_email) {
            $updates['secondary_email'] = $profile->secondary_email;
        }
        if ($profile->street_address) {
            $updates['street_address'] = $profile->street_address;
        }
        if ($profile->unit) {
            $updates['unit'] = $profile->unit;
        }
        if ($profile->city) {
            $updates['city'] = $profile->city;
        }
        if ($profile->province_state) {
            $updates['province_state'] = $profile->province_state;
        }
        if ($profile->postal_zip_code) {
            $updates['postal_zip_code'] = $profile->postal_zip_code;
        }
        if ($profile->emergency_contact) {
            $updates['emergency_contact_name'] = $profile->emergency_contact;
        }
        if ($profile->emergency_phone) {
            $updates['emergency_contact_phone'] = $profile->emergency_phone;
        }

        $updates['show_contact_info'] = $profile->show_contact_info;

        $user->update($updates);
    }
}
