<?php

declare(strict_types=1);

namespace Domain\Board\Models;

use Database\Factories\BoardMinuteFactory;
use Domain\Board\Enums\MinuteStatus;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

final class BoardMinute extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return BoardMinuteFactory::new();
    }

    protected $fillable = [
        'board_meeting_id',
        'content',
        'status',
        'recorded_by_user_id',
        'approved_at',
        'approved_by_user_id',
        'file_path',
    ];

    protected function casts(): array
    {
        return [
            'status' => MinuteStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(BoardMeeting::class, 'board_meeting_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', MinuteStatus::APPROVED);
    }

    public function approve(User $user): void
    {
        $this->update([
            'status' => MinuteStatus::APPROVED,
            'approved_at' => now(),
            'approved_by_user_id' => $user->id,
        ]);
    }

    public function reject(): void
    {
        $this->update([
            'status' => MinuteStatus::REJECTED,
            'approved_at' => null,
            'approved_by_user_id' => null,
        ]);
    }

    public function submitForApproval(): void
    {
        $this->update(['status' => MinuteStatus::PENDING_APPROVAL]);
    }

    public function isApproved(): bool
    {
        return $this->status === MinuteStatus::APPROVED;
    }

    public function isPending(): bool
    {
        return $this->status === MinuteStatus::PENDING_APPROVAL;
    }

    public function getFileUrl(): ?string
    {
        return $this->file_path
            ? Storage::disk('public')->url($this->file_path)
            : null;
    }
}
