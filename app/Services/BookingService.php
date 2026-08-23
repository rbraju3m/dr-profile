<?php

namespace App\Services;

use App\Exceptions\SlotUnavailableException;
use App\Mail\AppointmentConfirmation;
use App\Models\Appointment;
use App\Models\Chamber;
use App\Support\Phone;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Creates appointments safely.
 *
 * Two patients can hit "confirm" on the same slot at the same moment, so the
 * slot is re-validated inside a transaction while holding a row lock on the
 * chamber. That serialises bookings per chamber and makes the availability
 * read consistent with the insert that follows it.
 */
class BookingService
{
    public function __construct(private readonly SlotService $slots) {}

    /**
     * @param  array{patient_name: string, patient_phone: string, ...}  $data
     *
     * @throws SlotUnavailableException
     */
    public function book(Chamber $chamber, Carbon $date, string $time, array $data): Appointment
    {
        if (! $this->slots->isWithinWindow($date)) {
            throw SlotUnavailableException::outsideWindow($this->slots->windowDays());
        }

        $this->guardOpenAppointmentLimit($data['patient_phone']);

        $slotTime = $this->slots->normaliseTime($time);

        if ($slotTime === null) {
            throw SlotUnavailableException::taken();
        }

        $appointment = DB::transaction(function () use ($chamber, $date, $slotTime, $data) {
            // Serialise concurrent bookings for this chamber, then read availability
            // inside the same transaction so the check matches the insert.
            Chamber::query()->whereKey($chamber->id)->lockForUpdate()->first();

            // Reload the relation SlotService actually reads, so a copy loaded
            // before the lock cannot answer for the state inside it.
            $chamber->load('activeSchedules');
            $availability = $this->slots->availability($chamber, $date);

            if (! $availability->offers($slotTime)) {
                throw SlotUnavailableException::taken();
            }

            $scheduleId = $availability->scheduleIdFor($slotTime);

            return Appointment::create([
                'appointment_no' => Appointment::generateNumber($date),
                'chamber_id' => $chamber->id,
                'chamber_schedule_id' => $scheduleId,
                'service_id' => $data['service_id'] ?? null,
                'patient_name' => $data['patient_name'],
                'patient_phone' => $data['patient_phone'],
                'patient_email' => $data['patient_email'] ?? null,
                'patient_gender' => $data['patient_gender'] ?? null,
                'patient_age' => $data['patient_age'] ?? null,
                'patient_address' => $data['patient_address'] ?? null,
                'visit_type' => $data['visit_type'] ?? 'new',
                'appointment_date' => $date->toDateString(),
                'slot_time' => $slotTime,
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
                'ip_address' => $data['ip_address'] ?? null,
            ]);
        });

        $this->sendConfirmation($appointment);

        return $appointment;
    }

    /**
     * Best-effort confirmation email. A mail failure must never lose a booking
     * the patient has already been told is confirmed, so it is logged, not thrown.
     */
    private function sendConfirmation(Appointment $appointment): void
    {
        if (blank($appointment->patient_email)) {
            return;
        }

        try {
            Mail::to($appointment->patient_email)
                ->locale(app()->getLocale())
                ->send(new AppointmentConfirmation($appointment));
        } catch (\Throwable $e) {
            Log::warning('Appointment confirmation email failed', [
                'appointment' => $appointment->appointment_no,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function cancel(Appointment $appointment, ?string $reason = null): Appointment
    {
        $appointment->update([
            'status' => 'cancelled',
            'cancelled_reason' => $reason,
            'cancelled_at' => Carbon::now(),
        ]);

        return $appointment;
    }

    public function changeStatus(Appointment $appointment, string $status, ?string $reason = null): Appointment
    {
        $timestamps = [
            'confirmed' => ['confirmed_at' => Carbon::now()],
            'completed' => ['completed_at' => Carbon::now()],
            'cancelled' => ['cancelled_at' => Carbon::now(), 'cancelled_reason' => $reason],
            'pending' => ['confirmed_at' => null, 'completed_at' => null, 'cancelled_at' => null, 'cancelled_reason' => null],
        ];

        $appointment->update(['status' => $status] + ($timestamps[$status] ?? []));

        return $appointment;
    }

    /**
     * Stops one person from hoarding slots.
     *
     * Counting the raw string counted 01712345678 and +8801712345678 as two
     * different people, so the allowance could be taken as many times as the
     * patient had ways of writing their own number. Cancelling has always
     * compared the last nine digits; this now agrees with it.
     */
    private function guardOpenAppointmentLimit(string $phone): void
    {
        $key = Phone::key($phone);

        if ($key === '') {
            return;
        }

        $max = (int) config('site.booking.max_open_per_phone', 3);

        $open = Appointment::query()
            // $key is nine digits, so it carries no LIKE wildcards of its own.
            ->where('patient_phone', 'like', '%'.$key)
            ->whereIn('status', ['pending', 'confirmed'])
            ->upcoming()
            ->count();

        if ($open >= $max) {
            throw SlotUnavailableException::tooManyOpen($max);
        }
    }
}
