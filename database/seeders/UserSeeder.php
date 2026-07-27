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
            ['email' => 'admin@example.com'],
            [
                'name'     => 'Administrator',
                'password' => Hash::make('password'),
                'role'     => 'admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'operator@example.com'],
            [
                'name'     => 'Operator Utama',
                'password' => Hash::make('Operator2026!'),
                'role'     => 'operator',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'viewer@example.com'],
            [
                'name'     => 'Viewer Dashboard',
                'password' => Hash::make('Viewer2026!'),
                'role'     => 'viewer',
                'is_active' => true,
            ]
        );
    }
}
