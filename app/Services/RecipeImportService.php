<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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
                'has_image'         => isset($recipeData['image_file']),
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
        $data = [
            'title'        => self::extractTitle($recipe),
            'ingredients'  => self::extractIngredients($recipe),
            'instructions' => self::extractInstructions($recipe),
            'prep_time'    => self::extractTime($recipe, 'prepTime'),
            'cook_time'    => self::extractTime($recipe, 'cookTime'),
            'serves'       => self::extractServings($recipe),
            'tags'         => self::extractTags($recipe),
        ];

        // Extract and process image
        $imageUrl = self::extractImageUrl($recipe);
        if ($imageUrl) {
            try {
                $imageFile          = self::createTemporaryFileUploadFromUrl($imageUrl);
                $data['image_file'] = $imageFile;
            } catch (Exception $e) {
                Log::warning('Failed to import recipe image', [
                    'image_url' => $imageUrl,
                    'error'     => $e->getMessage(),
                ]);
                // Continue without image rather than failing the entire import
            }
        }

        return $data;
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

    /**
     * Extract image URL from recipe structured data
     */
    private static function extractImageUrl(array $recipe): ?string
    {
        if (isset($recipe['image'])) {
            $image = $recipe['image'];

            if (is_string($image)) {
                return self::validateImageUrl($image);
            }

            // Handle array of images (take the first one)
            if (is_array($image) && ! empty($image)) {
                $firstImage = $image[0];

                // If it's an object with url property
                if (is_array($firstImage) && isset($firstImage['url'])) {
                    return self::validateImageUrl($firstImage['url']);
                }

                // If it's a direct URL string
                if (is_string($firstImage)) {
                    return self::validateImageUrl($firstImage);
                }
            }
        }

        // Check for photo field (alternative naming)
        if (isset($recipe['photo']) && is_string($recipe['photo'])) {
            return self::validateImageUrl($recipe['photo']);
        }

        return null;
    }

    /**
     * Validate and clean image URL
     */
    private static function validateImageUrl(string $url): ?string
    {
        $url = trim($url);

        if (empty($url)) {
            return null;
        }

        // Ensure it's a valid URL
        if ( ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        return $url;
    }

    /**
     * Create a temporary file upload from an image URL (similar to the favicon example)
     */
    public static function createTemporaryFileUploadFromUrl(string $imageUrl): string
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ])
                ->get($imageUrl);

            if ( ! $response->successful()) {
                throw new Exception('Failed to fetch image: HTTP '.$response->status());
            }

            $imageContent = $response->body();

            if (empty($imageContent)) {
                throw new Exception('Empty image content received');
            }

            // Validate content type
            $contentType = $response->header('Content-Type');
            if ( ! str_starts_with($contentType, 'image/')) {
                throw new Exception('URL does not point to an image file. Content-Type: '.$contentType);
            }

            // Step 1: Save the file to a temporary location
            $tempFilePath = tempnam(sys_get_temp_dir(), 'recipe_image');
            file_put_contents($tempFilePath, $imageContent);

            // Determine file extension from content type or URL
            $extension = self::getImageExtension($contentType, $imageUrl);
            $filename  = 'recipe_image_'.time().'.'.$extension;

            // Step 2: Create an UploadedFile instance
            $tempFile = new UploadedFile(
                $tempFilePath,
                $filename,
                $contentType,
                null,
                true, // test mode - prevents validation errors
            );

            // Step 3: Store in livewire temp directory using the configured disk
            $disk = config('livewire.temporary_file_upload.disk');
            $path = Storage::disk($disk)->put('livewire-tmp', $tempFile);

            // Step 4: Create a TemporaryUploadedFile instance
            $file = TemporaryUploadedFile::createFromLivewire($path);

            // Step 5: Return temporary signed URL
            return URL::temporarySignedRoute(
                'livewire.preview-file',
                now()->addMinutes(30)->endOfHour(),
                ['filename' => $file->getFilename()],
            );
        } catch (Exception $e) {
            Log::error('Failed to create temporary file upload from URL', [
                'url'   => $imageUrl,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get appropriate file extension from content type or URL
     */
    private static function getImageExtension(string $contentType, string $imageUrl): string
    {
        // Map content types to extensions
        $contentTypeMap = [
            'image/jpeg' => 'jpg',
            'image/jpg'  => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];

        if (isset($contentTypeMap[$contentType])) {
            return $contentTypeMap[$contentType];
        }

        // Fallback to URL extension
        $urlPath   = parse_url($imageUrl, PHP_URL_PATH);
        $extension = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION));

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            return $extension === 'jpeg' ? 'jpg' : $extension;
        }

        // Default fallback
        return 'jpg';
    }
}
