<?php

namespace Database\Seeders;

use App\Models\Chamber;
use App\Models\ChamberSchedule;
use App\Models\ScheduleException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ChamberSeeder extends Seeder
{
    public function run(): void
    {
        $chambers = [
            [
                'slug' => 'evercare-hospital-dhaka',
                'name_en' => 'Evercare Hospital Dhaka',
                'name_bn' => 'এভারকেয়ার হাসপাতাল ঢাকা',
                'address_en' => 'Plot 81, Block E, Bashundhara R/A, Dhaka 1229',
                'address_bn' => 'প্লট ৮১, ব্লক ই, বসুন্ধরা আবাসিক এলাকা, ঢাকা ১২২৯',
                'city_en' => 'Dhaka', 'city_bn' => 'ঢাকা',
                'room_no' => 'Level 3, Room 312',
                'phone' => '+880 2 8431661',
                'appointment_phone' => '10678',
                'consultation_fee' => 2500, 'followup_fee' => 1500,
                'note_en' => 'Please arrive 15 minutes early with all previous reports and your current medicine strip.',
                'note_bn' => 'অনুগ্রহ করে পূর্ববর্তী সব রিপোর্ট ও বর্তমান ওষুধের পাতা নিয়ে ১৫ মিনিট আগে উপস্থিত হন।',
                'accepts_online_booking' => true,
                'schedules' => [
                    [0, '17:00', '20:00', 20], // Sunday
                    [1, '17:00', '20:00', 20],
                    [2, '17:00', '20:00', 20],
                    [3, '17:00', '20:00', 20],
                ],
            ],
            [
                'slug' => 'heart-care-chamber-dhanmondi',
                'name_en' => 'Heart Care Chamber, Dhanmondi',
                'name_bn' => 'হার্ট কেয়ার চেম্বার, ধানমন্ডি',
                'address_en' => 'House 42, Road 9/A, Dhanmondi, Dhaka 1209',
                'address_bn' => 'বাড়ি ৪২, রোড ৯/এ, ধানমন্ডি, ঢাকা ১২০৯',
                'city_en' => 'Dhaka', 'city_bn' => 'ঢাকা',
                'room_no' => '2nd Floor',
                'phone' => '+880 1711 000002',
                'appointment_phone' => '+880 1711 000002',
                'consultation_fee' => 1800, 'followup_fee' => 1000,
                'note_en' => 'Evening chamber. Serial closes 30 minutes before the sitting ends.',
                'note_bn' => 'সান্ধ্যকালীন চেম্বার। সময় শেষ হওয়ার ৩০ মিনিট আগে সিরিয়াল বন্ধ হয়ে যায়।',
                'accepts_online_booking' => true,
                'schedules' => [
                    [5, '10:00', '13:00', 15], // Friday morning
                    [6, '10:00', '13:00', 15], // Saturday morning
                    [6, '16:00', '19:00', 15],
                ],
            ],
            [
                'slug' => 'nicvd-outpatient-department',
                'name_en' => 'NICVD Outpatient Department',
                'name_bn' => 'জাতীয় হৃদরোগ ইনস্টিটিউট বহির্বিভাগ',
                'address_en' => 'Sher-e-Bangla Nagar, Dhaka 1207',
                'address_bn' => 'শেরেবাংলা নগর, ঢাকা ১২০৭',
                'city_en' => 'Dhaka', 'city_bn' => 'ঢাকা',
                'room_no' => 'OPD Block, Room 7',
                'phone' => '+880 2 9122560',
                'appointment_phone' => null,
                'consultation_fee' => 0, 'followup_fee' => 0,
                'note_en' => 'Government outpatient clinic — serials are issued at the counter on the day, not online.',
                'note_bn' => 'সরকারি বহির্বিভাগ — সিরিয়াল সেদিনই কাউন্টার থেকে দেওয়া হয়, অনলাইনে নয়।',
                'accepts_online_booking' => false,
                'schedules' => [
                    [4, '09:00', '12:00', 10], // Thursday
                ],
            ],
        ];

        foreach ($chambers as $i => $data) {
            $schedules = $data['schedules'];
            unset($data['schedules']);

            $chamber = Chamber::updateOrCreate(
                ['slug' => $data['slug']],
                $data + ['sort_order' => $i, 'is_active' => true]
            );

            $chamber->schedules()->delete();

            foreach ($schedules as [$day, $start, $end, $minutes]) {
                ChamberSchedule::create([
                    'chamber_id' => $chamber->id,
                    'day_of_week' => $day,
                    'start_time' => $start,
                    'end_time' => $end,
                    'slot_minutes' => $minutes,
                    'is_active' => true,
                ]);
            }
        }

        // A worked example of both exception kinds.
        ScheduleException::updateOrCreate(
            ['chamber_id' => null, 'date' => Carbon::today()->addDays(12)->toDateString()],
            [
                'is_available' => false,
                'reason_en' => 'Attending the national cardiology conference',
                'reason_bn' => 'জাতীয় কার্ডিওলজি সম্মেলনে অংশগ্রহণ',
            ]
        );
    }
}
