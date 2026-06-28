<?php

namespace Database\Seeders;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Database\Seeder;

class ApiTokenSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();

        if ($admin) {
            //    
            ApiToken::factory()
                ->create([
                    'user_id' => $admin->id,
                    'name' => 'Admin Token',
                    'scopes' => ['plans:read', 'plans:write', 'ideas:read', 'ideas:write'],
                ]);
        }

        //      (50% )
        User::where('email', '!=', 'admin@example.com')
            ->get()
            ->each(function (User $user) {
                if (rand(1, 100) <= 50) {
                    ApiToken::factory()
                        ->create(['user_id' => $user->id]);
                }
            });

        $this->command->info('✅ API Tokens seeded: ' . ApiToken::count());
    }
}
