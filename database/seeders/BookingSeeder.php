<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Area;
use App\Models\User;
use App\Models\Booking;
use App\Models\Availability;
use App\Enums\EventType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class BookingSeeder extends Seeder
{
    public function run()
    {
       $areas = Area::where('name', 'like', '%Sheet%')->with('availabilities')->get();


       $startDate = Carbon::now()->addDays(1);
       $endDate = Carbon::now()->addDays(90);

       for ($date = $startDate; $date <= $endDate; $date->addDay()) {
       

        // generate a booking with a random start time and end time 
        // and random number of areas

        $randomAreas = $areas->random(fake()->numberBetween(1, 4));

        // grab the availabilities for the areas and generate a random start time and end time within the availability

        $randomAvailability = $randomAreas->first()->availabilities->random();

        $startTime = Carbon::parse($randomAvailability->start_time)->addHours(fake()->numberBetween(1, 6));
        $endTime = Carbon::parse($startTime)->addHours(fake()->numberBetween(1, 2));

        Booking::factory()->withAreas($randomAreas)->create(
            

            [
                'date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]
        );





       }
      

    }
}