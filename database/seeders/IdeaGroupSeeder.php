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
            //    2-5 
            $groupCount = rand(2, 5);
            
            IdeaGroup::factory($groupCount)
                ->create(['plan_id' => $plan->id]);
        });

        $this->command->info('✅ Idea Groups seeded: ' . IdeaGroup::count());
    }
}
