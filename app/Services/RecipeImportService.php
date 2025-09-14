<?php

namespace App\Services;

use App\Helpers\TagHelper;
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

            if ( ! $structuredData) {
                throw new Exception('No recipe found on this page. This site might not be supported (yet!).');
            }

            $recipeData               = self::parseRecipeData($structuredData);
            $recipeData['source_url'] = $url;

            // Fetch the image and return its data
            $originalImageUrl = self::extractImageUrl($structuredData);
            if ($originalImageUrl) {
                try {
                    $imageData = self::fetchImageData($originalImageUrl);
                    if ($imageData) {
                        $recipeData['imported_image_data'] = base64_encode($imageData['body']);
                        $recipeData['imported_image_mime'] = $imageData['mime'];
                    }
                } catch (Exception $e) {
                    // Log the image fetch failure but don't fail the whole import
                    Log::warning('Failed to fetch and embed image data', [
                        'url'   => $originalImageUrl,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return $recipeData;
        } catch (Exception $e) {
            Log::error('Recipe import failed', [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Failed to import recipe: '.$e->getMessage());
        }
    }

    /**
     * Fetch the HTML content of a given URL.
     */
    private static function fetchWebpageContent(string $url): string
    {
        $response = Http::timeout(10)->get($url);

        if ( ! $response->successful()) {
            throw new Exception('Failed to fetch URL content: HTTP '.$response->status());
        }

        return $response->body();
    }

    /**
     * Extract structured data from HTML content.
     */
    private static function extractStructuredData(string $html): ?array
    {
        // Regex to find a script tag with type="application/ld+json"
        $pattern = '/<script[^>]*type=["\']?application\/ld\+json["\']?[^>]*>(.*?)<\/script>/is';
        preg_match_all($pattern, $html, $matches);

        if (empty($matches[1])) {
            return null;
        }

        foreach ($matches[1] as $json) {
            $data = json_decode(trim($json), true);
            if ( ! is_array($data)) {
                continue;
            }

            $recipeData = self::findRecipeInData($data);
            if ($recipeData) {
                return $recipeData;
            }
        }

        return null;
    }

    /**
     * Searches for a Recipe object within a structured data array, handling variations.
     */
    private static function findRecipeInData(array $data): ?array
    {
        // Check if the root object is a Recipe
        if (self::isRecipe($data)) {
            return $data;
        }

        // Check if the root object contains a list of objects or a graph
        if (is_array($data)) {
            foreach ($data as $item) {
                if (is_array($item) && self::isRecipe($item)) {
                    return $item;
                }
            }
        }

        // This is a common pattern for sites that group all data under a @graph key
        if (isset($data['@graph']) && is_array($data['@graph'])) {
            foreach ($data['@graph'] as $item) {
                if (is_array($item) && self::isRecipe($item)) {
                    return $item;
                }
            }
        }

        return null;
    }

    /**
     * Checks if a structured data object is a recipe, handling string and array types.
     */
    private static function isRecipe(array $data): bool
    {
        if (isset($data['@type'])) {
            $type = $data['@type'];
            if (is_string($type) && $type === 'Recipe') {
                return true;
            }
            if (is_array($type) && in_array('Recipe', $type)) {
                return true;
            }
        }

        return false;
    }

    private static function parseRecipeData(array $recipe): array
    {
        return [
            'title'        => self::extractTitle($recipe),
            'ingredients'  => self::extractIngredients($recipe),
            'instructions' => self::extractInstructions($recipe),
            'notes'        => self::extractNotes($recipe),
            'prep_time'    => self::extractTime($recipe, 'prepTime'),
            'cook_time'    => self::extractTime($recipe, 'cookTime'),
            'recipe_yield' => self::extractYield($recipe),
            'tags'         => self::extractTags($recipe),
            'is_public'    => true,
        ];
    }

    private static function extractTitle(array $recipe): ?string
    {
        $title = $recipe['name'] ?? $recipe['headline'] ?? null;
        if ( ! $title) {
            return null;
        }

        $title = strip_tags(trim($title));

        return ! empty($title) ? $title : null;
    }

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
                $ingredients[] = $ingredient; // Simple string, not array
            }
        }

        return $ingredients;
    }

    /**
     * Parses the recipe instructions from Schema.org format into our custom structure.
     *
     * Handles HowToStep, HowToSection, and simple string arrays.
     */
    private static function extractInstructions(array $recipe): array
    {
        $instructions    = [];
        $rawInstructions = $recipe['recipeInstructions'] ?? [];

        if (empty($rawInstructions)) {
            return [];
        }

        $position = 1;

        if (is_string($rawInstructions)) {
            $rawInstructions = [$rawInstructions];
        }

        foreach ($rawInstructions as $item) {
            if (is_array($item) && isset($item['@type']) && $item['@type'] === 'HowToSection') {
                $instructions = array_merge($instructions, self::flattenHowToSection($item, $position));
            } else {
                $stepText = self::extractStepText($item);
                if ( ! empty($stepText)) {
                    $instructions[] = [
                        'type'     => 'step',
                        'position' => $position++,
                        'text'     => $stepText,
                        'name'     => self::extractStepName($item),
                    ];
                }
            }
        }

        return array_values($instructions); // Re-index array
    }

    private static function flattenHowToSection(array $section, int &$position): array
    {
        $flattened   = [];
        $sectionName = self::extractText($section['name'] ?? '');

        // Add section as a step if it has a meaningful name
        if ( ! empty($sectionName)) {
            $flattened[] = [
                'type'     => 'section',
                'position' => $position++,
                'name'     => $sectionName,
                'steps'    => [],
            ];
        }

        $stepPosition = 1;
        $sectionSteps = [];

        if (isset($section['itemListElement']) && is_array($section['itemListElement'])) {
            foreach ($section['itemListElement'] as $item) {
                // Recursively flatten nested sections
                if (is_array($item) && isset($item['@type']) && $item['@type'] === 'HowToSection') {
                    $nestedSteps = self::flattenHowToSection($item, $position);
                    $flattened   = array_merge($flattened, $nestedSteps);
                } else {
                    $stepText = self::extractStepText($item);
                    if ( ! empty($stepText)) {
                        $sectionSteps[] = [
                            'type'     => 'step',
                            'position' => $stepPosition++,
                            'text'     => $stepText,
                            'name'     => self::extractStepName($item),
                        ];
                    }
                }
            }
        }

        // If we created a section and have steps, add them
        if ( ! empty($sectionName) && ! empty($sectionSteps)) {
            $flattened[count($flattened) - 1]['steps'] = $sectionSteps;
        } elseif ( ! empty($sectionSteps)) {
            // If no section name, add steps directly
            $flattened = array_merge($flattened, $sectionSteps);
        }

        return $flattened;
    }

    /**
     * Extracts text from a value which can be an array or string
     */
    private static function extractText(mixed $value): ?string
    {
        if (is_string($value)) {
            return strip_tags(trim($value));
        }

        if (is_array($value) && ! empty($value)) {
            $first = reset($value);

            return is_string($first) ? strip_tags(trim($first)) : null;
        }

        return null;
    }

    private static function extractStepText($item): ?string
    {
        if (is_string($item)) {
            return self::extractText($item);
        }

        if (is_array($item) && isset($item['@type']) && $item['@type'] === 'HowToStep') {
            return self::extractText($item['text'] ?? '');
        }

        return null;
    }

    private static function extractStepName($item): ?string
    {
        if (is_array($item) && isset($item['name'])) {
            return self::extractText($item['name']);
        }

        return null;
    }

    private static function extractNotes(array $recipe): ?string
    {
        $notes = $recipe['description'] ?? null;
        if ( ! $notes) {
            return null;
        }

        $notes = strip_tags(trim($notes));

        return ! empty($notes) ? $notes : null;
    }

    private static function extractTime(array $recipe, string $timeField): ?int
    {
        if ( ! isset($recipe[$timeField])) {
            return null;
        }

        return self::parseDuration($recipe[$timeField]);
    }

    private static function parseDuration(string $duration): int
    {
        // ISO 8601 format (PT15M, PT1H30M)
        if (preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?/i', $duration, $matches)) {
            $hours   = isset($matches[1]) ? (int) $matches[1] : 0;
            $minutes = isset($matches[2]) ? (int) $matches[2] : 0;

            return ($hours * 60) + $minutes;
        }

        // Plain numbers (assume minutes)
        if (is_numeric($duration)) {
            return (int) $duration;
        }

        return 0;
    }

    private static function extractYield(array $recipe): ?string
    {
        if (isset($recipe['recipeYield'])) {
            $yield = $recipe['recipeYield'];

            // Handle array of yields - concatenate with separator
            if (is_array($yield)) {
                $yieldStrings = [];
                foreach ($yield as $yieldValue) {
                    if (is_array($yieldValue) && isset($yieldValue['value'])) {
                        $yieldStrings[] = (string) $yieldValue['value'];
                    } elseif ( ! empty($yieldValue)) {
                        $yieldStrings[] = (string) $yieldValue;
                    }
                }

                return ! empty($yieldStrings) ? implode(', ', $yieldStrings) : null;
            }

            // Handle QuantitativeValue object
            if (is_array($yield) && isset($yield['value'])) {
                return (string) $yield['value'];
            }

            // Direct text or number
            return (string) $yield;
        }

        return null;
    }

    private static function extractTags(array $recipe): array
    {
        $tags = [
            'recipe_cuisine'  => [],
            'recipe_category' => [],
            'recipe_diet'     => [],
            'recipe_keyword'  => [],
        ];

        // Extract cuisine
        if (isset($recipe['recipeCuisine'])) {
            $cuisines = is_array($recipe['recipeCuisine']) ? $recipe['recipeCuisine'] : [$recipe['recipeCuisine']];
            foreach ($cuisines as $cuisine) {
                $normalized = TagHelper::normalizeTag($cuisine, 'recipe_cuisine');
                if ($normalized) {
                    $tags['recipe_cuisine'][] = $normalized;
                }
            }
        }

        // Extract categories
        if (isset($recipe['recipeCategory'])) {
            $categories = is_array($recipe['recipeCategory']) ? $recipe['recipeCategory'] : [$recipe['recipeCategory']];
            foreach ($categories as $category) {
                $normalized = TagHelper::normalizeTag($category, 'recipe_category');
                if ($normalized) {
                    $tags['recipe_category'][] = $normalized;
                }
            }
        }

        // Extract diet info from suitableForDiet
        if (isset($recipe['suitableForDiet'])) {
            $diets = is_array($recipe['suitableForDiet']) ? $recipe['suitableForDiet'] : [$recipe['suitableForDiet']];
            foreach ($diets as $diet) {
                // Handle both string and structured diet data
                $dietName   = is_array($diet) ? ($diet['name'] ?? $diet) : $diet;
                $normalized = TagHelper::normalizeTag($dietName, 'recipe_diet');
                if ($normalized) {
                    $tags['recipe_diet'][] = $normalized;
                }
            }
        }

        // Extract keywords
        if (isset($recipe['keywords'])) {
            if (is_string($recipe['keywords'])) {
                $keywords = array_map('trim', explode(',', $recipe['keywords']));
            } elseif (is_array($recipe['keywords'])) {
                $keywords = $recipe['keywords'];
            } else {
                $keywords = [];
            }

            foreach ($keywords as $keyword) {
                $normalized = TagHelper::normalizeTag($keyword, 'recipe_keyword');
                if ($normalized) {
                    $tags['recipe_keyword'][] = $normalized;
                }
            }
        }

        // Remove duplicates from each category
        foreach ($tags as $type => $tagList) {
            $tags[$type] = array_values(array_unique($tagList));
        }

        return $tags;
    }

    private static function extractImageUrl(array $data): ?string
    {
        $image = $data['image'] ?? null;

        // If 'image' is a string or an object with a 'url' property
        if (is_string($image) && self::validateImageUrl($image)) {
            return $image;
        } elseif (is_array($image) && isset($image['url']) && is_string($image['url'])) {
            return self::validateImageUrl($image['url']);
        }

        // If 'image' is an array of objects
        if (is_array($image)) {
            foreach ($image as $imgItem) {
                if (is_array($imgItem) && isset($imgItem['url'])) {
                    $url = self::validateImageUrl($imgItem['url']);
                    if ($url) {
                        return $url;
                    }
                } elseif (is_string($imgItem)) {
                    $url = self::validateImageUrl($imgItem);
                    if ($url) {
                        return $url;
                    }
                }
            }
        }

        return null;
    }

    private static function validateImageUrl(string $url): ?string
    {
        $url = trim($url);
        if (empty($url) || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        // Basic check for image extensions
        $path      = parse_url($url, PHP_URL_PATH);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']) || empty($extension)) {
            return $url;
        }

        return null;
    }

    private static function fetchImageData(string $imageUrl): ?array
    {
        $response = Http::timeout(10)->get($imageUrl);

        if ($response->successful()) {
            $size    = strlen($response->body());
            $maxSize = 5 * 1024 * 1024; // 5MB

            if ($size > $maxSize) {
                Log::warning('Image too large, skipping', ['url' => $imageUrl, 'size' => $size]);

                return null;
            }

            return [
                'body' => $response->body(),
                'mime' => $response->header('Content-Type'),
            ];
        }

        return null;
    }
}
