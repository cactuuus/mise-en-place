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
        $tagGroups = [
            [
                'type'   => TagType::RecipeCuisine->value,
                'values' => [
                    // European
                    'italian', 'french', 'spanish', 'greek', 'german', 'british', 'irish',
                    'portuguese', 'dutch', 'belgian', 'swiss', 'austrian', 'polish',
                    'russian', 'scandinavian', 'hungarian', 'czech', 'romanian',

                    // Asian
                    'chinese', 'japanese', 'korean', 'thai', 'vietnamese', 'indian',
                    'pakistani', 'bangladeshi', 'sri lankan', 'malaysian', 'indonesian',
                    'filipino', 'singaporean', 'cambodian', 'laotian', 'burmese',
                    'nepalese', 'tibetan', 'mongolian',

                    // Middle Eastern & North African
                    'lebanese', 'turkish', 'persian', 'moroccan', 'egyptian',
                    'israeli', 'syrian', 'iraqi', 'jordanian', 'tunisian',
                    'algerian', 'ethiopian', 'sudanese',

                    // American
                    'american', 'mexican', 'tex mex', 'cajun', 'creole', 'southern',
                    'southwestern', 'canadian', 'brazilian', 'peruvian', 'argentinian',
                    'colombian', 'venezuelan', 'chilean', 'ecuadorian', 'bolivian',
                    'cuban', 'puerto rican', 'jamaican', 'caribbean',

                    // African
                    'west african', 'east african', 'south african', 'nigerian',
                    'ghanaian', 'kenyan', 'tanzanian', 'senegalese',

                    // Oceanian
                    'australian', 'new zealand',

                    // Regional/Style
                    'mediterranean', 'nordic', 'baltic', 'balkan', 'central asian',
                    'southeast asian', 'east asian', 'south asian', 'fusion',
                    'international', 'modern american', 'continental',
                ],
            ],
            [
                'type'   => TagType::RecipeCategory->value,
                'values' => [
                    // Meal types
                    'breakfast', 'brunch', 'lunch', 'dinner', 'snack',

                    // Course types
                    'appetizer', 'main course', 'side dish', 'dessert', 'salad',
                    'soup', 'sauce', 'condiment', 'dressing', 'marinade',

                    // Food categories
                    'bread', 'pasta', 'rice', 'noodles', 'pizza', 'sandwich',
                    'burger', 'taco', 'wrap', 'bowl', 'stir fry', 'curry',
                    'stew', 'casserole', 'roast', 'grill', 'bbq',

                    // Beverages
                    'beverage', 'smoothie', 'juice', 'cocktail', 'mocktail',

                    // Baking
                    'cake', 'cookie', 'pie', 'pastry', 'muffin', 'cupcake',
                ],
            ],
            [
                'type'   => TagType::RecipeDiet->value,
                'values' => [
                    'diabetic', 'gluten free', 'halal', 'hindu', 'kosher',
                    'low calorie', 'low fat', 'low lactose', 'low salt',
                    'vegan', 'vegetarian',
                ],
            ],
            [
                'type'   => TagType::RecipeKeyword->value,
                'values' => [
                    // Cooking style
                    'quick & easy', 'one pot', 'one pan', 'no bake', 'make ahead',
                    'freezer friendly', 'slow cooker', 'instant pot', 'air fryer',
                    'grilled', 'baked', 'fried', 'steamed', 'raw',

                    // Characteristics
                    'kid friendly', 'budget friendly', 'healthy', 'comfort food',
                    'gourmet', 'restaurant style', 'holiday', 'party food',
                    'crowd pleaser', 'date night', 'meal prep',

                    // Flavor profiles
                    'spicy', 'mild', 'sweet', 'savory', 'tangy', 'smoky',
                    'fresh', 'creamy', 'crunchy', 'hearty', 'light',

                    // Season/Time
                    'summer', 'winter', 'spring', 'fall', 'cold weather',
                    'hot weather', 'weekend', 'weeknight', '15 minute',
                    '30 minute', 'under 1 hour',
                ],
            ],
        ];

        foreach ($tagGroups as $group) {
            foreach ($group['values'] as $tagName) {
                Tag::findOrCreate($tagName, $group['type']);
            }
        }
    }
}
