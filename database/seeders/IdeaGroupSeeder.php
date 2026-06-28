<?php

namespace Database\Seeders;

use App\Models\IdeaGroup;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class IdeaGroupSeeder extends Seeder
{
    public function run(): void
    {
        Plan::all()->each(function (Plan $plan) {
            // Create 2-5 groups per plan
            $groupCount = rand(2, 5);
            
            // Create groups with sequential sort_order within the plan
            for ($i = 0; $i < $groupCount; $i++) {
                IdeaGroup::factory()
                    ->create([
                        'plan_id' => $plan->id,
                        'sort_order' => $i,
                    ]);
            }
        });

        $this->command->info('✅ Idea Groups seeded: ' . IdeaGroup::count());
    }
}
