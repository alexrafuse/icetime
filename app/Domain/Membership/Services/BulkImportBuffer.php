<?php

declare(strict_types=1);

namespace App\Domain\Membership\Services;

use App\Domain\Membership\Models\UserProduct;
use Domain\User\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BulkImportBuffer
{
    private Collection $usersToUpdate;

    private Collection $membershipsToUpdate;

    private Collection $affectedUserIds;

    public function __construct()
    {
        $this->usersToUpdate = collect();
        $this->membershipsToUpdate = collect();
        $this->affectedUserIds = collect();
    }

    public function addUserForUpdate(User $user, array $updates): void
    {
        // Use email as unique key to prevent duplicate updates
        $this->usersToUpdate->put($user->email, [
            'id' => $user->id,
            'email' => $user->email,
            ...$updates,
        ]);

        $this->affectedUserIds->push($user->id);
    }

    public function trackAffectedUser(int $userId): void
    {
        $this->affectedUserIds->push($userId);
    }

    public function addMembershipForUpdate(int $membershipId, array $updates): void
    {
        $this->membershipsToUpdate->push([
            'id' => $membershipId,
            ...$updates,
        ]);
    }

    public function flush(): array
    {
        $stats = [
            'users_updated' => 0,
            'memberships_updated' => 0,
        ];

        // Bulk update users
        if ($this->usersToUpdate->isNotEmpty()) {
            // Define all possible fields that can be updated
            $updateFields = [
                'curlingio_profile_id',
                'first_name',
                'last_name',
                'name',
                'middle_initial',
                'date_of_birth',
                'gender',
                'phone',
                'secondary_phone',
                'secondary_email',
                'street_address',
                'unit',
                'city',
                'province_state',
                'postal_zip_code',
                'emergency_contact_name',
                'emergency_contact_phone',
                'show_contact_info',
            ];

            // Normalize all user records to have the same fields (set missing fields to null)
            $userBatch = $this->usersToUpdate->values()->map(function ($userData) use ($updateFields) {
                $normalized = [
                    'id' => $userData['id'],
                    'email' => $userData['email'],
                    'password' => Hash::make(Str::random(32)), // Required for new user inserts
                    'email_verified_at' => null,
                ];

                foreach ($updateFields as $field) {
                    $normalized[$field] = $userData[$field] ?? null;
                }

                return $normalized;
            })->toArray();

            User::upsert(
                $userBatch,
                ['email'], // Unique key
                $updateFields // Fields to update
            );

            $stats['users_updated'] = $this->usersToUpdate->count();
        }

        // Bulk update memberships
        if ($this->membershipsToUpdate->isNotEmpty()) {
            foreach ($this->membershipsToUpdate as $membership) {
                UserProduct::where('id', $membership['id'])
                    ->update(collect($membership)->except('id')->toArray());
            }
            $stats['memberships_updated'] = $this->membershipsToUpdate->count();
        }

        return $stats;
    }

    public function getAffectedUserIds(): Collection
    {
        return $this->affectedUserIds->unique();
    }

    public function hasData(): bool
    {
        return $this->usersToUpdate->isNotEmpty()
            || $this->membershipsToUpdate->isNotEmpty();
    }
}
