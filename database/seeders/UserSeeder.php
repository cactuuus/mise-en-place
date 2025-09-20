<?php

namespace Database\Seeders;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $NUMBER_OF_USERS = 10;

        // Create the test user first
        User::factory()->create([
            'name'  => 'Test User',
            'email' => 'user@example.com',
        ]);

        // Create an admin user
        $admin = User::factory()->create([
            'name'  => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole(Roles::Admin);

        // Create 5 more users (for a total of 6)
        User::factory($NUMBER_OF_USERS)->create();

        $this->command->info("✅ Created $NUMBER_OF_USERS users (including test user)");
    }
}
