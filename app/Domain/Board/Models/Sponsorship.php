<?php

declare(strict_types=1);

namespace Domain\Board\Models;

use App\Domain\Membership\Models\Season;
use Database\Factories\SponsorshipFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Sponsorship extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return SponsorshipFactory::new();
    }

    protected $fillable = [
        'sponsor_id',
        'sponsorship_level_id',
        'season_id',
        'amount_cents',
        'start_date',
        'end_date',
        'notes',
        'is_confirmed',
        'is_paid',
        'reference',
    ];

    protected function casts(): array
    {
        return [
            'amount_cents' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_confirmed' => 'boolean',
            'is_paid' => 'boolean',
        ];
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(Sponsor::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(SponsorshipLevel::class, 'sponsorship_level_id');
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function scopeForSeason(Builder $query, int $seasonId): Builder
    {
        return $query->where('season_id', $seasonId);
    }
}
