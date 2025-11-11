<?php

declare(strict_types=1);

namespace Domain\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

final class DrawDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'day_of_week',
        'file_path',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
        'day_of_week' => 'integer', // 2=Tuesday, 3=Wednesday, 4=Thursday, 5=Friday
    ];

    public static function getDayNames(): array
    {
        return [
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
        ];
    }

    public function getDayNameAttribute(): string
    {
        return self::getDayNames()[$this->day_of_week] ?? 'Unknown';
    }

    public function getFileUrl(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }
}
