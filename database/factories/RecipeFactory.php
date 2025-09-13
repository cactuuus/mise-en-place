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
            'recipe_yield'          => $this->getRandomYield(),
            'difficulty_level'      => $this->faker->randomElement(Difficulty::cases()),
        ];
    }

    private function getRandomRecipeData(): array
    {
        $recipes = [
            // Recipe with just steps (some with names, some without)
            [
                'title'        => 'Classic Chocolate Chip Cookies',
                'ingredients'  => [
                    '2¼ cups all-purpose flour',
                    '1 tsp baking soda',
                    '1 tsp salt',
                    '1 cup butter, softened',
                    '¾ cup granulated sugar',
                    '¾ cup brown sugar',
                    '1 tsp vanilla extract',
                    '2 large eggs',
                    '2 cups chocolate chips',
                ],
                'instructions' => [
                    ['type' => 'step', 'position' => 1, 'text' => 'Preheat oven to 375°F (190°C).'],
                    [
                        'type' => 'step', 'position' => 2, 'name' => 'Mix dry ingredients',
                        'text' => 'Mix flour, baking soda, and salt in a bowl.',
                    ],
                    [
                        'type' => 'step', 'position' => 3,
                        'text' => 'Beat butter, sugars, and vanilla in large bowl until creamy.',
                    ],
                    [
                        'type' => 'step', 'position' => 4, 'name' => 'Add eggs',
                        'text' => 'Add eggs one at a time, beating well after each addition.',
                    ],
                    ['type' => 'step', 'position' => 5, 'text' => 'Gradually blend in flour mixture.'],
                    ['type' => 'step', 'position' => 6, 'text' => 'Stir in chocolate chips.'],
                    [
                        'type' => 'step', 'position' => 7, 'name' => 'Shape and bake',
                        'text' => 'Drop rounded tablespoons of dough onto ungreased baking sheets. Bake 9-11 minutes or until golden brown.',
                    ],
                    [
                        'type' => 'step', 'position' => 8,
                        'text' => 'Cool on baking sheets for 2 minutes; remove to wire rack.',
                    ],
                ],
            ],

            // Recipe with sections containing steps
            [
                'title'        => 'Homemade Pizza Margherita',
                'ingredients'  => [
                    '1 lb pizza dough',
                    '2 tbsp olive oil',
                    '1 cup crushed tomatoes',
                    '2 cloves garlic, minced',
                    '8 oz fresh mozzarella',
                    '1 cup fresh basil leaves',
                    'salt to taste',
                    'black pepper to taste',
                ],
                'instructions' => [
                    [
                        'type'     => 'section',
                        'position' => 1,
                        'name'     => 'Prepare the base',
                        'steps'    => [
                            ['type' => 'step', 'position' => 1, 'text' => 'Preheat oven to 475°F (245°C).'],
                            [
                                'type' => 'step', 'position' => 2, 'name' => 'Roll dough',
                                'text' => 'Roll out pizza dough on floured surface and transfer to pizza stone or baking sheet.',
                            ],
                            ['type' => 'step', 'position' => 3, 'text' => 'Brush with olive oil.'],
                        ],
                    ],
                    [
                        'type'     => 'section',
                        'position' => 2,
                        'name'     => 'Add toppings and bake',
                        'steps'    => [
                            [
                                'type' => 'step', 'position' => 1, 'name' => 'Make sauce',
                                'text' => 'Mix crushed tomatoes with minced garlic, salt, and pepper. Spread evenly over dough.',
                            ],
                            [
                                'type' => 'step', 'position' => 2,
                                'text' => 'Tear mozzarella into pieces and distribute over pizza.',
                            ],
                            [
                                'type' => 'step', 'position' => 3,
                                'text' => 'Bake for 12-15 minutes until crust is golden.',
                            ],
                            [
                                'type' => 'step', 'position' => 4, 'name' => 'Finish',
                                'text' => 'Top with fresh basil leaves before serving.',
                            ],
                        ],
                    ],
                ],
            ],

            // Recipe with mix of global steps and sections
            [
                'title'        => 'Chicken Stir Fry',
                'ingredients'  => [
                    '1 lb chicken breast, sliced',
                    '3 tbsp soy sauce',
                    '2 tbsp vegetable oil',
                    '2 large bell peppers',
                    '2 cups broccoli florets',
                    '3 cloves garlic, minced',
                    '1 tbsp ginger, minced',
                    '1 tbsp cornstarch',
                    '1 tsp sesame oil',
                ],
                'instructions' => [
                    [
                        'type' => 'step', 'position' => 1, 'name' => 'Prep beef',
                        'text' => 'Season beef cubes with salt and pepper, then coat with flour.',
                    ],
                    [
                        'type' => 'step', 'position' => 2,
                        'text' => 'Heat oil in large pot over medium-high heat. Brown beef on all sides.',
                    ],
                    [
                        'type'     => 'section',
                        'position' => 3,
                        'name'     => 'Build the stew',
                        'steps'    => [
                            [
                                'type' => 'step', 'position' => 1,
                                'text' => 'Add diced onion to pot and cook until softened.',
                            ],
                            [
                                'type' => 'step', 'position' => 2, 'name' => 'Add liquid',
                                'text' => 'Pour in beef broth and add bay leaves. Bring to a boil.',
                            ],
                            [
                                'type' => 'step', 'position' => 3,
                                'text' => 'Reduce heat to low, cover and simmer for 1 hour.',
                            ],
                        ],
                    ],
                    [
                        'type'     => 'section',
                        'position' => 4,
                        'name'     => 'Add vegetables',
                        'steps'    => [
                            ['type' => 'step', 'position' => 1, 'text' => 'Add carrots and potatoes to the pot.'],
                            [
                                'type' => 'step', 'position' => 2,
                                'text' => 'Continue simmering for 30-45 minutes until vegetables are tender.',
                            ],
                        ],
                    ],
                    [
                        'type' => 'step', 'position' => 5,
                        'text' => 'Remove bay leaves, season with salt and pepper, and serve hot.',
                    ],
                ],
            ],

            // Simple steps-only recipe
            [
                'title'        => 'Classic Caesar Salad',
                'ingredients'  => [
                    '2 heads romaine lettuce',
                    '½ cup grated parmesan cheese',
                    '1 cup croutons',
                    '½ cup mayonnaise',
                    '2 tbsp lemon juice',
                    '1 tsp worcestershire sauce',
                    '2 cloves garlic, minced',
                    '1 tsp anchovy paste',
                    'black pepper to taste',
                ],
                'instructions' => [
                    [
                        'type' => 'step', 'position' => 1, 'name' => 'Prep lettuce',
                        'text' => 'Wash and chop romaine lettuce into bite-sized pieces.',
                    ],
                    [
                        'type' => 'step', 'position' => 2,
                        'text' => 'In a large bowl, whisk together mayonnaise, lemon juice, worcestershire sauce, garlic, and anchovy paste.',
                    ],
                    [
                        'type' => 'step', 'position' => 3, 'name' => 'Combine',
                        'text' => 'Add lettuce to bowl and toss with dressing.',
                    ],
                    [
                        'type' => 'step', 'position' => 4,
                        'text' => 'Top with parmesan cheese and croutons. Season with black pepper and serve immediately.',
                    ],
                ],
            ],
        ];

        return $this->faker->randomElement($recipes);
    }

    private function getRandomYield(): string
    {
        $yields = [
            '1 serving',
            '2 servings',
            '4 servings',
            '6 servings',
            '8 servings',
            '10 servings',
            '12 servings',
            '1 loaf',
            '1 cake',
            '1 pie',
            '24 cookies',
            '1 dozen cookies',
            '2 large pizzas',
            '1 liter',
        ];

        return $this->faker->randomElement($yields);
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
}
