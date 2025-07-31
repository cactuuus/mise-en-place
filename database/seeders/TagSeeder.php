<?php

namespace Database\Seeders;

use App\Enums\TagType;
use Illuminate\Database\Seeder;
use Spatie\Tags\Tag;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            'type'   => TagType::Recipe->value,
            'values' => [
                // Dietary restrictions
                'Vegetarian',
                'Vegan',
                'Gluten-Free',
                'Dairy-Free',
                'Keto',
                'Low-Carb',

                // Cuisine types
                'Italian',
                'Mexican',
                'Asian',
                'Mediterranean',
                'American',

                // Cooking style
                'Quick & Easy',
                'One-Pot',
                'Make-Ahead',
                'Kid-Friendly',

                // Flavor profiles
                'Spicy',
                'Sweet',
                'Comfort Food',
            ],
        ];

        foreach ($tags['values'] as $tagName) {
            Tag::findOrCreate($tagName, $tags['type']);
        }
    }
}
