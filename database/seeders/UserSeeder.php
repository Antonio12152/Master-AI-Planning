<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Создать админ пользователя
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make(env('PASSWORD', 'password123')),
            'timezone' => 'Europe/Berlin',
            'is_active' => true,
            'is_verified' => true,
        ]);

        // Создать тестовых пользователей
        User::factory(5)->create();

        // Создать неверифицированного пользователя
        User::factory()->unverified()->create([
            'email' => 'unverified@example.com',
        ]);

        // Создать неактивного пользователя
        User::factory()->inactive()->create([
            'email' => 'inactive@example.com',
        ]);

        $this->command->info('✅ Users seeded: ' . User::count());
    }
}
