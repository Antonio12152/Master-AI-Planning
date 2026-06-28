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
            // Create 3-10 ideas per group
            $ideaCount = rand(3, 10);
            
            // Create ideas with sequential sort_order within the group
            for ($i = 0; $i < $ideaCount; $i++) {
                Idea::factory()
                    ->create([
                        'plan_id' => $group->plan_id,
                        'group_id' => $group->id,
                        'sort_order' => $i,
                    ]);
            }
        });

        $this->command->info('✅ Ideas seeded: ' . Idea::count());
    }
}
