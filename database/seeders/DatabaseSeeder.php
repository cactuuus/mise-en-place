<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create all users first (including test user)
        $this->call(UserSeeder::class);
        
        // Then create recipes, ratings, and follows for those users
        $this->call(RecipeSeeder::class);
    }
}
