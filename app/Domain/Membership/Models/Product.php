<?php

declare(strict_types=1);

namespace App\Domain\Membership\Models;

use App\Domain\Membership\Enums\MembershipCapacity;
use App\Domain\Membership\Enums\MembershipTier;
use App\Domain\Membership\Enums\ProductType;
use Domain\Shared\ValueObjects\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'season_id',
        'curlingio_id',
        'name',
        'slug',
        'description',
        'product_type',
        'membership_tier',
        'capacity',
        'price_cents',
        'currency',
        'is_available',
        'metadata',
    ];

    protected $casts = [
        'product_type' => ProductType::class,
        'membership_tier' => MembershipTier::class,
        'capacity' => MembershipCapacity::class,
        'price_cents' => 'integer',
        'is_available' => 'boolean',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function userProducts(): HasMany
    {
        return $this->hasMany(UserProduct::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(\Domain\User\Models\User::class, 'user_products')
            ->using(UserProduct::class)
            ->withPivot(['assigned_at', 'expires_at', 'status', 'purchase_reference', 'metadata'])
            ->withTimestamps();
    }

    public function price(): Money
    {
        return new Money($this->price_cents, $this->currency);
    }

    public function isMembership(): bool
    {
        return $this->product_type === ProductType::MEMBERSHIP;
    }

    public function isLeague(): bool
    {
        return $this->product_type === ProductType::LEAGUE;
    }

    public function isAddon(): bool
    {
        return $this->product_type === ProductType::ADDON;
    }

    public function isProgram(): bool
    {
        return $this->product_type === ProductType::PROGRAM;
    }

    public function grantsActiveStatus(): bool
    {
        return $this->isMembership() && $this->membership_tier?->grantsActiveStatus() === true;
    }

    public function isCoupleCapacity(): bool
    {
        return $this->capacity === MembershipCapacity::COUPLE;
    }

    public function getMaxMembers(): int
    {
        return $this->capacity?->getMaxMembers() ?? 1;
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeMemberships($query)
    {
        return $query->where('product_type', ProductType::MEMBERSHIP);
    }

    public function scopeLeagues($query)
    {
        return $query->where('product_type', ProductType::LEAGUE);
    }

    public function scopeAddons($query)
    {
        return $query->where('product_type', ProductType::ADDON);
    }

    public function scopePrograms($query)
    {
        return $query->where('product_type', ProductType::PROGRAM);
    }

    public function scopeForSeason($query, Season $season)
    {
        return $query->where('season_id', $season->id);
    }
}
