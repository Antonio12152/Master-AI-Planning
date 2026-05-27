<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlanMember;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlanMemberSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        Plan::all()->each(function (Plan $plan) use ($users) {
            // Владелец плана автоматически админ
            PlanMember::firstOrCreate(
                ['plan_id' => $plan->id, 'user_id' => $plan->user_id],
                ['role' => 'admin']
            );

            // Добавить 2-4 случайных пользователей к плану
            $memberCount = rand(2, 4);
            $randomUsers = $users->random(min($memberCount, $users->count()));

            foreach ($randomUsers as $user) {
                if ($user->id !== $plan->user_id) {
                    PlanMember::firstOrCreate(
                        ['plan_id' => $plan->id, 'user_id' => $user->id],
                        ['role' => fake()->randomElement(['editor', 'viewer'])]
                    );
                }
            }
        });

        $this->command->info('✅ Plan Members seeded: ' . PlanMember::count());
    }
}
