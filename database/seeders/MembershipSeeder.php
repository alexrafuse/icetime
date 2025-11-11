<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Membership\Actions\AssignProductToUserAction;
use App\Domain\Membership\Enums\MembershipStatus;
use App\Domain\Membership\Models\Product;
use App\Domain\Membership\Models\Season;
use App\Enums\RoleEnum;
use Domain\User\Models\User;
use Illuminate\Database\Seeder;

class MembershipSeeder extends Seeder
{
    public function run(): void
    {
        $season = Season::query()->where('slug', '2025-2026')->first();

        if (! $season) {
            $this->command->error('Season 2025-2026 not found. Please run SeasonsSeeder first.');

            return;
        }

        $assignAction = app(AssignProductToUserAction::class);

        $activeProduct = Product::query()
            ->where('season_id', $season->id)
            ->where('curlingio_id', 9757)
            ->first();

        $socialProduct = Product::query()
            ->where('season_id', $season->id)
            ->where('curlingio_id', 9768)
            ->first();

        $studentProduct = Product::query()
            ->where('season_id', $season->id)
            ->where('curlingio_id', 9766)
            ->first();

        $lockerProduct = Product::query()
            ->where('season_id', $season->id)
            ->where('curlingio_id', 9769)
            ->first();

        $keyFobProduct = Product::query()
            ->where('season_id', $season->id)
            ->where('curlingio_id', 9770)
            ->first();

        $adminUser = User::query()->where('email', 'hello@stacked.dev')->first();

        if ($adminUser && $activeProduct) {
            $assignAction->execute(
                user: $adminUser,
                product: $activeProduct,
                season: $season,
                status: MembershipStatus::ACTIVE
            );

            if ($lockerProduct) {
                $assignAction->execute(
                    user: $adminUser,
                    product: $lockerProduct,
                    season: $season,
                    status: MembershipStatus::ACTIVE
                );
            }

            $this->command->info('Assigned Active membership + Locker to admin user');
        }

        $memberWithSocial = User::factory()->create([
            'name' => 'Social Member',
            'email' => 'social@example.com',
        ]);
        $memberWithSocial->assignRole(RoleEnum::MEMBER->value);

        if ($socialProduct) {
            $assignAction->execute(
                user: $memberWithSocial,
                product: $socialProduct,
                season: $season,
                status: MembershipStatus::ACTIVE
            );
            $this->command->info('Created user with Social membership');
        }

        $studentMember = User::factory()->create([
            'name' => 'Student Member',
            'email' => 'student@example.com',
        ]);
        $studentMember->assignRole(RoleEnum::MEMBER->value);

        if ($studentProduct && $keyFobProduct) {
            $assignAction->execute(
                user: $studentMember,
                product: $studentProduct,
                season: $season,
                status: MembershipStatus::ACTIVE
            );
            $assignAction->execute(
                user: $studentMember,
                product: $keyFobProduct,
                season: $season,
                status: MembershipStatus::ACTIVE
            );
            $this->command->info('Created student with Student membership + Key Fob');
        }

        $pendingMember = User::factory()->create([
            'name' => 'Pending Member',
            'email' => 'pending@example.com',
        ]);
        $pendingMember->assignRole(RoleEnum::MEMBER->value);

        if ($activeProduct) {
            $assignAction->execute(
                user: $pendingMember,
                product: $activeProduct,
                season: $season,
                status: MembershipStatus::PENDING
            );
            $this->command->info('Created user with Pending membership');
        }

        $expiredMember = User::factory()->create([
            'name' => 'Expired Member',
            'email' => 'expired@example.com',
        ]);
        $expiredMember->assignRole(RoleEnum::MEMBER->value);

        if ($activeProduct) {
            $assignAction->execute(
                user: $expiredMember,
                product: $activeProduct,
                season: $season,
                expiresAt: now()->subDays(30),
                status: MembershipStatus::EXPIRED
            );
            $this->command->info('Created user with Expired membership');
        }

        $noMembership = User::factory()->create([
            'name' => 'No Membership User',
            'email' => 'nomembership@example.com',
        ]);
        $noMembership->assignRole(RoleEnum::MEMBER->value);
        $this->command->info('Created user with no membership');

        $this->command->info('Membership seeding completed!');
    }
}
