<?php

namespace App\Providers;

use App\Policies\AreaPolicy;
use App\Policies\AvailabilityPolicy;
use App\Policies\BookingPolicy;
use App\Policies\DrawDocumentPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\ProductPolicy;
use App\Policies\RecurringPatternPolicy;
use App\Policies\RolePolicy;
use App\Policies\SeasonPolicy;
use App\Policies\SpareAvailabilityPolicy;
use App\Policies\UserPolicy;
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
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
