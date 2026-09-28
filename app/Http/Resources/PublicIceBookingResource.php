<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Public, FullCalendar-shaped event. Never expose the renter, payment status or setup notes here.
 */
final class PublicIceBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $date = $this->date->format('Y-m-d');
        $color = $this->event_type->hexColor();

        return [
            'id' => $this->id,
            'title' => $this->publicTitle(),
            'start' => $date.'T'.$this->start_time->format('H:i:s'),
            'end' => $date.'T'.$this->end_time->format('H:i:s'),
            'backgroundColor' => $color,
            'borderColor' => $color,
            'extendedProps' => [
                'event_type' => $this->event_type->getLabel(),
                'sheets' => $this->iceSheets->pluck('name')->sort()->values(),
            ],
        ];
    }
}
