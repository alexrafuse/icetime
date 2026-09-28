<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

final class IceCalendarRangeRequest extends FormRequest
{
    /**
     * FullCalendar asks for at most six weeks in month view. The cap keeps the public feed from dumping the whole season.
     */
    public const MAX_RANGE_DAYS = 45;

    public function rules(): array
    {
        return [
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start', 'before_or_equal:'.$this->maxEnd()],
        ];
    }

    public function startDate(): Carbon
    {
        return Carbon::parse($this->validated('start'))->startOfDay();
    }

    public function endDate(): Carbon
    {
        return Carbon::parse($this->validated('end'))->startOfDay();
    }

    private function maxEnd(): string
    {
        $start = strtotime((string) $this->input('start')) ?: time();

        return Carbon::createFromTimestamp($start)->addDays(self::MAX_RANGE_DAYS)->toDateTimeString();
    }
}
