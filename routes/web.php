<?php

use App\Enums\Permission;
use App\Filament\Pages\Auth\SetPassword;
use App\Services\BookingValidationService;
use Domain\Booking\Models\Booking;
use Domain\Facility\Models\Area;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {

//     // return Booking::all();
//     // return Auth::user()->can(Permission::VIEW_SPARES);
//     return true;

//     // $validationService = app(BookingValidationService::class);

//     // $areas = Area::findMany([1]);
//     // $date = Carbon::parse('2024-12-19');
//     // $startTime = Carbon::parse('10:00 am');
//     // $endTime = Carbon::parse('11:00 pm');

//     // return response()->json($validationService->validateBooking($areas, $date, $startTime, $endTime, 1));

// });

// Password setup route (magic link)
Route::get('/admin/set-password', SetPassword::class)->name('filament.admin.pages.set-password');
