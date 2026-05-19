<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ApiTokenFactory extends Factory
{
    public function definition(): array
    {
        $token = Str::random(40);

        return [
            'user_id' => User::factory(),
            'token' => $token,
            'token_hash' => hash('sha256', $token),
            'name' => fake()->words(2, true),
            'scopes' => fake()->randomElements(
                ['plans:read', 'plans:write', 'ideas:read', 'ideas:write'],
                fake()->numberBetween(2, 3)
            ),
            'expires_at' => fake()->boolean(60) ? fake()->dateTimeBetween('+1 days', '+1 year') : null,
            'last_used_at' => fake()->boolean(70) ? fake()->dateTime() : null,
        ];
    }
}
