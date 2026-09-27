<?php

declare(strict_types=1);

namespace Domain\Board\Models;

use Database\Factories\DonorFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

final class Donor extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return DonorFactory::new();
    }

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'is_anonymous',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
        ];
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    public function scopeNonAnonymous(Builder $query): Builder
    {
        return $query->where('is_anonymous', false);
    }

    public function totalDonatedCents(): int
    {
        return (int) $this->donations()->sum('amount_cents');
    }

    public function donationsBySeason(): Collection
    {
        return $this->donations()
            ->with('season')
            ->get()
            ->groupBy('season_id');
    }
}
