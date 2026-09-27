<?php

namespace App\Providers;

use App\Policies\AreaPolicy;
use App\Policies\AvailabilityPolicy;
use App\Policies\BoardMeetingPolicy;
use App\Policies\BoardMinutePolicy;
use App\Policies\BookingPolicy;
use App\Policies\ClubBylawPolicy;
use App\Policies\ClubPolicyPolicy;
use App\Policies\DonorPolicy;
use App\Policies\DrawDocumentPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\ProductPolicy;
use App\Policies\RecurringPatternPolicy;
use App\Policies\RolePolicy;
use App\Policies\SeasonPolicy;
use App\Policies\SpareAvailabilityPolicy;
use App\Policies\SponsorPolicy;
use App\Policies\SponsorshipLevelPolicy;
use App\Policies\UserPolicy;
use Domain\Board\Models\BoardMeeting;
use Domain\Board\Models\BoardMinute;
use Domain\Board\Models\ClubBylaw;
use Domain\Board\Models\ClubPolicy;
use Domain\Board\Models\Donor;
use Domain\Board\Models\Sponsor;
use Domain\Board\Models\SponsorshipLevel;
use Domain\Booking\Models\Booking;
use Domain\Booking\Models\RecurringPattern;
use Domain\Facility\Models\Area;
use Domain\Facility\Models\Availability;
use Domain\Facility\Models\SpareAvailability;
use Domain\Membership\Models\Product;
use Domain\Membership\Models\Season;
use Domain\Shared\Models\DrawDocument;
use Domain\User\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,
        DrawDocument::class => DrawDocumentPolicy::class,
        RecurringPattern::class => RecurringPatternPolicy::class,
        Booking::class => BookingPolicy::class,
        Area::class => AreaPolicy::class,
        Availability::class => AvailabilityPolicy::class,
        Product::class => ProductPolicy::class,
        Season::class => SeasonPolicy::class,
        SpareAvailability::class => SpareAvailabilityPolicy::class,
        BoardMeeting::class => BoardMeetingPolicy::class,
        BoardMinute::class => BoardMinutePolicy::class,
        Sponsor::class => SponsorPolicy::class,
        SponsorshipLevel::class => SponsorshipLevelPolicy::class,
        Donor::class => DonorPolicy::class,
        ClubPolicy::class => ClubPolicyPolicy::class,
        ClubBylaw::class => ClubBylawPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
