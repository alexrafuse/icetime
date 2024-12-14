<?php

use App\Models\Area;
use App\Models\Booking;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use App\Services\BookingValidationService;

Route::get('/', function () {

    return Booking::all();

return true;

    // $validationService = app(BookingValidationService::class);

    // $areas = Area::findMany([1]);
    // $date = Carbon::parse('2024-12-19');
    // $startTime = Carbon::parse('10:00 am');
    // $endTime = Carbon::parse('11:00 pm');

    // return response()->json($validationService->validateBooking($areas, $date, $startTime, $endTime, 1));

    
});
