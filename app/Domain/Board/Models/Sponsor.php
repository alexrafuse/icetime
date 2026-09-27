<?php

declare(strict_types=1);

namespace Domain\Board\Models;

use Database\Factories\SponsorFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Domain\User\Models\User;

final class Sponsor extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return SponsorFactory::new();
    }

    protected $fillable = [
        'name',
        'contact_name',
        'contact_email',
        'contact_phone',
        'website',
        'logo_path',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function sponsorships(): HasMany
    {
        return $this->hasMany(Sponsorship::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['role', 'notes'])
            ->withTimestamps();
    }

    public function currentSeasonSponsorship(): HasOne
    {
        return $this->hasOne(Sponsorship::class)
            ->whereHas('season', fn (Builder $query) => $query->where('is_current', true))
            ->latestOfMany();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function totalContributedCents(): int
    {
        return (int) $this->sponsorships()->sum('amount_cents');
    }

    public function yearsSponsoring(): int
    {
        return (int) $this->sponsorships()->distinct('season_id')->count('season_id');
    }

    public function getLogoUrl(): ?string
    {
        return $this->logo_path
            ? Storage::disk('public')->url($this->logo_path)
            : null;
    }
}
