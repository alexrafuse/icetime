<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Domain\Facility\Models\Area;
use Illuminate\Contracts\View\View;

final class IceCalendarEmbedController extends Controller
{
    public function __invoke(): View
    {
        return view('embed.ice-calendar', [
            'sheetCount' => Area::iceSheets()->active()->count(),
        ]);
    }
}
