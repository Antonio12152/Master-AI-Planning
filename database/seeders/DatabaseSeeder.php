<?php
 
namespace Database\Seeders;
 
use Illuminate\Database\Seeder;
 
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            PlanSeeder::class,
            IdeaGroupSeeder::class,
            IdeaSeeder::class,
            PlanMemberSeeder::class,
            ApiTokenSeeder::class,
            ActivityLogSeeder::class,
        ]);
    }
}
 