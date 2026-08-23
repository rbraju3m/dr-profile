<?php

namespace App\Support;

use App\Models\Appointment;
use Illuminate\Http\Request;

/**
 * Who is allowed to read an appointment.
 *
 * The serial travels on a printed slip, by SMS and by email, so on its own it
 * is not proof of identity — cancelling has always demanded the mobile number
 * as well. Reading the record now demands the same, because the page carries
 * everything the patient typed, clinical notes included.
 *
 * Access is granted for the life of a session, either by making the booking or
 * by passing the lookup form, and is remembered per appointment so that a
 * shared device does not open every record somebody has ever looked up.
 */
final class PatientAccess
{
    private const KEY = 'appointment_access';

    /** Kept per session, oldest dropped first — a family books more than one. */
    private const LIMIT = 20;

    public static function grant(Request $request, Appointment $appointment): void
    {
        $granted = self::all($request);
        $granted[] = $appointment->getKey();

        $request->session()->put(self::KEY, array_slice(array_values(array_unique($granted)), -self::LIMIT));
    }

    public static function granted(Request $request, Appointment $appointment): bool
    {
        return in_array($appointment->getKey(), self::all($request), true);
    }

    /** @return list<int> */
    private static function all(Request $request): array
    {
        return array_values(array_filter(
            (array) $request->session()->get(self::KEY, []),
            'is_int',
        ));
    }
}
