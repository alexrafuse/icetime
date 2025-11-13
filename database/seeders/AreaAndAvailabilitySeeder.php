<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\Carbon;
use Domain\Facility\Models\Area;
use Domain\Facility\Models\Availability;
use Illuminate\Database\Seeder;

class AreaAndAvailabilitySeeder extends Seeder
{
    private const AREAS = [
        'Ice Sheets' => [
            'Sheet A',
            'Sheet B',
            'Sheet C',
            'Sheet D',
        ],
        'Other Areas' => [
            'Lounge',
            'Kitchen',

        ],
    ];

    private const WEEKDAY_HOURS = [
        'start' => '09:00',
        'end' => '22:00',
    ];

    private const WEEKEND_HOURS = [
        'start' => '12:00',
        'end' => '18:00',
    ];

    public function run(): void
    {
        // Create Ice Sheets
        foreach (self::AREAS['Ice Sheets'] as $sheetName) {
            $area = Area::create([
                'name' => $sheetName,
                'description' => "Ice sheet {$sheetName}",
                'is_active' => true,
                'base_price' => 20,
            ]);
            $this->createAvailabilities($area);
        }

        // Create Other Areas
        foreach (self::AREAS['Other Areas'] as $areaName) {
            $area = Area::create([
                'name' => $areaName,
                'description' => "{$areaName} area",
                'is_active' => true,
            ]);
            $this->createAvailabilities($area);
        }
    }

    private function createAvailabilities(Area $area): void
    {
        $baseDate = Carbon::now()->startOfWeek();

        // Create weekday availabilities (Monday = 1 to Friday = 5)
        for ($day = 1; $day <= 5; $day++) {
            Availability::create([
                'area_id' => $area->id,
                'day_of_week' => $day,
                'start_time' => $baseDate->copy()->addDays($day - 1)->setTimeFromTimeString(self::WEEKDAY_HOURS['start']),
                'end_time' => $baseDate->copy()->addDays($day - 1)->setTimeFromTimeString(self::WEEKDAY_HOURS['end']),
                'is_available' => true,
            ]);
        }

        // Create weekend availabilities (Saturday = 6, Sunday = 0)
        foreach ([6, 0] as $day) {
            Availability::create([
                'area_id' => $area->id,
                'day_of_week' => $day,
                'start_time' => $baseDate->copy()->addDays($day === 0 ? 6 : 5)->setTimeFromTimeString(self::WEEKEND_HOURS['start']),
                'end_time' => $baseDate->copy()->addDays($day === 0 ? 6 : 5)->setTimeFromTimeString(self::WEEKEND_HOURS['end']),
                'is_available' => true,
            ]);
        }
    }
}
