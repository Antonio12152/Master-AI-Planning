<?php

namespace Database\Seeders;

use App\Models\Idea;
use App\Models\IdeaGroup;
use Illuminate\Database\Seeder;

class IdeaSeeder extends Seeder
{
    public function run(): void
    {
        IdeaGroup::all()->each(function (IdeaGroup $group) {
            // Каждой группе создать 3-10 идей
            $ideaCount = rand(3, 10);
            
            Idea::factory($ideaCount)
                ->create([
                    'plan_id' => $group->plan_id,
                    'group_id' => $group->id,
                ]);
        });

        $this->command->info('✅ Ideas seeded: ' . Idea::count());
    }
}
