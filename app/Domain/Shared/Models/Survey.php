<?php

declare(strict_types=1);

namespace Domain\Shared\Models;

use App\Domain\Shared\Enums\RecurrencePeriod;
use Database\Factories\SurveyFactory;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Survey extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return SurveyFactory::new();
    }

    protected $fillable = [
        'title',
        'description',
        'tally_form_url',
        'is_active',
        'priority',
        'starts_at',
        'ends_at',
        'is_recurring',
        'recurrence_period',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'priority' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_recurring' => 'boolean',
            'recurrence_period' => RecurrencePeriod::class,
        ];
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function isActiveNow(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $now->isBefore($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $now->isAfter($this->ends_at)) {
            return false;
        }

        return true;
    }

    public function shouldShowToUser(User $user): bool
    {
        if (! $this->isActiveNow()) {
            return false;
        }

        $response = $this->responses()
            ->where('user_id', $user->id)
            ->first();

        if (! $response) {
            return true;
        }

        // If user marked as not interested, never show again
        if ($response->status->value === 'not_interested') {
            return false;
        }

        // If user completed and not recurring, don't show
        if ($response->status->value === 'completed' && ! $this->is_recurring) {
            return false;
        }

        // If recurring and completed, check if period has passed
        if ($this->is_recurring && $response->status->value === 'completed') {
            return $this->hasRecurrencePeriodPassed($response->responded_at);
        }

        // For "later" or "dismissed", check if enough time has passed (3 days)
        if (in_array($response->status->value, ['later', 'dismissed'])) {
            return $response->responded_at->addDays(3)->isPast();
        }

        return true;
    }

    private function hasRecurrencePeriodPassed(\DateTime $lastResponseDate): bool
    {
        if (! $this->recurrence_period) {
            return false;
        }

        $lastResponse = \Carbon\Carbon::instance($lastResponseDate);

        return match ($this->recurrence_period) {
            RecurrencePeriod::DAILY => $lastResponse->addDay()->isPast(),
            RecurrencePeriod::WEEKLY => $lastResponse->addWeek()->isPast(),
            RecurrencePeriod::MONTHLY => $lastResponse->addMonth()->isPast(),
            RecurrencePeriod::SEASON => $this->hasSeasonChanged($lastResponse),
        };
    }

    private function hasSeasonChanged(\Carbon\Carbon $lastResponse): bool
    {
        // Season changes approximately July 1st each year
        $currentSeasonStart = now()->month >= 7
            ? now()->setMonth(7)->setDay(1)->startOfDay()
            : now()->subYear()->setMonth(7)->setDay(1)->startOfDay();

        return $lastResponse->isBefore($currentSeasonStart);
    }

    public static function getNextForUser(User $user): ?self
    {
        return self::query()
            ->where('is_active', true)
            ->orderBy('priority')
            ->orderBy('created_at')
            ->get()
            ->first(fn (Survey $survey) => $survey->shouldShowToUser($user));
    }
}
