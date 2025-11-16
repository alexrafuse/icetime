<?php

declare(strict_types=1);

namespace Domain\Shared\Models;

use App\Domain\Shared\Enums\SurveyStatus;
use Database\Factories\SurveyResponseFactory;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SurveyResponse extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return SurveyResponseFactory::new();
    }

    protected $fillable = [
        'survey_id',
        'user_id',
        'status',
        'responded_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => SurveyStatus::class,
            'responded_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
