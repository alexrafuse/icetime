<?php

declare(strict_types=1);

namespace Domain\Facility\Models;

use Database\Factories\AreaFactory;
use Domain\Booking\Models\Booking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return AreaFactory::new();
    }

    public const ICE_SHEET_NAME_PREFIX = 'Sheet ';

    protected $fillable = [
        'name',
        'base_price',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class)
            ->withPivot('custom_price')
            ->withTimestamps();
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeIceSheets(Builder $query): Builder
    {
        return $query->where('name', 'like', self::ICE_SHEET_NAME_PREFIX.'%');
    }
}
