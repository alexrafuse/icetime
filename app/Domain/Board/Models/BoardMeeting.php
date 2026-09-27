<?php

declare(strict_types=1);

namespace Domain\Board\Models;

use Database\Factories\BoardMeetingFactory;
use Domain\Board\Enums\MeetingStatus;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class BoardMeeting extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return BoardMeetingFactory::new();
    }

    protected $fillable = [
        'title',
        'description',
        'scheduled_at',
        'location',
        'status',
        'called_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'status' => MeetingStatus::class,
        ];
    }

    public function calledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'called_by_user_id');
    }

    public function agendas(): HasMany
    {
        return $this->hasMany(BoardAgenda::class)->orderBy('sort_order');
    }

    public function minutes(): HasOne
    {
        return $this->hasOne(BoardMinute::class);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('scheduled_at', '>=', now())
            ->where('status', MeetingStatus::SCHEDULED);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', MeetingStatus::COMPLETED);
    }

    public function isCompleted(): bool
    {
        return $this->status === MeetingStatus::COMPLETED;
    }

    public function complete(): void
    {
        $this->update(['status' => MeetingStatus::COMPLETED]);
    }

    public function cancel(): void
    {
        $this->update(['status' => MeetingStatus::CANCELLED]);
    }
}
