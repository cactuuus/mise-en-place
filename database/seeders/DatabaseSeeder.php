<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create roles first
        $this->call(RoleSeeder::class);

        // Create all users first (including test user)
        $this->call(UserSeeder::class);

        // Create tags before recipes so we can attach them
        $this->call(TagSeeder::class);

        // Then create recipes, ratings, and follows for those users
        $this->call(RecipeSeeder::class);
    }
}
