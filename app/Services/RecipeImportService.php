<?php

namespace App\Services;

use Exception;

class RecipeImportService
{
    /**
     * Import recipe data from a URL
     *
     * @param  string  $url
     *
     * @return array
     * @throws Exception
     */
    public function importFromUrl(string $url): array
    {
        // Validate URL
        if ( ! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid URL provided');
        }

        // For now, return mock data
        // Later you can replace this with your neuronAI agent logic
        return $this->getMockRecipeData($url);
    }

    /**
     * Mock recipe data for testing
     * This will be replaced with actual scraping/AI logic later
     */
    private function getMockRecipeData(string $sourceUrl): array
    {
        return [
            'title'            => 'Classic Chocolate Chip Cookies',
            'ingredients'      => [
                ['amount' => '2 cups', 'item' => 'all-purpose flour'],
                ['amount' => '1 tsp', 'item' => 'baking soda'],
                ['amount' => '1/2 tsp', 'item' => 'salt'],
                ['amount' => '1 cup', 'item' => 'butter, softened'],
                ['amount' => '3/4 cup', 'item' => 'brown sugar'],
                ['amount' => '1/4 cup', 'item' => 'white sugar'],
                ['amount' => '2 large', 'item' => 'eggs'],
                ['amount' => '2 tsp', 'item' => 'vanilla extract'],
                ['amount' => '2 cups', 'item' => 'chocolate chips'],
            ],
            'instructions'     => [
                ['instruction' => 'Preheat oven to 375°F (190°C). Line baking sheets with parchment paper.'],
                ['instruction' => 'In a medium bowl, whisk together flour, baking soda, and salt. Set aside.'],
                ['instruction' => 'In a large bowl, cream together the softened butter and both sugars until light and fluffy, about 3-4 minutes.'],
                ['instruction' => 'Beat in eggs one at a time, then add vanilla extract.'],
                ['instruction' => 'Gradually mix in the flour mixture until just combined. Don\'t overmix.'],
                ['instruction' => 'Fold in chocolate chips.'],
                ['instruction' => 'Drop rounded tablespoons of dough onto prepared baking sheets, spacing them 2 inches apart.'],
                ['instruction' => 'Bake for 9-11 minutes, until edges are golden but centers still look slightly underbaked.'],
                ['instruction' => 'Cool on baking sheet for 5 minutes before transferring to a wire rack.'],
            ],
            'prep_time'        => 15,
            'cook_time'        => 10,
            'serves'           => 24,
            'difficulty_level' => 'easy',
            'source_url'       => $sourceUrl,
        ];
    }
}
