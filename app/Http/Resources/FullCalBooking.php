<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

final class FullCalBooking extends JsonResource
{
    public function toArray(Request $request): array
    {
        $date = Carbon::parse($this->date)->format('Y-m-d');
        $startTime = Carbon::parse($this->start_time)->format('H:i:s');
        $endTime = Carbon::parse($this->end_time)->format('H:i:s');

        $backgroundColor = $this->event_type->hexColor();

        return [
            'id' => $this->id,
            'title' => $this->title ?? ($this->user->name ?? 'Booking'),
            'start' => Carbon::parse($date.' '.$startTime)->format('Y-m-d\TH:i:s'),
            'end' => Carbon::parse($date.' '.$endTime)->format('Y-m-d\TH:i:s'),
            'backgroundColor' => $backgroundColor,
            'borderColor' => $backgroundColor,
            'resourceIds' => $this->area_ids ?? [],
            'extendedProps' => [
                'areas' => $this->area_names ?? '',
                'event_type' => $this->event_type,
                'payment_status' => $this->payment_status,
                'setup_instructions' => $this->setup_instructions,
            ],
        ];
    }
}
