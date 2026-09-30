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
            ['name' => 'admin', 'email' => 'admin@admin.com', 'password' => 'admin'],
            ['name' => 'Anna Bójko', 'email' => 'anna@example.com', 'password' => 'password'],
            ['name' => 'Jan Kowalski', 'email' => 'jan@example.com', 'password' => 'password'],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'email_verified_at' => now(),
                    'password' => Hash::make($user['password']),
                ],
            );
        }
    }
}
