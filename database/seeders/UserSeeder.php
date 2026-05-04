<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User contoh untuk login/testing
        $pm = User::firstOrCreate(
            ['email' => 'pm@test.com'],
            [
                'name' => 'PM Demo',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $dev = User::firstOrCreate(
            ['email' => 'dev@test.com'],
            [
                'name' => 'Developer Demo',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Tambahan dummy programmer (3 user)
        $dev2 = User::firstOrCreate(
            ['email' => 'dev2@test.com'],
            [
                'name' => 'Developer 2 Demo',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $dev3 = User::firstOrCreate(
            ['email' => 'dev3@test.com'],
            [
                'name' => 'Developer 3 Demo',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $dev4 = User::firstOrCreate(
            ['email' => 'dev4@test.com'],
            [
                'name' => 'Developer 4 Demo',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $qa = User::firstOrCreate(
            ['email' => 'qa@test.com'],
            [
                'name' => 'QA Demo',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Single-role enforcement: gunakan syncRoles
        $pm->syncRoles(['Project Manager']);
        $dev->syncRoles(['Programmer']);
        $dev2->syncRoles(['Programmer']);
        $dev3->syncRoles(['Programmer']);
        $dev4->syncRoles(['Programmer']);
        $qa->syncRoles(['QA']);
    }
}
