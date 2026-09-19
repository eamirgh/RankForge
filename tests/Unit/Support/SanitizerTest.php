<?php

namespace Eamirgh\RankForge\Tests\Unit\Support;

use Eamirgh\RankForge\Support\Sanitizer;
use Eamirgh\RankForge\Tests\TestCase;

class SanitizerTest extends TestCase
{
    public function test_it_sanitizes_and_truncates_text_at_word_boundaries(): void
    {
        $raw = "  <p>Hello &amp; <strong>welcome</strong> to the RankForge test.</p>  \n  More text here.  ";
        $sanitized = Sanitizer::text($raw);

        $this->assertEquals('Hello & welcome to the RankForge test. More text here.', $sanitized);

        // Truncate at word boundary
        $truncated = Sanitizer::text('The quick brown fox jumps over the lazy dog', 20);
        $this->assertEquals('The quick brown fox', $truncated);
        $this->assertLessThanOrEqual(20, mb_strlen($truncated));
    }

    public function test_it_sanitizes_and_validates_urls(): void
    {
        $this->assertEquals('https://example.com/path?arg=1', Sanitizer::url('https://example.com/path?arg=1'));
        $this->assertEquals('//example.com/cdn', Sanitizer::url('//example.com/cdn'));
        $this->assertEquals('/relative/path', Sanitizer::url('/relative/path'));
        $this->assertEquals('', Sanitizer::url('javascript:alert(1)'));
        $this->assertEquals('', Sanitizer::url(''));
    }

    public function test_it_normalizes_and_deduplicates_keywords(): void
    {
        $keywords = ['  Laravel ', 'SEO', 'laravel', 'Laravel', 'RANKFORGE'];
        $result = Sanitizer::keywords($keywords);

        $this->assertEquals('laravel, seo, rankforge', $result);

        $stringResult = Sanitizer::keywords('php, laravel, PHP, framework');
        $this->assertEquals('php, laravel, framework', $stringResult);
    }

    public function test_it_strips_query_parameters_from_urls(): void
    {
        $url = 'https://example.com/page?utm_source=twitter&utm_medium=social&category=news&fbclid=123';
        $stripped = Sanitizer::stripQueryParams($url, ['utm_source', 'utm_medium', 'fbclid']);

        $this->assertEquals('https://example.com/page?category=news', $stripped);

        // No params left
        $allStripped = Sanitizer::stripQueryParams('https://example.com/page?utm_source=twitter', ['utm_source']);
        $this->assertEquals('https://example.com/page', $allStripped);
    }

    public function test_it_escapes_attributes_for_html(): void
    {
        $escaped = Sanitizer::escapeAttribute('Title with "quotes" and <tags> & ampersands');
        $this->assertEquals('Title with &quot;quotes&quot; and &lt;tags&gt; &amp; ampersands', $escaped);
    }
}
