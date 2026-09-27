<?php

declare(strict_types=1);

namespace Domain\Board\Models;

use Database\Factories\ClubPolicyFactory;
use Domain\Board\Enums\DocumentStatus;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class ClubPolicy extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return ClubPolicyFactory::new();
    }

    protected $fillable = [
        'title',
        'slug',
        'content',
        'file_path',
        'version',
        'status',
        'effective_date',
        'parent_id',
        'approved_by_user_id',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'status' => DocumentStatus::class,
            'effective_date' => 'date',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        self::creating(function (self $policy) {
            $policy->slug ??= Str::slug($policy->title);
        });
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', DocumentStatus::PUBLISHED);
    }

    public function scopeCurrentVersions(Builder $query): Builder
    {
        return $query->whereDoesntHave('revisions', fn (Builder $q) => $q->where('status', DocumentStatus::PUBLISHED));
    }

    public function publish(User $user): void
    {
        $this->update([
            'status' => DocumentStatus::PUBLISHED,
            'published_at' => now(),
            'approved_by_user_id' => $user->id,
        ]);
    }

    public function archive(): void
    {
        $this->update(['status' => DocumentStatus::ARCHIVED]);
    }

    public function createRevision(): self
    {
        return self::create([
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'file_path' => $this->file_path,
            'version' => $this->version + 1,
            'status' => DocumentStatus::DRAFT,
            'effective_date' => null,
            'parent_id' => $this->id,
        ]);
    }

    public function isPublished(): bool
    {
        return $this->status === DocumentStatus::PUBLISHED;
    }

    public function isDraft(): bool
    {
        return $this->status === DocumentStatus::DRAFT;
    }

    public function getFileUrl(): ?string
    {
        return $this->file_path
            ? Storage::disk('public')->url($this->file_path)
            : null;
    }
}
