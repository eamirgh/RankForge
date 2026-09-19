<?php

namespace Eamirgh\RankForge\Crawlers\Transformers;

class ContentTransformer
{
    /**
     * Convert HTML content to clean, readable Markdown.
     */
    public static function toMarkdown(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        // Remove scripts, styles, navigation, headers, footers, noscript, svg
        $cleaned = preg_replace('/<(script|style|nav|header|footer|noscript|svg)[^>]*>.*?<\/\1>/is', '', $html) ?? $html;

        // Code blocks <pre><code>...</code></pre> or <pre>...</pre>
        $cleaned = preg_replace_callback('/<pre[^>]*>(?:<code[^>]*>)?(.*?)(?:<\/code>)?<\/pre>/is', function ($matches) {
            $code = html_entity_decode(strip_tags($matches[1]), ENT_QUOTES, 'UTF-8');

            return "\n```\n".trim($code)."\n```\n";
        }, $cleaned) ?? $cleaned;

        // Inline code <code>...</code>
        $cleaned = preg_replace_callback('/<code[^>]*>(.*?)<\/code>/is', function ($matches) {
            return '`'.trim(strip_tags($matches[1])).'`';
        }, $cleaned) ?? $cleaned;

        // Headings <h1> through <h6>
        for ($i = 1; $i <= 6; $i++) {
            $cleaned = preg_replace_callback("/<h{$i}[^>]*>(.*?)<\/h{$i}>/is", function ($matches) use ($i) {
                $prefix = str_repeat('#', $i);
                $text = trim(strip_tags($matches[1]));

                return "\n\n{$prefix} {$text}\n\n";
            }, $cleaned) ?? $cleaned;
        }

        // Links <a href="...">...</a>
        $cleaned = preg_replace_callback('/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', function ($matches) {
            $url = trim($matches[1]);
            $text = trim(strip_tags($matches[2]));

            return $text !== '' ? "[{$text}]({$url})" : $url;
        }, $cleaned) ?? $cleaned;

        // Images <img src="..." alt="...">
        $cleaned = preg_replace_callback('/<img\s+[^>]*src=["\']([^"\']+)["\'][^>]*>/is', function ($matches) {
            $tag = $matches[0];
            $src = $matches[1];
            $alt = '';
            if (preg_match('/alt=["\']([^"\']*)["\']/i', $tag, $altMatch)) {
                $alt = trim($altMatch[1]);
            }

            return "![{$alt}]({$src})";
        }, $cleaned) ?? $cleaned;

        // Blockquotes <blockquote>...</blockquote>
        $cleaned = preg_replace_callback('/<blockquote[^>]*>(.*?)<\/blockquote>/is', function ($matches) {
            $lines = explode("\n", trim(strip_tags($matches[1])));
            $quoted = array_map(fn ($line) => '> '.trim($line), $lines);

            return "\n\n".implode("\n", $quoted)."\n\n";
        }, $cleaned) ?? $cleaned;

        // Bold & Italic
        $cleaned = preg_replace('/<(strong|b)[^>]*>(.*?)<\/\1>/is', '**$2**', $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/<(em|i)[^>]*>(.*?)<\/\1>/is', '*$2*', $cleaned) ?? $cleaned;

        // Unordered lists <li> inside <ul>
        $cleaned = preg_replace_callback('/<li[^>]*>(.*?)<\/li>/is', function ($matches) {
            $text = trim(strip_tags($matches[1]));

            return "- {$text}\n";
        }, $cleaned) ?? $cleaned;

        // Paragraphs and breaks
        $cleaned = preg_replace('/<p[^>]*>(.*?)<\/p>/is', "\n\n$1\n\n", $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/<br\s*\/?>/i', "\n", $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/<hr\s*\/?>/i', "\n\n---\n\n", $cleaned) ?? $cleaned;

        // Strip any remaining HTML tags
        $cleaned = strip_tags($cleaned);

        // Decode entities
        $cleaned = html_entity_decode($cleaned, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Normalize whitespace: collapse 3+ newlines to 2, trim lines
        $cleaned = preg_replace("/[ \t]+$/m", '', $cleaned) ?? $cleaned;
        $cleaned = preg_replace("/\n{3,}/", "\n\n", $cleaned) ?? $cleaned;

        return trim($cleaned);
    }

    /**
     * Convert HTML content to clean plain text.
     */
    public static function toPlainText(string $html): string
    {
        $markdown = static::toMarkdown($html);

        // Remove markdown formatting
        $text = preg_replace('/^#+\s+/m', '', $markdown) ?? $markdown;
        $text = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $text) ?? $text;
        $text = preg_replace('/!\[([^\]]*)\]\([^)]+\)/', '$1', $text) ?? $text;
        $text = preg_replace('/\*\*([^*]+)\*\*/', '$1', $text) ?? $text;
        $text = preg_replace('/\*([^*]+)\*/', '$1', $text) ?? $text;
        $text = preg_replace('/`([^`]+)`/', '$1', $text) ?? $text;
        $text = preg_replace('/^>\s+/m', '', $text) ?? $text;
        $text = preg_replace('/^-\s+/m', '', $text) ?? $text;

        return trim($text);
    }
}
