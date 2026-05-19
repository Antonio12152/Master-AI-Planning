<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    public function definition(): array
    {
        $colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8', '#F7DC6F', '#BB8FCE'];
        $icons = ['📋', '🎯', '💡', '🚀', '📝', '🎨', '📊', '🔥'];

        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['active', 'inactive', 'archived']),
            'color' => fake()->randomElement($colors),
            'icon' => fake()->randomElement($icons),
            'idea_count' => fake()->numberBetween(0, 50),
            'group_count' => fake()->numberBetween(1, 10),
            'member_count' => 1, // минимально владелец
            'is_public' => false, // по умолчанию приватный
            'archived_at' => fake()->boolean(20) ? fake()->dateTime() : null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'archived_at' => null,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
            'archived_at' => fake()->dateTime(),
        ]);
    }

    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }
}
