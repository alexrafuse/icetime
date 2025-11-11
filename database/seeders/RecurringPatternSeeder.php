<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\FrequencyType;
use Carbon\Carbon;
use Domain\Booking\Models\RecurringPattern;
use Illuminate\Database\Seeder;

class RecurringPatternSeeder extends Seeder
{
    public function run(): void
    {

        // $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        // collect($daysOfWeek)->each(function ($day) {
        //     RecurringPattern::create([
        //         'title' => "Every {$day}",
        //         'start_date' => Carbon::now(),
        //         'end_date' => Carbon::now()->addYear(),
        //         'frequency' => FrequencyType::WEEKLY,
        //         'days_of_week' => [$day],
        //         'excluded_dates' => [],
        //     ]);
        // });

    }
}
