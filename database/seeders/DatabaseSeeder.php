<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            DoctorProfileSeeder::class,
            ServiceSeeder::class,
            ChamberSeeder::class,
            ContentSeeder::class,
            SiteSeeder::class,
            AppointmentSeeder::class,
        ]);
    }
}
