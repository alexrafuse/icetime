<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $date = Carbon::parse($this->date)->format('Y-m-d');
        $startTime = Carbon::parse($this->start_time)->format('H:i:s');
        $endTime = Carbon::parse($this->end_time)->format('H:i:s');

        return [
            'id' => $this->id,
            'owner_name' => $this->user->name,
            'title' => $this->title ?? ' no title',
            'start' => Carbon::parse($date.' '.$startTime)->format('Y-m-d\TH:i:s'),
            'end' => Carbon::parse($date.' '.$endTime)->format('Y-m-d\TH:i:s'),
            'backgroundColor' => $this->event_type->hexColor(),
            'borderColor' => $this->event_type->hexColor(),
            'areas' => $this->whenLoaded('areas', function () {
                return $this->areas->map(function ($area) {
                    return [
                        'id' => $area->id,
                        'name' => $area->name,
                    ];
                });
            }),
            'event_type' => $this->event_type,
            'payment_status' => $this->payment_status,
            'setup_instructions' => $this->setup_instructions,

        ];
    }
}
