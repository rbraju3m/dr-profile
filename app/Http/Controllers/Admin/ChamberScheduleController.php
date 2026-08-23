<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chamber;
use App\Models\ChamberSchedule;
use App\Support\Week;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ChamberScheduleController extends Controller
{
    public function index(Chamber $chamber): View
    {
        return view('admin.schedules.index', [
            'chamber' => $chamber,
            'schedules' => $chamber->schedules()->get()->groupBy('day_of_week'),
            'days' => collect(Week::DAYS)->mapWithKeys(fn (int $d) => [$d => Week::name($d)]),
        ]);
    }

    public function store(Request $request, Chamber $chamber): RedirectResponse
    {
        $data = $request->validate([
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'slot_minutes' => ['required', 'integer', 'min:5', 'max:180'],
            'max_patients' => ['nullable', 'integer', 'min:1', 'max:500'],
            'location_en' => ['nullable', 'string', 'max:120'],
            'location_bn' => ['nullable', 'string', 'max:120'],
        ], [], [
            'day_of_week' => __('admin.schedules.day'),
            'start_time' => __('admin.schedules.start'),
            'end_time' => __('admin.schedules.end'),
            'slot_minutes' => __('admin.schedules.slot_minutes'),
            'max_patients' => __('admin.schedules.max_patients'),
        ]);

        $this->guardAgainstOverlap($chamber, $data);

        $chamber->schedules()->create($data + ['is_active' => true]);

        return back()->with('success', __('admin.flash.created', ['item' => __('admin.nav.schedules')]));
    }

    public function destroy(ChamberSchedule $schedule): RedirectResponse
    {
        $chamber = $schedule->chamber;
        $schedule->delete();

        return redirect()
            ->route('admin.chambers.schedules.index', $chamber)
            ->with('success', __('admin.flash.deleted', ['item' => __('admin.nav.schedules')]));
    }

    /** Two sittings on the same day must not overlap, or slots would be generated twice. */
    private function guardAgainstOverlap(Chamber $chamber, array $data): void
    {
        $clash = $chamber->schedules()
            ->where('day_of_week', $data['day_of_week'])
            ->where('start_time', '<', $data['end_time'])
            ->where('end_time', '>', $data['start_time'])
            ->exists();

        if ($clash) {
            throw ValidationException::withMessages([
                'start_time' => __('admin.schedules.overlap'),
            ]);
        }
    }
}
