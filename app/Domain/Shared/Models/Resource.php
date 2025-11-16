<?php

declare(strict_types=1);

namespace Domain\Shared\Models;

use App\Domain\Shared\Enums\ResourceCategory;
use Database\Factories\ResourceFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

final class Resource extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return ResourceFactory::new();
    }

    protected $fillable = [
        'title',
        'description',
        'category',
        'type',
        'url',
        'file_path',
        'visibility',
        'priority',
        'is_active',
        'valid_from',
        'valid_until',
    ];

    protected function casts(): array
    {
        return [
            'category' => ResourceCategory::class,
            'priority' => 'integer',
            'is_active' => 'boolean',
            'valid_from' => 'date',
            'valid_until' => 'date',
        ];
    }

    public function getFileUrl(): ?string
    {
        if ($this->file_path) {
            return Storage::disk('public')->url($this->file_path);
        }

        return null;
    }

    public function isFile(): bool
    {
        return $this->type === 'file';
    }

    public function isUrl(): bool
    {
        return $this->type === 'url';
    }

    public function isVisibleToAll(): bool
    {
        return $this->visibility === 'all';
    }

    public function isAdminStaffOnly(): bool
    {
        return $this->visibility === 'admin_staff_only';
    }

    public function isCurrentlyValid(): bool
    {
        $now = now();

        if ($this->valid_from && $now->isBefore($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->isAfter($this->valid_until)) {
            return false;
        }

        return true;
    }
}
