<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        //   
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make(env('PASSWORD', 'password123')),
            'timezone' => 'Europe/Berlin',
            'is_active' => true,
            'is_verified' => true,
        ]);

        //   
        User::factory(5)->create();

        //   
        User::factory()->unverified()->create([
            'email' => 'unverified@example.com',
        ]);

        //   
        User::factory()->inactive()->create([
            'email' => 'inactive@example.com',
        ]);

        $this->command->info('✅ Users seeded: ' . User::count());
    }
}
