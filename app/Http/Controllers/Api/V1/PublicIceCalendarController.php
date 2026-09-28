<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\IceCalendarRangeRequest;
use App\Http\Resources\PublicIceBookingResource;
use Domain\Booking\Models\Booking;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class PublicIceCalendarController extends Controller
{
    public function __invoke(IceCalendarRangeRequest $request): AnonymousResourceCollection
    {
        $bookings = Booking::query()
            ->onIceSheets()
            ->with('iceSheets')
            ->where('date', '>=', $request->startDate())
            ->where('date', '<', $request->endDate())
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return PublicIceBookingResource::collection($bookings);
    }
}
