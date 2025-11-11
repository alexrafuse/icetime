<?php

declare(strict_types=1);

namespace Domain\Booking\Models;

use App\Enums\EventType;
use App\Enums\PaymentStatus;
use Database\Factories\BookingFactory;
use Domain\Facility\Models\Area;
use Domain\Payment\Models\Payment;
use Domain\Shared\Models\Notification;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return BookingFactory::new();
    }

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'event_type' => EventType::class,
        'payment_status' => PaymentStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function areas(): BelongsToMany
    {
        return $this->belongsToMany(Area::class)
            ->withPivot('custom_price')
            ->withTimestamps();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function recurringPattern(): BelongsTo
    {
        return $this->belongsTo(RecurringPattern::class);
    }
}
