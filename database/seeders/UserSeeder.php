<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $NUMBER_OF_USERS = 5;

        // Create the test user first
        \App\Models\User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create 50 more users (for a total of 51)
        \App\Models\User::factory($NUMBER_OF_USERS)->create();

        $this->command->info("✅ Created $NUMBER_OF_USERS users (including test user)");
    }
}
