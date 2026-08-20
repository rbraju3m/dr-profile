<?php

namespace App\Http\Controllers;

use App\Exceptions\SlotUnavailableException;
use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\Service;
use App\Services\BookingService;
use App\Services\SlotService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AppointmentController extends Controller
{
    public function create(Request $request, SlotService $slots): View
    {
        $chambers = Chamber::with('activeSchedules')
            ->active()
            ->where('accepts_online_booking', true)
            ->ordered()
            ->get();

        $selected = $chambers->firstWhere('slug', $request->string('chamber')->toString()) ?? $chambers->first();

        return view('public.appointment.create', [
            'chambers' => $chambers,
            'closedChambers' => Chamber::active()->where('accepts_online_booking', false)->ordered()->get(),
            'selected' => $selected,
            'services' => Service::active()->ordered()->get(),
            'calendars' => $chambers->mapWithKeys(
                fn (Chamber $chamber) => [$chamber->id => $slots->calendar($chamber)]
            ),
            'maxDate' => $slots->lastBookableDate()->toDateString(),
        ]);
    }

    /**
     * Slots for one chamber on one date. Called by the wizard whenever the
     * patient changes chamber or date, so it stays deliberately small.
     */
    public function slots(Request $request, SlotService $slots): JsonResponse
    {
        $validated = $request->validate([
            'chamber_id' => ['required', 'integer', 'exists:chambers,id'],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $chamber = Chamber::with('schedules')->findOrFail($validated['chamber_id']);

        abort_unless($chamber->is_active && $chamber->accepts_online_booking, 404);

        $availability = $slots->availability($chamber, Carbon::parse($validated['date']));

        return response()->json([
            'date' => $validated['date'],
            'open' => $availability->isOpen,
            'reason' => $availability->closedReason,
            'slots' => $availability->slots,
            'open_count' => $availability->openCount(),
        ]);
    }

    public function store(StoreAppointmentRequest $request, BookingService $booking): RedirectResponse
    {
        try {
            $appointment = $booking->book(
                $request->chamber(),
                Carbon::parse($request->validated('appointment_date')),
                $request->validated('slot_time'),
                $request->safe()->except(['chamber_id', 'appointment_date', 'slot_time'])
                    + ['ip_address' => $request->ip()],
            );
        } catch (SlotUnavailableException $e) {
            return back()
                ->withInput()
                ->withErrors(['slot_time' => $e->getMessage()]);
        }

        return redirect()
            ->route('appointment.show', $appointment)
            ->with('booked', true);
    }

    public function show(Appointment $appointment): View
    {
        return view('public.appointment.show', [
            'appointment' => $appointment->load('chamber', 'service'),
            'justBooked' => (bool) session('booked'),
        ]);
    }

    /** Serial-number lookup so a patient can re-open their confirmation. */
    public function lookup(Request $request): View|RedirectResponse
    {
        $serial = $request->string('serial')->trim()->upper()->toString();

        if ($serial !== '') {
            $appointment = Appointment::where('appointment_no', $serial)->first();

            if ($appointment) {
                return redirect()->route('appointment.show', $appointment);
            }

            return view('public.appointment.lookup', [
                'serial' => $serial,
                'error' => __('site.booking.not_found'),
            ]);
        }

        return view('public.appointment.lookup', ['serial' => '', 'error' => null]);
    }
}
