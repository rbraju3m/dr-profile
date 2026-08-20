<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@drprofile.test'],
            [
                'name' => 'Site Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '01700000000',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'editor@drprofile.test'],
            [
                'name' => 'Chamber Editor',
                'password' => Hash::make('password'),
                'role' => 'editor',
                'phone' => '01700000001',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
