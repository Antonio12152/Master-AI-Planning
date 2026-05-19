<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class IdeaGroupFactory extends Factory
{
    public function definition(): array
    {
        $colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8', '#F7DC6F', '#BB8FCE'];

        return [
            'plan_id' => Plan::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->paragraph(),
            'sort_order' => fake()->numberBetween(0, 100),
            'idea_count' => fake()->numberBetween(0, 20),
            'color' => fake()->randomElement($colors),
        ];
    }
}
