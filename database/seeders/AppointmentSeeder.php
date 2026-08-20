<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Chamber;
use App\Services\SlotService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Books a scattering of real slots so the admin dashboard and the public
 * slot picker both have something meaningful to show on a fresh install.
 */
class AppointmentSeeder extends Seeder
{
    public function run(SlotService $slots): void
    {
        Appointment::query()->delete();

        $names = [
            ['Rokeya Sultana', '01711111101', 'female', 58],
            ['Abdul Karim', '01711111102', 'male', 64],
            ['Sabina Yasmin', '01711111103', 'female', 42],
            ['Mizanur Rahman', '01711111104', 'male', 51],
            ['Papia Akter', '01711111105', 'female', 36],
            ['Habibur Rahman', '01711111106', 'male', 70],
            ['Sharmin Nahar', '01711111107', 'female', 45],
            ['Kamal Uddin', '01711111108', 'male', 60],
            ['Ruma Begum', '01711111109', 'female', 33],
            ['Jasim Uddin', '01711111110', 'male', 55],
        ];

        $chambers = Chamber::with('schedules')->where('accepts_online_booking', true)->get();
        $statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        $index = 0;

        foreach ([-14, -7, -2, 0, 1, 2, 3, 5, 8, 11] as $offset) {
            $date = Carbon::today()->addDays($offset);

            foreach ($chambers as $chamber) {
                $availability = $slots->availability($chamber, $date);

                // Past dates return "closed"; fall back to the weekly pattern for history.
                $times = $availability->hasOpenSlots()
                    ? array_column(array_slice($availability->openSlots(), 0, 3), 'time')
                    : $this->historicTimes($chamber, $date);

                foreach ($times as $time) {
                    [$name, $phone, $gender, $age] = $names[$index % count($names)];
                    $index++;

                    $status = $offset < 0
                        ? ($index % 5 === 0 ? 'cancelled' : 'completed')
                        : $statuses[$index % 2];

                    Appointment::create([
                        'appointment_no' => Appointment::generateNumber($date),
                        'chamber_id' => $chamber->id,
                        'chamber_schedule_id' => null,
                        'patient_name' => $name,
                        'patient_phone' => $phone,
                        'patient_gender' => $gender,
                        'patient_age' => $age,
                        'visit_type' => $index % 3 === 0 ? 'followup' : 'new',
                        'appointment_date' => $date->toDateString(),
                        'slot_time' => $time,
                        'status' => $status,
                        'confirmed_at' => in_array($status, ['confirmed', 'completed'], true) ? $date->copy()->subDay() : null,
                        'completed_at' => $status === 'completed' ? $date : null,
                        'cancelled_at' => $status === 'cancelled' ? $date->copy()->subDay() : null,
                    ]);
                }
            }
        }
    }

    /** @return array<int, string> */
    private function historicTimes(Chamber $chamber, Carbon $date): array
    {
        $schedule = $chamber->schedules
            ->where('is_active', true)
            ->firstWhere('day_of_week', $date->dayOfWeek);

        if (! $schedule) {
            return [];
        }

        $start = Carbon::parse($schedule->start_time);

        return [
            $start->copy()->format('H:i:s'),
            $start->copy()->addMinutes($schedule->slot_minutes)->format('H:i:s'),
        ];
    }
}
