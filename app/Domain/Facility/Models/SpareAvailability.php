<?php

declare(strict_types=1);

namespace Domain\Facility\Models;

use Database\Factories\SpareAvailabilityFactory;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpareAvailability extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return SpareAvailabilityFactory::new();
    }

    protected $fillable = [
        'user_id',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'phone_number',
        'sms_enabled',
        'call_enabled',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'monday' => 'boolean',
        'tuesday' => 'boolean',
        'wednesday' => 'boolean',
        'thursday' => 'boolean',
        'friday' => 'boolean',
        'sms_enabled' => 'boolean',
        'call_enabled' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
