<?php

namespace App\Providers;

use App\Models\Area;
use App\Models\Availability;
use App\Models\Booking;
use App\Models\DrawDocument;
use App\Models\RecurringPattern;
use App\Policies\AreaPolicy;
use App\Policies\AvailabilityPolicy;
use App\Policies\BookingPolicy;
use App\Policies\DrawDocumentPolicy;
use App\Policies\RecurringPatternPolicy;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Policies\RolePolicy;
use App\Policies\PermissionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,
        DrawDocument::class => DrawDocumentPolicy::class,
        RecurringPattern::class => RecurringPatternPolicy::class,
        Booking::class => BookingPolicy::class,
        Area::class => AreaPolicy::class,
        Availability::class => AvailabilityPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
} 