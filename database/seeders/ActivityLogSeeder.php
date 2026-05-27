<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Idea;
use App\Models\IdeaGroup;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $actions = ['created', 'updated', 'deleted', 'moved', 'completed'];
        $entityTypes = ['plan', 'group', 'idea'];

        // Создать логи для каждого плана
        Plan::all()->each(function (Plan $plan) use ($actions, $entityTypes) {
            $logCount = rand(5, 15);

            for ($i = 0; $i < $logCount; $i++) {
                $entityType = fake()->randomElement($entityTypes);
                $entityId = null;

                if ($entityType === 'plan') {
                    $entityId = $plan->id;
                } elseif ($entityType === 'group') {
                    $entityId = $plan->ideaGroups()->first()?->id;
                } elseif ($entityType === 'idea') {
                    $entityId = $plan->ideas()->first()?->id;
                }

                ActivityLog::create([
                    'user_id' => $plan->user_id,
                    'plan_id' => $plan->id,
                    'action' => fake()->randomElement($actions),
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                    'details' => [
                        'description' => fake()->sentence(),
                        'timestamp' => now(),
                    ],
                    'changes' => [],
                    'created_at' => fake()->dateTimeBetween('-30 days'),
                ]);
            }
        });

        $this->command->info('✅ Activity Logs seeded: ' . ActivityLog::count());
    }
}
