<?php

declare(strict_types=1);

namespace Domain\Shared\Models;

use App\Enums\NotificationType;
use Database\Factories\NotificationFactory;
use Domain\Booking\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return NotificationFactory::new();
    }

    protected $fillable = [
        'booking_id',
        'recipient',
        'type',
        'sent_at',
    ];

    protected $casts = [
        'type' => NotificationType::class,
        'sent_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
