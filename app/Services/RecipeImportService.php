<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecipeImportService
{
    /**
     * Import recipe data from a URL using Schema.org structured data
     */
    public static function importFromUrl(string $url): array
    {
        if ( ! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid URL provided');
        }

        try {
            $html           = self::fetchWebpageContent($url);
            $structuredData = self::extractStructuredData($html);

            Log::info(json_encode($structuredData));

            if ( ! $structuredData) {
                throw new Exception('No recipe found on this page. This site might not support structured recipe data.');
            }

            $recipeData               = self::parseRecipeData($structuredData);
            $recipeData['source_url'] = $url;

            Log::info('Successfully imported recipe from structured data', [
                'url'               => $url,
                'title'             => $recipeData['title'],
                'ingredient_count'  => count($recipeData['ingredients']),
                'instruction_count' => count($recipeData['instructions']),
            ]);

            return $recipeData;
        } catch (Exception $e) {
            throw new Exception('Failed to import recipe: '.$e->getMessage());
        }
    }

    /**
     * Fetch webpage content
     */
    private static function fetchWebpageContent(string $url): string
    {
        $response = Http::timeout(10)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ])
            ->get($url);

        if ( ! $response->successful()) {
            throw new Exception('Failed to fetch webpage: HTTP '.$response->status());
        }

        return $response->body();
    }

    /**
     * Extract JSON-LD structured data from HTML
     */
    private static function extractStructuredData(string $html): ?array
    {
        // Find all JSON-LD script tags
        if ( ! preg_match_all('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html,
            $matches)) {
            return null;
        }

        foreach ($matches[1] as $jsonText) {
            $data = json_decode(trim($jsonText), true);

            if ( ! $data) {
                continue;
            }

            $recipe = self::findRecipeInData($data);
            if ($recipe) {
                return $recipe;
            }
        }

        return null;
    }

    /**
     * Find recipe object in structured data
     */
    private static function findRecipeInData(array $data): ?array
    {
        // Direct recipe object
        if (isset($data['@type']) && $data['@type'] === 'Recipe') {
            return $data;
        }

        // Look in @graph array
        if (isset($data['@graph']) && is_array($data['@graph'])) {
            foreach ($data['@graph'] as $item) {
                if (isset($item['@type']) && $item['@type'] === 'Recipe') {
                    return $item;
                }
            }
        }

        // Look in array of items
        if (is_array($data)) {
            foreach ($data as $item) {
                if (is_array($item) && isset($item['@type']) && $item['@type'] === 'Recipe') {
                    return $item;
                }
            }
        }

        return null;
    }

    /**
     * Parse recipe data from Schema.org format
     */
    private static function parseRecipeData(array $recipe): array
    {
        return [
            'title'        => self::extractTitle($recipe),
            'ingredients'  => self::extractIngredients($recipe),
            'instructions' => self::extractInstructions($recipe),
            'prep_time'    => self::extractTime($recipe, 'prepTime'),
            'cook_time'    => self::extractTime($recipe, 'cookTime'),
            'serves'       => self::extractServings($recipe),
            'tags'         => self::extractTags($recipe),
        ];
    }

    /**
     * Extract recipe title
     */
    private static function extractTitle(array $recipe): ?string
    {
        if ( ! isset($recipe['name']) || empty(trim($recipe['name']))) {
            return null;
        }

        $title = strip_tags(trim($recipe['name']));

        return ! empty($title) ? $title : null;
    }

    /**
     * Extract ingredients as simple array of strings
     */
    private static function extractIngredients(array $recipe): array
    {
        $ingredients    = [];
        $rawIngredients = $recipe['recipeIngredient'] ?? [];

        if ( ! is_array($rawIngredients)) {
            return [];
        }

        foreach ($rawIngredients as $ingredient) {
            $ingredient = strip_tags(trim($ingredient));
            if ( ! empty($ingredient)) {
                $ingredients[] = ['ingredient' => $ingredient];
            }
        }

        return $ingredients;
    }

    /**
     * Extract instructions
     */
    private static function extractInstructions(array $recipe): array
    {
        $instructions    = [];
        $rawInstructions = $recipe['recipeInstructions'] ?? [];

        if ( ! is_array($rawInstructions)) {
            return [];
        }

        foreach ($rawInstructions as $instruction) {
            $text = '';

            if (is_string($instruction)) {
                $text = $instruction;
            } elseif (is_array($instruction)) {
                $text = $instruction['text'] ?? $instruction['name'] ?? '';
            }

            $text = strip_tags(trim($text));
            if ( ! empty($text)) {
                $instructions[] = ['instruction' => $text];
            }
        }

        return $instructions;
    }

    /**
     * Extract time in minutes
     */
    private static function extractTime(array $recipe, string $timeField): ?int
    {
        if ( ! isset($recipe[$timeField])) {
            return null;
        }

        $minutes = self::parseDuration($recipe[$timeField]);

        return $minutes;
    }

    /**
     * Parse duration to minutes
     */
    private static function parseDuration(string $duration): int
    {
        // ISO 8601 format (PT15M, PT1H30M)
        if (preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?/i', $duration, $matches)) {
            $hours   = isset($matches[1]) ? (int) $matches[1] : 0;
            $minutes = isset($matches[2]) ? (int) $matches[2] : 0;

            return ($hours * 60) + $minutes;
        }

        // Plain numbers
        if (is_numeric($duration)) {
            return (int) $duration;
        }

        return 0;
    }

    /**
     * Extract servings
     */
    private static function extractServings(array $recipe): ?int
    {
        $fields = ['recipeYield', 'yield', 'serves'];

        foreach ($fields as $field) {
            if (isset($recipe[$field])) {
                $value = $recipe[$field];

                if (is_numeric($value)) {
                    $servings = (int) $value;

                    return $servings > 0 ? $servings : null;
                }

                if (is_array($value) && ! empty($value)) {
                    $value = $value[0];
                }

                if (is_string($value) && preg_match('/(\d+)/', $value, $matches)) {
                    $servings = (int) $matches[1];

                    return $servings > 0 ? $servings : null;
                }
            }
        }

        return null;
    }

    /**
     * Extract tags from categories, cuisine, keywords
     */
    private static function extractTags(array $recipe): array
    {
        $tags = [];

        // Categories
        if (isset($recipe['recipeCategory'])) {
            $categories = is_array($recipe['recipeCategory']) ? $recipe['recipeCategory'] : [$recipe['recipeCategory']];
            foreach ($categories as $category) {
                $tag = strtolower(strip_tags(trim($category)));
                if ( ! empty($tag)) {
                    $tags[] = $tag;
                }
            }
        }

        // Cuisine
        if (isset($recipe['recipeCuisine'])) {
            $cuisines = is_array($recipe['recipeCuisine']) ? $recipe['recipeCuisine'] : [$recipe['recipeCuisine']];
            foreach ($cuisines as $cuisine) {
                $tag = strtolower(strip_tags(trim($cuisine)));
                if ( ! empty($tag)) {
                    $tags[] = $tag;
                }
            }
        }

        // Keywords
        if (isset($recipe['keywords'])) {
            if (is_string($recipe['keywords'])) {
                $keywords = array_map('trim', explode(',', $recipe['keywords']));
                foreach ($keywords as $keyword) {
                    $tag = strtolower(strip_tags($keyword));
                    if ( ! empty($tag) && strlen($tag) > 1) {
                        $tags[] = $tag;
                    }
                }
            } elseif (is_array($recipe['keywords'])) {
                foreach ($recipe['keywords'] as $keyword) {
                    $tag = strtolower(strip_tags(trim($keyword)));
                    if ( ! empty($tag) && strlen($tag) > 1) {
                        $tags[] = $tag;
                    }
                }
            }
        }

        // Clean up tags - remove duplicates and empty values
        $tags = array_filter(array_unique($tags), function ($tag) {
            return ! empty($tag) && strlen($tag) > 1;
        });

        return array_values($tags);
    }
}
