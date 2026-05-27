<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();

        if ($admin) {
            // Создать активные планы для админа
            Plan::factory(3)
                ->active()
                ->create(['user_id' => $admin->id]);

            // Создать публичный план
            Plan::factory()
                ->public()
                ->active()
                ->create(['user_id' => $admin->id]);

            // Создать архивированный план
            Plan::factory()
                ->archived()
                ->create(['user_id' => $admin->id]);
        }

        // Создать планы для остальных пользователей
        User::where('email', '!=', 'admin@example.com')
            ->limit(5)
            ->get()
            ->each(function (User $user) {
                Plan::factory(2)
                    ->active()
                    ->create(['user_id' => $user->id]);
            });

        $this->command->info('✅ Plans seeded: ' . Plan::count());
    }
}
