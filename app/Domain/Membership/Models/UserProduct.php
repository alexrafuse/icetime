<?php

declare(strict_types=1);

namespace App\Domain\Membership\Models;

use App\Domain\Membership\Enums\MembershipStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserProduct extends Pivot
{
    use HasFactory;

    protected $table = 'user_products';

    public $incrementing = true;

    protected $fillable = [
        'user_id',
        'product_id',
        'season_id',
        'price_paid_cents',
        'refund_amount_cents',
        'refund_reason',
        'refunded_at',
        'assigned_at',
        'expires_at',
        'status',
        'purchase_reference',
        'metadata',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'expires_at' => 'datetime',
        'refunded_at' => 'datetime',
        'status' => MembershipStatus::class,
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $userProduct) {
            if (empty($userProduct->assigned_at)) {
                $userProduct->assigned_at = now();
            }

            if (empty($userProduct->status)) {
                $userProduct->status = MembershipStatus::ACTIVE;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\Domain\User\Models\User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function isActive(): bool
    {
        return $this->status === MembershipStatus::ACTIVE && ! $this->hasExpired();
    }

    public function hasExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function scopeActive($query)
    {
        return $query->where('status', MembershipStatus::ACTIVE)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeExpired($query)
    {
        return $query->where('status', MembershipStatus::EXPIRED)
            ->orWhere(function ($q) {
                $q->whereNotNull('expires_at')
                    ->where('expires_at', '<=', now());
            });
    }

    public function scopeForSeason($query, Season $season)
    {
        return $query->where('season_id', $season->id);
    }

    public function scopeForUser($query, \Domain\User\Models\User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function markAsExpired(): void
    {
        $this->update(['status' => MembershipStatus::EXPIRED]);
    }

    public function markAsCancelled(): void
    {
        $this->update(['status' => MembershipStatus::CANCELLED]);
    }

    public function activate(): void
    {
        $this->update(['status' => MembershipStatus::ACTIVE]);
    }

    public function getNetPriceCents(): int
    {
        $gross = $this->price_paid_cents ?? $this->product->price_cents;
        $refund = $this->refund_amount_cents ?? 0;

        return $gross - $refund;
    }

    public function isFullyRefunded(): bool
    {
        if (! $this->refund_amount_cents) {
            return false;
        }

        $gross = $this->price_paid_cents ?? $this->product->price_cents;

        return $this->refund_amount_cents >= $gross;
    }

    public function isPartiallyRefunded(): bool
    {
        if (! $this->refund_amount_cents) {
            return false;
        }

        return ! $this->isFullyRefunded();
    }
}
