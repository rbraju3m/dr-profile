<?php

namespace App\Http\Controllers;

use App\Exceptions\SlotUnavailableException;
use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\Service;
use App\Services\BookingService;
use App\Services\SlotService;
use App\Support\PatientAccess;
use App\Support\Phone;
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

        $chamber = Chamber::with('activeSchedules')->findOrFail($validated['chamber_id']);

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

        PatientAccess::grant($request, $appointment);

        return redirect()
            ->route('appointment.show', $appointment)
            ->with('booked', true);
    }

    /**
     * The confirmation page.
     *
     * The record is bound by hand rather than by the router so that a serial
     * that does not exist and one that does but belongs to somebody else end at
     * the same place. A 404 for the first and a redirect for the second would
     * make this page a way of discovering which serials are real, which is the
     * work the mobile number is here to prevent.
     */
    public function show(Request $request, string $appointment): View|RedirectResponse
    {
        $record = Appointment::where('appointment_no', $appointment)->first();

        if (! $record || ! PatientAccess::granted($request, $record)) {
            return redirect()->route('appointment.lookup', ['serial' => $appointment]);
        }

        return view('public.appointment.show', [
            'appointment' => $record->load('chamber', 'service'),
            'justBooked' => (bool) session('booked'),
        ]);
    }

    /**
     * Cancel from the confirmation page.
     *
     * The serial alone is a weak authenticator — it is short and printed on a
     * slip anyone might see — so the phone number given at booking has to match
     * as well. Wrong number, no cancellation, and the route is throttled.
     */
    public function cancel(Request $request, Appointment $appointment, BookingService $booking): RedirectResponse
    {
        if (! $appointment->isCancellable()) {
            return back()->withErrors(['phone' => __('site.booking.cannot_cancel')]);
        }

        $validated = $request->validate([
            'phone' => ['required', 'string'],
            'reason' => ['nullable', 'string', 'max:200'],
        ]);

        // Compare the last nine digits so +8801… and 01… both match.
        if (! Phone::matches($validated['phone'], $appointment->patient_phone)) {
            return back()->withErrors(['phone' => __('site.booking.phone_mismatch')]);
        }

        $booking->cancel($appointment, $validated['reason'] ?? __('site.booking.cancelled_by_patient'));

        return redirect()
            ->route('appointment.show', $appointment)
            ->with('cancelled', true);
    }

    /**
     * The form a patient re-opens their confirmation with.
     *
     * It reads nothing: the serial only arrives here to be typed back into the
     * field, either because the patient followed a link or because show() sent
     * them here to prove the appointment is theirs.
     */
    public function lookup(Request $request): View
    {
        return view('public.appointment.lookup', [
            'serial' => $request->string('serial')->trim()->upper()->toString(),
        ]);
    }

    /**
     * Serial *and* the number it was booked with, then the record opens.
     *
     * One message covers a serial that does not exist and a serial that does
     * but was booked on another number, for the same reason show() redirects
     * both: a form that tells them apart is a way of harvesting live serials.
     */
    public function find(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'serial' => ['required', 'string', 'max:40'],
            'phone' => ['required', 'string', 'max:40'],
        ]);

        $serial = strtoupper(trim($data['serial']));
        $appointment = Appointment::where('appointment_no', $serial)->first();

        if (! $appointment || ! Phone::matches($data['phone'], $appointment->patient_phone)) {
            return back()
                ->withInput(['serial' => $serial])
                ->withErrors(['serial' => __('site.booking.not_found')]);
        }

        PatientAccess::grant($request, $appointment);

        return redirect()->route('appointment.show', $appointment);
    }
}
