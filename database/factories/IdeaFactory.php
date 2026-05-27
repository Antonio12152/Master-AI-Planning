<?php

namespace Database\Factories;

use App\Models\IdeaGroup;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class IdeaFactory extends Factory
{
    public function definition(): array
    {
        $group = IdeaGroup::inRandomOrder()->first() ?? IdeaGroup::factory()->create();
        $plan = $group->plan;

        $statuses = ['new', 'in_progress', 'completed', 'rejected'];
        $tagsList = ['feature', 'ui', 'performance', 'bug', 'improvement', 'design', 'backend', 'frontend'];

        return [
            'group_id' => $group->id,
            'plan_id' => $plan->id,
            'text' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement($statuses),
            'priority' => fake()->randomElement([0, 1, 2, 3]),
            'tags' => fake()->randomElements($tagsList, fake()->numberBetween(0, 3)),
            'sort_order' => fake()->numberBetween(0, 100),
            'completed_at' => fake()->boolean(30) ? fake()->dateTime() : null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => fake()->dateTime(),
        ]);
    }

    public function asNew(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'new',
            'completed_at' => null,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'completed_at' => null,
        ]);
    }
}
