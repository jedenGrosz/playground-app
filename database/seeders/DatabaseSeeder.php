<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Admin Testowy', 'email' => 'admin@example.com'],
            ['name' => 'Anna Bójko', 'email' => 'anna@example.com'],
            ['name' => 'Jan Kowalski', 'email' => 'jan@example.com'],
        ];

        foreach ($users as $user) {
            User::query()->firstOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ],
            );
        }
    }
}
