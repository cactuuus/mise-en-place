<?php

namespace App\Helpers;

class TagHelper
{
    /**
     * Given a collection of tags, groups tags by their type and returns an associative array.
     * - Optionally filters by specific types and locale for translations.
     * - If no types are provided, all types are included.
     */
    public static function groupTagsByType($tags, array $types = null, string $locale = 'en'): array
    {
        $result = [];

        foreach ($tags as $tag) {
            $tagType = $tag->type;

            // Skip if we're filtering by specific types and this isn't one of them
            if ($types && ! in_array($tagType, $types)) {
                continue;
            }

            if ( ! isset($result[$tagType])) {
                $result[$tagType] = [];
            }

            $result[$tagType][] = $tag->getTranslation('name', $locale);
        }

        return $result;
    }

    /**
     * Normalizes a tag by trimming whitespace, converting to lowercase, and handling special cases.
     * - If the tag is empty or too short, returns null.
     * - For diet types, applies specific normalization rules.
     */
    public static function normalizeTag(string $tag, string $type = null): ?string
    {
        $tag = strtolower(strip_tags(trim($tag)));

        if (empty($tag) || strlen($tag) <= 1) {
            return null;
        }

        // Special handling for diet types
        if ($type === 'recipe_diet') {
            return self::normalizeDietTag($tag);
        }

        return $tag;
    }

    /** Normalizes diet tags by mapping known variations to standard forms.
     * - Handles schema.org URLs by extracting the diet name.
     * - Maps various diet names to consistent formats.
     */
    private static function normalizeDietTag(string $diet): string
    {
        // Handle schema.org URLs
        if (strpos($diet, 'schema.org/') !== false) {
            // Extract the diet name from URL like "https://schema.org/GlutenFreeDiet"
            $diet = basename(parse_url($diet, PHP_URL_PATH));
        }

        $dietMap = [
            'lowfatdiet'          => 'low fat',
            'lowlactosediet'      => 'low lactose',
            'lowsaltdiet'         => 'low salt',
            'lowsodiumdiet'       => 'low sodium',
            'glutenfreediet'      => 'gluten free',
            'diabeticdiet'        => 'diabetic',
            'halaldiet'           => 'halal',
            'hinduvegetariandiet' => 'hindu vegetarian',
            'kosherdiet'          => 'kosher',
            'vegandiet'           => 'vegan',
            'vegetariandiet'      => 'vegetarian',
        ];

        $normalized = strtolower(str_replace(' ', '', $diet));

        return $dietMap[$normalized] ?? $diet;
    }
}
