<?php

namespace App\Helpers;

class ErrorMessages
{
    /**
     * Collection of playful cooking-themed error titles
     */
    private static array $errorTitles = [
        // Gordon Ramsay inspired (family-friendly versions)
        'Oh dear, what a mess!',
        'This is a disaster!',
        'What a nightmare!',
        "It's freaking raw... I mean wrong!",
        'Absolutely shocking!',

        // Cooking disasters
        'The soufflé has fallen!',
        'Burnt to a crisp!',
        'Something\'s gone off!',
        'The pot has boiled over!',
        'Oops, we dropped the cake!',
        'Someone forgot to preheat the oven!',

        // Food puns
        'Holy cannoli!',
        'Oh my gourd!',
        'This is bananas!',
        'Something\'s a bit fishy here!',
        'We\'re in a pickle!',
        'That\'s one tough cookie!',
        'Donut panic!',
    ];

    /**
     * Get a specific error title by index (for testing)
     */
    public static function get(int $index): string
    {
        return self::$errorTitles[$index] ?? self::random();
    }

    /**
     * Get a random error title
     */
    public static function random(): string
    {
        return self::$errorTitles[array_rand(self::$errorTitles)];
    }

    /**
     * Get all error titles
     */
    public static function all(): array
    {
        return self::$errorTitles;
    }

    /**
     * Get count of available error titles
     */
    public static function count(): int
    {
        return count(self::$errorTitles);
    }
}
