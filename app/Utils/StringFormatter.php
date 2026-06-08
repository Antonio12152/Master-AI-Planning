<?php

namespace App\Utils;

class StringFormatter
{
    /**
     * Slugify a string (convert to URL-friendly format)
     */
    public static function slugify(string $text): string
    {
        $slug = strtolower(
            preg_replace('/[^a-z0-9]+/i', '-', trim($text)) ?? ''
        );
        return trim($slug, '-');
    }

    /**
     * Truncate a string to a specific length
     */
    public static function truncate(string $text, int $length = 100, string $ending = '...'): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length - strlen($ending)) . $ending;
    }

    /**
     * Capitalize first letter of each word
     */
    public static function titleCase(string $text): string
    {
        return ucwords(strtolower($text));
    }

    /**
     * Check if string contains profanity (simple check)
     */
    public static function isProfane(string $text): bool
    {
        $badWords = ['badword1', 'badword2', 'spam'];
        
        foreach ($badWords as $word) {
            if (stripos($text, $word) !== false) {
                return true;
            }
        }

        return false;
    }
}
