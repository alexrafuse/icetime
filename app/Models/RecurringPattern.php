<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EventType;
use App\Enums\FrequencyType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

final class RecurringPattern extends Model
{
    use HasFactory;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 