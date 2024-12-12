<?php

declare(strict_types=1);

namespace App\Models;

use App\Http\Requests\StoreAvailabilityRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Validation\ValidationException;

class Availability extends Model
{
    use HasFactory;

    protected $fillable = [
        'area_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_available',
        'note',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_available' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($availability) {
            $request = new StoreAvailabilityRequest();
            $request->merge([
                'area_id' => $availability->area_id,
                'day_of_week' => $availability->day_of_week,
                'start_time' => $availability->start_time,
                'end_time' => $availability->end_time,
                'is_available' => $availability->is_available,
                'note' => $availability->note,
            ]);

            if (!$request->validateUniqueAvailability()) {
                throw ValidationException::withMessages([
                    'availability' => ['An availability for this area and time period already exists.'],
                ]);
            }
        });
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }
} 