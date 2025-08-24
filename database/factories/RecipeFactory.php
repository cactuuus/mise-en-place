<?php

namespace Database\Factories;

use App\Enums\Difficulty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recipe>
 */
class RecipeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $recipeData = $this->getRandomRecipeData();

        return [
            'user_id'               => \App\Models\User::factory(),
            'title'                 => $recipeData['title'],
            'ingredients'           => $recipeData['ingredients'],
            'instructions'          => $recipeData['instructions'],
            'notes'                 => $this->faker->optional(0.6)->paragraph(),
            'source_url'            => $this->faker->optional(0.3)->url(),
            'is_public'             => $this->faker->boolean(85), // 85% chance of being public
            'forked_from_recipe_id' => null, // We'll handle forks separately
            'prep_time'             => $this->faker->numberBetween(5, 60),
            'cook_time'             => $this->faker->numberBetween(10, 180),
            'serves'                => $this->faker->numberBetween(1, 8),
            'difficulty_level'      => $this->faker->randomElement(Difficulty::cases()),
        ];
    }

    private function getRandomRecipeData(): array
    {
        $recipes = [
            [
                'title'        => 'Classic Chocolate Chip Cookies',
                'ingredients'  => [
                    ['ingredient' => '2¼ cups all-purpose flour'],
                    ['ingredient' => '1 tsp baking soda'],
                    ['ingredient' => '1 tsp salt'],
                    ['ingredient' => '1 cup butter, softened'],
                    ['ingredient' => '¾ cup granulated sugar'],
                    ['ingredient' => '¾ cup brown sugar'],
                    ['ingredient' => '1 tsp vanilla extract'],
                    ['ingredient' => '2 large eggs'],
                    ['ingredient' => '2 cups chocolate chips'],
                ],
                'instructions' => [
                    ['instruction' => 'Preheat oven to 375°F (190°C).'],
                    ['instruction' => 'Mix flour, baking soda, and salt in a bowl.'],
                    ['instruction' => 'Beat butter, sugars, and vanilla in large bowl until creamy.'],
                    ['instruction' => 'Add eggs one at a time, beating well after each addition.'],
                    ['instruction' => 'Gradually blend in flour mixture.'],
                    ['instruction' => 'Stir in chocolate chips.'],
                    ['instruction' => 'Drop rounded tablespoons of dough onto ungreased baking sheets.'],
                    ['instruction' => 'Bake 9-11 minutes or until golden brown.'],
                    ['instruction' => 'Cool on baking sheets for 2 minutes; remove to wire rack.'],
                ],
            ],
            [
                'title'        => 'Homemade Pizza Margherita',
                'ingredients'  => [
                    ['ingredient' => '1 lb pizza dough'],
                    ['ingredient' => '2 tbsp olive oil'],
                    ['ingredient' => '1 cup crushed tomatoes'],
                    ['ingredient' => '2 cloves garlic, minced'],
                    ['ingredient' => '8 oz fresh mozzarella'],
                    ['ingredient' => '1 cup fresh basil leaves'],
                    ['ingredient' => 'salt to taste'],
                    ['ingredient' => 'black pepper to taste'],
                ],
                'instructions' => [
                    ['instruction' => 'Preheat oven to 475°F (245°C).'],
                    ['instruction' => 'Roll out pizza dough on floured surface.'],
                    ['instruction' => 'Transfer to pizza stone or baking sheet.'],
                    ['instruction' => 'Brush with olive oil.'],
                    ['instruction' => 'Mix crushed tomatoes with minced garlic, salt, and pepper.'],
                    ['instruction' => 'Spread tomato mixture evenly over dough.'],
                    ['instruction' => 'Tear mozzarella into pieces and distribute over pizza.'],
                    ['instruction' => 'Bake for 12-15 minutes until crust is golden.'],
                    ['instruction' => 'Top with fresh basil leaves before serving.'],
                ],
            ],
            [
                'title'        => 'Chicken Stir Fry',
                'ingredients'  => [
                    ['ingredient' => '1 lb chicken breast, sliced'],
                    ['ingredient' => '3 tbsp soy sauce'],
                    ['ingredient' => '2 tbsp vegetable oil'],
                    ['ingredient' => '2 large bell peppers'],
                    ['ingredient' => '2 cups broccoli florets'],
                    ['ingredient' => '3 cloves garlic, minced'],
                    ['ingredient' => '1 tbsp ginger, minced'],
                    ['ingredient' => '1 tbsp cornstarch'],
                    ['ingredient' => '1 tsp sesame oil'],
                ],
                'instructions' => [
                    ['instruction' => 'Marinate chicken in 1 tbsp soy sauce for 15 minutes.'],
                    ['instruction' => 'Heat oil in large wok or skillet over high heat.'],
                    ['instruction' => 'Add chicken and cook until no longer pink.'],
                    ['instruction' => 'Remove chicken and set aside.'],
                    ['instruction' => 'Add vegetables to wok and stir-fry for 3-4 minutes.'],
                    ['instruction' => 'Add garlic and ginger, cook for 30 seconds.'],
                    ['instruction' => 'Return chicken to wok.'],
                    ['instruction' => 'Mix remaining soy sauce with cornstarch and add to wok.'],
                    ['instruction' => 'Stir-fry until sauce thickens, about 1 minute.'],
                    ['instruction' => 'Drizzle with sesame oil before serving.'],
                ],
            ],
            [
                'title'        => 'Classic Caesar Salad',
                'ingredients'  => [
                    ['ingredient' => '2 heads romaine lettuce'],
                    ['ingredient' => '½ cup grated parmesan cheese'],
                    ['ingredient' => '1 cup croutons'],
                    ['ingredient' => '½ cup mayonnaise'],
                    ['ingredient' => '2 tbsp lemon juice'],
                    ['ingredient' => '1 tsp worcestershire sauce'],
                    ['ingredient' => '2 cloves garlic, minced'],
                    ['ingredient' => '1 tsp anchovy paste'],
                    ['ingredient' => 'black pepper to taste'],
                ],
                'instructions' => [
                    ['instruction' => 'Wash and chop romaine lettuce into bite-sized pieces.'],
                    ['instruction' => 'In a large bowl, whisk together mayonnaise, lemon juice, worcestershire sauce, garlic, and anchovy paste.'],
                    ['instruction' => 'Add lettuce to bowl and toss with dressing.'],
                    ['instruction' => 'Top with parmesan cheese and croutons.'],
                    ['instruction' => 'Season with black pepper and serve immediately.'],
                ],
            ],
            [
                'title'        => 'Beef Tacos',
                'ingredients'  => [
                    ['ingredient' => '1 lb ground beef'],
                    ['ingredient' => '1 packet taco seasoning'],
                    ['ingredient' => '¾ cup water'],
                    ['ingredient' => '8 corn tortillas'],
                    ['ingredient' => '1 cup shredded lettuce'],
                    ['ingredient' => '2 medium tomatoes, diced'],
                    ['ingredient' => '1 cup shredded cheddar cheese'],
                    ['ingredient' => '½ cup sour cream'],
                    ['ingredient' => '½ cup salsa'],
                ],
                'instructions' => [
                    ['instruction' => 'Brown ground beef in large skillet over medium-high heat.'],
                    ['instruction' => 'Drain excess fat.'],
                    ['instruction' => 'Add taco seasoning and water, simmer for 10 minutes.'],
                    ['instruction' => 'Warm tortillas in microwave or dry skillet.'],
                    ['instruction' => 'Fill tortillas with beef mixture.'],
                    ['instruction' => 'Top with lettuce, tomatoes, cheese, sour cream, and salsa.'],
                    ['instruction' => 'Serve immediately.'],
                ],
            ],
            [
                'title'        => 'Banana Bread',
                'ingredients'  => [
                    ['ingredient' => '3 large ripe bananas'],
                    ['ingredient' => '⅓ cup melted butter'],
                    ['ingredient' => '¾ cup sugar'],
                    ['ingredient' => '1 beaten egg'],
                    ['ingredient' => '1 tsp vanilla extract'],
                    ['ingredient' => '1 tsp baking soda'],
                    ['ingredient' => 'pinch of salt'],
                    ['ingredient' => '1½ cups all-purpose flour'],
                ],
                'instructions' => [
                    ['instruction' => 'Preheat oven to 350°F (175°C).'],
                    ['instruction' => 'Mash bananas in a large bowl.'],
                    ['instruction' => 'Mix in melted butter.'],
                    ['instruction' => 'Add sugar, egg, and vanilla extract.'],
                    ['instruction' => 'Sprinkle baking soda and salt over mixture and mix.'],
                    ['instruction' => 'Add flour and mix until just combined.'],
                    ['instruction' => 'Pour into greased 4×8 inch loaf pan.'],
                    ['instruction' => 'Bake for 60-65 minutes until toothpick comes out clean.'],
                    ['instruction' => 'Cool in pan for 10 minutes, then turn out onto wire rack.'],
                ],
            ],
            [
                'title'        => 'Spaghetti Carbonara',
                'ingredients'  => [
                    ['ingredient' => '1 lb spaghetti'],
                    ['ingredient' => '6 oz pancetta or guanciale, diced'],
                    ['ingredient' => '4 large eggs'],
                    ['ingredient' => '1 cup freshly grated Pecorino Romano cheese'],
                    ['ingredient' => '2 cloves garlic, minced'],
                    ['ingredient' => 'freshly cracked black pepper'],
                    ['ingredient' => 'salt for pasta water'],
                ],
                'instructions' => [
                    ['instruction' => 'Bring a large pot of salted water to boil. Cook spaghetti according to package directions.'],
                    ['instruction' => 'While pasta cooks, sauté pancetta in large skillet until crispy.'],
                    ['instruction' => 'In a bowl, whisk together eggs, cheese, and black pepper.'],
                    ['instruction' => 'Reserve 1 cup pasta cooking water before draining.'],
                    ['instruction' => 'Add drained hot pasta to skillet with pancetta.'],
                    ['instruction' => 'Remove from heat and quickly toss with egg mixture.'],
                    ['instruction' => 'Add pasta water gradually until creamy consistency is reached.'],
                    ['instruction' => 'Serve immediately with extra cheese and pepper.'],
                ],
            ],
            [
                'title'        => 'Thai Green Curry',
                'ingredients'  => [
                    ['ingredient' => '1 lb chicken thigh, sliced'],
                    ['ingredient' => '2 tbsp green curry paste'],
                    ['ingredient' => '1 can (14oz) coconut milk'],
                    ['ingredient' => '1 tbsp fish sauce'],
                    ['ingredient' => '1 tbsp brown sugar'],
                    ['ingredient' => '1 eggplant, cubed'],
                    ['ingredient' => '1 red bell pepper, sliced'],
                    ['ingredient' => '¼ cup Thai basil leaves'],
                    ['ingredient' => '2 kaffir lime leaves'],
                    ['ingredient' => 'jasmine rice for serving'],
                ],
                'instructions' => [
                    ['instruction' => 'Heat 2 tbsp of thick coconut milk in a wok over medium heat.'],
                    ['instruction' => 'Add curry paste and fry for 2 minutes until fragrant.'],
                    ['instruction' => 'Add chicken and cook until no longer pink.'],
                    ['instruction' => 'Add remaining coconut milk, fish sauce, and sugar.'],
                    ['instruction' => 'Bring to a simmer and add eggplant and bell pepper.'],
                    ['instruction' => 'Cook for 10-15 minutes until vegetables are tender.'],
                    ['instruction' => 'Stir in Thai basil and lime leaves.'],
                    ['instruction' => 'Serve hot over jasmine rice.'],
                ],
            ],
        ];

        return $this->faker->randomElement($recipes);
    }

    public function configure(): static
    {
        return $this->afterCreating(function ($recipe) {
            // Only add images to 70% of recipes
            if ($this->faker->boolean(70)) {
                $this->attachRandomImage($recipe);
            }

            // Attach random tags to each recipe
            $this->attachRandomTags($recipe);
        });
    }

    private function attachRandomImage($recipe): void
    {
        $seedImagesPath = storage_path('app/public/seed-images');

        // Get all image files from the seed-images directory
        if ( ! is_dir($seedImagesPath)) {
            return;
        }

        $imageFiles = glob($seedImagesPath.'/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);

        if (empty($imageFiles)) {
            return;
        }

        $randomImagePath = $this->faker->randomElement($imageFiles);

        // Create a temporary copy so we don't consume the original
        $tempPath = storage_path('app/temp/'.basename($randomImagePath));

        // Ensure temp directory exists
        if ( ! is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        // Copy the file
        copy($randomImagePath, $tempPath);

        // Add the copy to media library (this will move it to final location)
        $recipe
            ->addMedia($tempPath)
            ->toMediaCollection('recipe-images');
    }

    private function attachRandomTags($recipe): void
    {
        $allTags = \Spatie\Tags\Tag::all();

        if ($allTags->isEmpty()) {
            return;
        }

        // Attach 1-6 random tags
        $randomTags = $allTags->random($this->faker->numberBetween(1, min(6, $allTags->count())));
        $recipe->attachTags($randomTags);
    }
}
