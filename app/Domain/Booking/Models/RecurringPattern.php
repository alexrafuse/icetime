<?php

declare(strict_types=1);

namespace Domain\Booking\Models;

use App\Enums\FrequencyType;
use Database\Factories\RecurringPatternFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class RecurringPattern extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return RecurringPatternFactory::new();
    }

    protected $guarded = [

    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'days_of_week' => 'array',
        'excluded_dates' => 'array',
        'frequency' => FrequencyType::class,
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'recurring_pattern_id');
    }

    public function primaryBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'primary_booking_id');
    }
}
