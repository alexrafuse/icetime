<?php

declare(strict_types=1);

namespace Domain\Board\Models;

use Database\Factories\BoardAgendaFactory;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BoardAgenda extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return BoardAgendaFactory::new();
    }

    protected $fillable = [
        'board_meeting_id',
        'title',
        'description',
        'sort_order',
        'duration_minutes',
        'presenter_user_id',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'duration_minutes' => 'integer',
        ];
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(BoardMeeting::class, 'board_meeting_id');
    }

    public function presenter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'presenter_user_id');
    }
}
