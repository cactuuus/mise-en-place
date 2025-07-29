<?php

namespace Database\Seeders;

use App\Models\Recipe;
use App\Models\RecipeRating;
use App\Models\User;
use App\Models\UserFollow;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all existing users (created by UserSeeder)
        $users = User::all();

        // Create 1 to 10 recipes per user
        $recipes = collect();
        foreach ($users as $user) {
            $userRecipes = Recipe::factory(rand(1, 10))->create([
                'user_id' => $user->id,
            ]);
            $recipes     = $recipes->merge($userRecipes);
        }

        // Create some forked recipes
        $originalRecipes = $recipes->where('forked_from_recipe_id', null)->take(30);
        foreach ($originalRecipes as $originalRecipe) {
            // Random user forks this recipe
            $forker = $users->where('id', '!=', $originalRecipe->user_id)->random();

            Recipe::factory()->create([
                'user_id'               => $forker->id,
                'title'                 => $originalRecipe->title.' (My Version)',
                'ingredients'           => $originalRecipe->ingredients,
                'instructions'          => $originalRecipe->instructions,
                'forked_from_recipe_id' => $originalRecipe->id,
                'notes'                 => 'This is my take on '.$originalRecipe->user->name.'\'s recipe. I made a few adjustments to suit my taste.',
            ]);
        }

        // Create ratings for recipes (each recipe gets 1-5 ratings)
        foreach ($recipes as $recipe) {
            $ratingCount = rand(1, 5);
            $raters      = $users->where('id', '!=', $recipe->user_id)->random($ratingCount);

            foreach ($raters as $rater) {
                RecipeRating::factory()->create([
                    'recipe_id' => $recipe->id,
                    'user_id'   => $rater->id,
                ]);
            }
        }

        // Create follow relationships (each user follows 2-4 other users)
        foreach ($users as $user) {
            $followCount = rand(2, 4);
            $toFollow    = $users->where('id', '!=', $user->id)->random($followCount);

            foreach ($toFollow as $followed) {
                UserFollow::factory()->create([
                    'follower_id'  => $user->id,
                    'following_id' => $followed->id,
                ]);
            }
        }

        $this->command->info('✅ Created:');
        $this->command->info('- '.$users->count().' users');
        $this->command->info('- '.Recipe::count().' recipes (includes forks)');
        $this->command->info('- '.RecipeRating::count().' ratings');
        $this->command->info('- '.UserFollow::count().' follow relationships');
    }
}
