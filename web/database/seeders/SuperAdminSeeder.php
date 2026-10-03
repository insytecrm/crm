<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@platform.com'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
                'is_super_admin' => true,
                'email_verified_at' => now(),
            ],
        );
    }
}
