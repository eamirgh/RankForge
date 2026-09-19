<?php

namespace RankForge\Support;

class Sanitizer
{
    /**
     * Strip HTML tags, normalize whitespace, and optionally truncate.
     *
     * Truncation is word-boundary aware — it will not cut in the middle
     * of a word unless a single word exceeds the max length.
     */
    public static function text(string $value, int $maxLength = 0): string
    {
        $value = strip_tags($value);

        // Decode HTML entities so &amp; becomes &, etc.
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Collapse all whitespace (including newlines) into single spaces
        $value = preg_replace('/\s+/u', ' ', $value);
        $value = trim($value);

        if ($maxLength > 0 && mb_strlen($value, 'UTF-8') > $maxLength) {
            $value = self::truncateAtWord($value, $maxLength);
        }

        return $value;
    }

    /**
     * Sanitize and validate a URL string.
     *
     * Returns the URL with whitespace trimmed and basic validation.
     * Invalid URLs are returned as empty string.
     */
    public static function url(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return '';
        }

        $filtered = filter_var($url, FILTER_SANITIZE_URL);

        if ($filtered === false) {
            return '';
        }

        // Validate the URL structure
        if (! filter_var($filtered, FILTER_VALIDATE_URL)) {
            // Allow protocol-relative URLs
            if (str_starts_with($filtered, '//')) {
                if (! filter_var('https:'.$filtered, FILTER_VALIDATE_URL)) {
                    return '';
                }

                return $filtered;
            }

            // Allow relative paths starting with /
            if (str_starts_with($filtered, '/')) {
                return $filtered;
            }

            return '';
        }

        return $filtered;
    }

    /**
     * Normalize keywords into a comma-separated, deduplicated string.
     *
     * Accepts either an array of keywords or a comma-separated string.
     * Empty and duplicate entries are removed.
     *
     * @param  array<string>|string  $keywords
     */
    public static function keywords(array|string $keywords): string
    {
        if (is_string($keywords)) {
            $keywords = explode(',', $keywords);
        }

        $normalized = [];

        foreach ($keywords as $keyword) {
            $keyword = self::text((string) $keyword);

            if ($keyword !== '') {
                $normalized[] = mb_strtolower($keyword, 'UTF-8');
            }
        }

        // Deduplicate while preserving order
        $normalized = array_values(array_unique($normalized));

        return implode(', ', $normalized);
    }

    /**
     * Remove specific query parameters from a URL.
     *
     * Useful for stripping tracking parameters (UTM, fbclid, gclid) from
     * canonical URLs to prevent duplicate content issues.
     *
     * @param  string[]  $params  Parameter names to strip.
     */
    public static function stripQueryParams(string $url, array $params): string
    {
        if ($params === []) {
            return $url;
        }

        $parsed = parse_url($url);

        if ($parsed === false || ! isset($parsed['query'])) {
            return $url;
        }

        parse_str($parsed['query'], $queryArray);

        foreach ($params as $param) {
            unset($queryArray[$param]);
        }

        // Rebuild the URL
        $scheme = isset($parsed['scheme']) ? $parsed['scheme'].'://' : '';
        $host = $parsed['host'] ?? '';
        $port = isset($parsed['port']) ? ':'.$parsed['port'] : '';
        $path = $parsed['path'] ?? '';
        $query = $queryArray !== [] ? '?'.http_build_query($queryArray) : '';
        $fragment = isset($parsed['fragment']) ? '#'.$parsed['fragment'] : '';

        return $scheme.$host.$port.$path.$query.$fragment;
    }

    /**
     * Escape a string for safe use inside an HTML attribute.
     *
     * Uses htmlspecialchars with ENT_QUOTES to escape &, ", ', <, >.
     */
    public static function escapeAttribute(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
    }

    /**
     * Truncate a string at the nearest word boundary.
     *
     * Will not exceed $maxLength characters. If the first word alone
     * exceeds the limit, it is hard-cut at $maxLength.
     */
    protected static function truncateAtWord(string $value, int $maxLength): string
    {
        if (mb_strlen($value, 'UTF-8') <= $maxLength) {
            return $value;
        }

        // Cut to max length
        $truncated = mb_substr($value, 0, $maxLength, 'UTF-8');

        // Find the last space within the truncated string
        $lastSpace = mb_strrpos($truncated, ' ', 0, 'UTF-8');

        if ($lastSpace !== false && $lastSpace > 0) {
            $truncated = mb_substr($truncated, 0, $lastSpace, 'UTF-8');
        }

        return rtrim($truncated);
    }
}
