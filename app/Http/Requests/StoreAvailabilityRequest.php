<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'area_id' => ['required', 'exists:areas,id'],
            'day_of_week' => ['nullable', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'is_available' => ['boolean'],
            'note' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->validateUniqueAvailability()) {
                $validator->errors()->add(
                    'availability',
                    'An availability for this area and time period already exists.'
                );
            }
        });
    }

    public function validateUniqueAvailability(): bool
    {
        $query = \Domain\Facility\Models\Availability::where('area_id', $this->area_id);

        if ($this->day_of_week !== null) {
            // Weekly availability - check for duplicate day_of_week
            return ! $query->where('day_of_week', $this->day_of_week)->exists();
        } else {
            // Specific date availability - check for same date
            $date = Carbon::parse($this->start_time)->format('Y-m-d');

            return ! $query->whereNull('day_of_week')
                ->whereDate('start_time', $date)
                ->exists();
        }
    }
}
