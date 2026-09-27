<?php

declare(strict_types=1);

namespace Domain\Board\Models;

use App\Domain\Membership\Models\Season;
use Database\Factories\DonationFactory;
use Domain\Board\Enums\DonationType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Donation extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return DonationFactory::new();
    }

    protected $fillable = [
        'donor_id',
        'type',
        'amount_cents',
        'description',
        'donated_at',
        'season_id',
        'receipt_number',
        'is_tax_receipted',
    ];

    protected function casts(): array
    {
        return [
            'type' => DonationType::class,
            'amount_cents' => 'integer',
            'donated_at' => 'date',
            'is_tax_receipted' => 'boolean',
        ];
    }

    public function donor(): BelongsTo
    {
        return $this->belongsTo(Donor::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function scopeForSeason(Builder $query, int $seasonId): Builder
    {
        return $query->where('season_id', $seasonId);
    }

    public function formattedAmount(): string
    {
        return $this->amount_cents
            ? '$'.number_format($this->amount_cents / 100, 2)
            : 'N/A';
    }
}
