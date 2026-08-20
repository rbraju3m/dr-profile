<?php

namespace App\Http\Controllers;

use App\Models\Chamber;
use App\Services\SlotService;
use Illuminate\Contracts\View\View;

class ChamberController extends Controller
{
    public function index(SlotService $slots): View
    {
        $chambers = Chamber::with('activeSchedules')->active()->ordered()->get();

        return view('public.chambers.index', [
            'chambers' => $chambers,
            'nextDates' => $chambers->mapWithKeys(
                fn (Chamber $chamber) => [$chamber->id => $slots->nextAvailableDate($chamber)]
            ),
        ]);
    }

    public function show(Chamber $chamber, SlotService $slots): View
    {
        abort_unless($chamber->is_active, 404);

        $chamber->load('activeSchedules');

        return view('public.chambers.show', [
            'chamber' => $chamber,
            'nextDate' => $slots->nextAvailableDate($chamber),
            'calendar' => collect($slots->calendar($chamber, 14)),
            'others' => Chamber::active()->ordered()->whereKeyNot($chamber->id)->get(),
        ]);
    }
}
