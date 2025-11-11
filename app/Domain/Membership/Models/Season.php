<?php

declare(strict_types=1);

namespace App\Domain\Membership\Models;

use App\Domain\Membership\ValueObjects\SeasonPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Season extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'start_date',
        'end_date',
        'is_current',
        'is_registration_open',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'is_registration_open' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $season) {
            if (empty($season->slug)) {
                $season->slug = Str::slug($season->name);
            }
        });

        static::saving(function (self $season) {
            if ($season->is_current && $season->isDirty('is_current')) {
                static::query()->where('id', '!=', $season->id)->update(['is_current' => false]);
            }
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
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

    public function period(): SeasonPeriod
    {
        return new SeasonPeriod($this->start_date, $this->end_date);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    public function scopePast($query)
    {
        return $query->where('end_date', '<', now());
    }

    public function markAsCurrent(): void
    {
        $this->update(['is_current' => true]);
    }

    public function openRegistration(): void
    {
        $this->update(['is_registration_open' => true]);
    }

    public function closeRegistration(): void
    {
        $this->update(['is_registration_open' => false]);
    }

    public function isCurrent(): bool
    {
        return $this->is_current;
    }

    public function isRegistrationOpen(): bool
    {
        return $this->is_registration_open;
    }
}
