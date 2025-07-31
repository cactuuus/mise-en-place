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
                    ['item' => 'all-purpose flour', 'amount' => '2¼ cups'],
                    ['item' => 'baking soda', 'amount' => '1 tsp'],
                    ['item' => 'salt', 'amount' => '1 tsp'],
                    ['item' => 'butter, softened', 'amount' => '1 cup'],
                    ['item' => 'granulated sugar', 'amount' => '¾ cup'],
                    ['item' => 'brown sugar', 'amount' => '¾ cup'],
                    ['item' => 'vanilla extract', 'amount' => '1 tsp'],
                    ['item' => 'large eggs', 'amount' => '2'],
                    ['item' => 'chocolate chips', 'amount' => '2 cups'],
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
                    ['item' => 'pizza dough', 'amount' => '1 lb'],
                    ['item' => 'olive oil', 'amount' => '2 tbsp'],
                    ['item' => 'crushed tomatoes', 'amount' => '1 cup'],
                    ['item' => 'garlic, minced', 'amount' => '2 cloves'],
                    ['item' => 'fresh mozzarella', 'amount' => '8 oz'],
                    ['item' => 'fresh basil leaves', 'amount' => '1 cup'],
                    ['item' => 'salt', 'amount' => 'to taste'],
                    ['item' => 'black pepper', 'amount' => 'to taste'],
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
                    ['item' => 'chicken breast, sliced', 'amount' => '1 lb'],
                    ['item' => 'soy sauce', 'amount' => '3 tbsp'],
                    ['item' => 'vegetable oil', 'amount' => '2 tbsp'],
                    ['item' => 'bell peppers', 'amount' => '2 large'],
                    ['item' => 'broccoli florets', 'amount' => '2 cups'],
                    ['item' => 'garlic, minced', 'amount' => '3 cloves'],
                    ['item' => 'ginger, minced', 'amount' => '1 tbsp'],
                    ['item' => 'cornstarch', 'amount' => '1 tbsp'],
                    ['item' => 'sesame oil', 'amount' => '1 tsp'],
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
                    ['item' => 'romaine lettuce', 'amount' => '2 heads'],
                    ['item' => 'parmesan cheese', 'amount' => '½ cup grated'],
                    ['item' => 'croutons', 'amount' => '1 cup'],
                    ['item' => 'mayonnaise', 'amount' => '½ cup'],
                    ['item' => 'lemon juice', 'amount' => '2 tbsp'],
                    ['item' => 'worcestershire sauce', 'amount' => '1 tsp'],
                    ['item' => 'garlic, minced', 'amount' => '2 cloves'],
                    ['item' => 'anchovy paste', 'amount' => '1 tsp'],
                    ['item' => 'black pepper', 'amount' => 'to taste'],
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
                    ['item' => 'ground beef', 'amount' => '1 lb'],
                    ['item' => 'taco seasoning', 'amount' => '1 packet'],
                    ['item' => 'water', 'amount' => '¾ cup'],
                    ['item' => 'corn tortillas', 'amount' => '8'],
                    ['item' => 'lettuce, shredded', 'amount' => '1 cup'],
                    ['item' => 'tomatoes, diced', 'amount' => '2 medium'],
                    ['item' => 'cheddar cheese, shredded', 'amount' => '1 cup'],
                    ['item' => 'sour cream', 'amount' => '½ cup'],
                    ['item' => 'salsa', 'amount' => '½ cup'],
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
                    ['item' => 'ripe bananas', 'amount' => '3 large'],
                    ['item' => 'melted butter', 'amount' => '⅓ cup'],
                    ['item' => 'sugar', 'amount' => '¾ cup'],
                    ['item' => 'egg, beaten', 'amount' => '1'],
                    ['item' => 'vanilla extract', 'amount' => '1 tsp'],
                    ['item' => 'baking soda', 'amount' => '1 tsp'],
                    ['item' => 'salt', 'amount' => 'pinch'],
                    ['item' => 'all-purpose flour', 'amount' => '1½ cups'],
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

        $recipe
            ->addMedia($randomImagePath)
            ->toMediaCollection('recipe-images');
    }
}
