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
                    ['2¼ cups all-purpose flour'],
                    ['1 tsp baking soda'],
                    ['1 tsp salt'],
                    ['1 cup butter, softened'],
                    ['¾ cup granulated sugar'],
                    ['¾ cup brown sugar'],
                    ['1 tsp vanilla extract'],
                    ['2 large eggs'],
                    ['2 cups chocolate chips'],
                ],
                'instructions' => [
                    ['Preheat oven to 375°F (190°C).'],
                    ['Mix flour, baking soda, and salt in a bowl.'],
                    ['Beat butter, sugars, and vanilla in large bowl until creamy.'],
                    ['Add eggs one at a time, beating well after each addition.'],
                    ['Gradually blend in flour mixture.'],
                    ['Stir in chocolate chips.'],
                    ['Drop rounded tablespoons of dough onto ungreased baking sheets.'],
                    ['Bake 9-11 minutes or until golden brown.'],
                    ['Cool on baking sheets for 2 minutes; remove to wire rack.'],
                ],
            ],
            [
                'title'        => 'Homemade Pizza Margherita',
                'ingredients'  => [
                    ['1 lb pizza dough'],
                    ['2 tbsp olive oil'],
                    ['1 cup crushed tomatoes'],
                    ['2 cloves garlic, minced'],
                    ['8 oz fresh mozzarella'],
                    ['1 cup fresh basil leaves'],
                    ['salt to taste'],
                    ['black pepper to taste'],
                ],
                'instructions' => [
                    ['Preheat oven to 475°F (245°C).'],
                    ['Roll out pizza dough on floured surface.'],
                    ['Transfer to pizza stone or baking sheet.'],
                    ['Brush with olive oil.'],
                    ['Mix crushed tomatoes with minced garlic, salt, and pepper.'],
                    ['Spread tomato mixture evenly over dough.'],
                    ['Tear mozzarella into pieces and distribute over pizza.'],
                    ['Bake for 12-15 minutes until crust is golden.'],
                    ['Top with fresh basil leaves before serving.'],
                ],
            ],
            [
                'title'        => 'Chicken Stir Fry',
                'ingredients'  => [
                    ['1 lb chicken breast, sliced'],
                    ['3 tbsp soy sauce'],
                    ['2 tbsp vegetable oil'],
                    ['2 large bell peppers'],
                    ['2 cups broccoli florets'],
                    ['3 cloves garlic, minced'],
                    ['1 tbsp ginger, minced'],
                    ['1 tbsp cornstarch'],
                    ['1 tsp sesame oil'],
                ],
                'instructions' => [
                    ['Marinate chicken in 1 tbsp soy sauce for 15 minutes.'],
                    ['Heat oil in large wok or skillet over high heat.'],
                    ['Add chicken and cook until no longer pink.'],
                    ['Remove chicken and set aside.'],
                    ['Add vegetables to wok and stir-fry for 3-4 minutes.'],
                    ['Add garlic and ginger, cook for 30 seconds.'],
                    ['Return chicken to wok.'],
                    ['Mix remaining soy sauce with cornstarch and add to wok.'],
                    ['Stir-fry until sauce thickens, about 1 minute.'],
                    ['Drizzle with sesame oil before serving.'],
                ],
            ],
            [
                'title'        => 'Classic Caesar Salad',
                'ingredients'  => [
                    ['2 heads romaine lettuce'],
                    ['½ cup grated parmesan cheese'],
                    ['1 cup croutons'],
                    ['½ cup mayonnaise'],
                    ['2 tbsp lemon juice'],
                    ['1 tsp worcestershire sauce'],
                    ['2 cloves garlic, minced'],
                    ['1 tsp anchovy paste'],
                    ['black pepper to taste'],
                ],
                'instructions' => [
                    ['Wash and chop romaine lettuce into bite-sized pieces.'],
                    ['In a large bowl, whisk together mayonnaise, lemon juice, worcestershire sauce, garlic, and anchovy paste.'],
                    ['Add lettuce to bowl and toss with dressing.'],
                    ['Top with parmesan cheese and croutons.'],
                    ['Season with black pepper and serve immediately.'],
                ],
            ],
            [
                'title'        => 'Beef Tacos',
                'ingredients'  => [
                    ['1 lb ground beef'],
                    ['1 packet taco seasoning'],
                    ['¾ cup water'],
                    ['8 corn tortillas'],
                    ['1 cup shredded lettuce'],
                    ['2 medium tomatoes, diced'],
                    ['1 cup shredded cheddar cheese'],
                    ['½ cup sour cream'],
                    ['½ cup salsa'],
                ],
                'instructions' => [
                    ['Brown ground beef in large skillet over medium-high heat.'],
                    ['Drain excess fat.'],
                    ['Add taco seasoning and water, simmer for 10 minutes.'],
                    ['Warm tortillas in microwave or dry skillet.'],
                    ['Fill tortillas with beef mixture.'],
                    ['Top with lettuce, tomatoes, cheese, sour cream, and salsa.'],
                    ['Serve immediately.'],
                ],
            ],
            [
                'title'        => 'Banana Bread',
                'ingredients'  => [
                    ['3 large ripe bananas'],
                    ['⅓ cup melted butter'],
                    ['¾ cup sugar'],
                    ['1 beaten egg'],
                    ['1 tsp vanilla extract'],
                    ['1 tsp baking soda'],
                    ['pinch of salt'],
                    ['1½ cups all-purpose flour'],
                ],
                'instructions' => [
                    ['Preheat oven to 350°F (175°C).'],
                    ['Mash bananas in a large bowl.'],
                    ['Mix in melted butter.'],
                    ['Add sugar, egg, and vanilla extract.'],
                    ['Sprinkle baking soda and salt over mixture and mix.'],
                    ['Add flour and mix until just combined.'],
                    ['Pour into greased 4×8 inch loaf pan.'],
                    ['Bake for 60-65 minutes until toothpick comes out clean.'],
                    ['Cool in pan for 10 minutes, then turn out onto wire rack.'],
                ],
            ],
            [
                'title'        => 'Spaghetti Carbonara',
                'ingredients'  => [
                    ['1 lb spaghetti'],
                    ['6 oz pancetta or guanciale, diced'],
                    ['4 large eggs'],
                    ['1 cup freshly grated Pecorino Romano cheese'],
                    ['2 cloves garlic, minced'],
                    ['freshly cracked black pepper'],
                    ['salt for pasta water'],
                ],
                'instructions' => [
                    ['Bring a large pot of salted water to boil. Cook spaghetti according to package directions.'],
                    ['While pasta cooks, sauté pancetta in large skillet until crispy.'],
                    ['In a bowl, whisk together eggs, cheese, and black pepper.'],
                    ['Reserve 1 cup pasta cooking water before draining.'],
                    ['Add drained hot pasta to skillet with pancetta.'],
                    ['Remove from heat and quickly toss with egg mixture.'],
                    ['Add pasta water gradually until creamy consistency is reached.'],
                    ['Serve immediately with extra cheese and pepper.'],
                ],
            ],
            [
                'title'        => 'Thai Green Curry',
                'ingredients'  => [
                    ['1 lb chicken thigh, sliced'],
                    ['2 tbsp green curry paste'],
                    ['1 can (14oz) coconut milk'],
                    ['1 tbsp fish sauce'],
                    ['1 tbsp brown sugar'],
                    ['1 eggplant, cubed'],
                    ['1 red bell pepper, sliced'],
                    ['¼ cup Thai basil leaves'],
                    ['2 kaffir lime leaves'],
                    ['jasmine rice for serving'],
                ],
                'instructions' => [
                    ['Heat 2 tbsp of thick coconut milk in a wok over medium heat.'],
                    ['Add curry paste and fry for 2 minutes until fragrant.'],
                    ['Add chicken and cook until no longer pink.'],
                    ['Add remaining coconut milk, fish sauce, and sugar.'],
                    ['Bring to a simmer and add eggplant and bell pepper.'],
                    ['Cook for 10-15 minutes until vegetables are tender.'],
                    ['Stir in Thai basil and lime leaves.'],
                    ['Serve hot over jasmine rice.'],
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
