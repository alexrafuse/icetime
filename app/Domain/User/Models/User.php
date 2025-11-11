<?php

namespace Domain\User\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Domain\Membership\Enums\MembershipStatus;
use App\Domain\Membership\Enums\MembershipTier;
use App\Domain\Membership\Models\Product;
use App\Domain\Membership\Models\Season;
use App\Domain\Membership\Models\UserProduct;
use Database\Factories\UserFactory;
use Domain\Booking\Models\Booking;
use Domain\Facility\Models\SpareAvailability;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected static function newFactory(): Factory
    {
        return UserFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [

    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'current_membership_status' => MembershipStatus::class,
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function spareAvailability(): HasOne
    {
        return $this->hasOne(SpareAvailability::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'user_products')
            ->using(UserProduct::class)
            ->withPivot(['assigned_at', 'expires_at', 'status', 'purchase_reference', 'metadata'])
            ->withTimestamps();
    }

    public function userProducts(): HasMany
    {
        return $this->hasMany(UserProduct::class);
    }

    public function seasons(): BelongsToMany
    {
        return $this->belongsToMany(Season::class, 'user_products')
            ->using(UserProduct::class)
            ->withPivot(['assigned_at', 'expires_at', 'status', 'purchase_reference', 'metadata'])
            ->withTimestamps();
    }

    public function hasActiveMembership(?Season $season = null): bool
    {
        $season = $season ?? Season::query()->where('is_current', true)->first();

        if (! $season) {
            return false;
        }

        return $this->userProducts()
            ->forSeason($season)
            ->active()
            ->whereHas('product', function ($query) {
                $query->memberships();
            })
            ->exists();
    }

    public function getMembershipProducts(Season $season): Collection
    {
        return $this->userProducts()
            ->forSeason($season)
            ->active()
            ->with('product')
            ->get()
            ->pluck('product')
            ->filter(fn ($product) => $product->isMembership());
    }

    public function getHighestMembershipTier(?Season $season = null): ?MembershipTier
    {
        $season = $season ?? Season::query()->where('is_current', true)->first();

        if (! $season) {
            return null;
        }

        $memberships = $this->getMembershipProducts($season);

        if ($memberships->isEmpty()) {
            return null;
        }

        return $memberships
            ->map(fn ($product) => $product->membership_tier)
            ->filter()
            ->sortByDesc(fn ($tier) => $tier->getLevel())
            ->first();
    }

    public function canViewMembershipStatus(self $targetUser): bool
    {
        return $this->id === $targetUser->id || $this->can(\App\Enums\Permission::VIEW_MEMBERSHIPS->value);
    }
}
