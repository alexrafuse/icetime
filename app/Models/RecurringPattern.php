<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

final class RecurringPattern extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'frequency',
        'interval',
        'start_date',
        'end_date',
        'days_of_week',
        'excluded_dates',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'days_of_week' => 'array',
        'excluded_dates' => 'array',
        'interval' => 'integer',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
} 