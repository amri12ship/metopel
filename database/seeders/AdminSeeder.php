<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    private const ADMIN_EMAIL = 'admin@gmail.com';

    private const DEMO_PASSWORD = '12345678';

    /**
     * Create the demo admin account if it does not already exist.
     * Idempotent: never creates duplicates, never modifies other users.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => self::ADMIN_EMAIL],
            [
                'name' => 'Admin Sistem',
                'password' => Hash::make(self::DEMO_PASSWORD),
                'role' => User::ROLE_ADMIN,
            ],
        );
    }
}
