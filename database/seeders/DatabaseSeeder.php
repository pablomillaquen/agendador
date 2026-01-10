<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('test1234'), // Re-hashing just in case, or use existing if not changing
                'role' => 'admin',
                'color' => '#EF4444', // Red
            ]
        );

        // Coordinator
        User::updateOrCreate(
            ['email' => 'coordinator@admin.com'],
            [
                'name' => 'Coordinator User',
                'password' => bcrypt('test1234'),
                'role' => 'coordinator',
                'color' => '#F59E0B', // Amber
            ]
        );

        // Professionals
        $professionals = [
            ['name' => 'Dr. Smith', 'email' => 'smith@admin.com', 'color' => '#3B82F6'], // Blue
            ['name' => 'Dra. Jones', 'email' => 'jones@admin.com', 'color' => '#10B981'], // Green
            ['name' => 'Dr. House', 'email' => 'house@admin.com', 'color' => '#8B5CF6'], // Purple
        ];

        foreach ($professionals as $pro) {
            User::updateOrCreate(
                ['email' => $pro['email']],
                [
                    'name' => $pro['name'],
                    'password' => bcrypt('test1234'),
                    'role' => 'professional',
                    'color' => $pro['color'],
                ]
            );
        }
    }
}
