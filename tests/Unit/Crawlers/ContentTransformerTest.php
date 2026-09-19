<?php

namespace Eamirgh\RankForge\Tests\Unit\Crawlers;

use Eamirgh\RankForge\Crawlers\Transformers\ContentTransformer;
use Eamirgh\RankForge\Tests\TestCase;

class ContentTransformerTest extends TestCase
{
    public function test_it_transforms_html_to_clean_markdown(): void
    {
        $html = <<<'HTML'
        <header><nav><a href="/home">Home</a></nav></header>
        <script>console.log("bad");</script>
        <style>body { color: red; }</style>
        <h1>Article Title</h1>
        <p>This is a paragraph with <strong>bold</strong> and <em>italic</em> text.</p>
        <blockquote>Wise words from someone.</blockquote>
        <ul>
            <li>Item 1</li>
            <li>Item 2</li>
        </ul>
        <p>Visit <a href="https://example.com">Example</a> or check <img src="/logo.png" alt="Company Logo" />.</p>
        <pre><code>echo "hello world";</code></pre>
        <footer><p>&copy; 2025</p></footer>
HTML;

        $markdown = ContentTransformer::toMarkdown($html);

        $this->assertStringNotContainsString('console.log', $markdown);
        $this->assertStringNotContainsString('body { color: red; }', $markdown);
        $this->assertStringNotContainsString('Home', $markdown);
        $this->assertStringNotContainsString('&copy;', $markdown);

        $this->assertStringContainsString('# Article Title', $markdown);
        $this->assertStringContainsString('**bold**', $markdown);
        $this->assertStringContainsString('*italic*', $markdown);
        $this->assertStringContainsString('> Wise words from someone.', $markdown);
        $this->assertStringContainsString('- Item 1', $markdown);
        $this->assertStringContainsString('- Item 2', $markdown);
        $this->assertStringContainsString('[Example](https://example.com)', $markdown);
        $this->assertStringContainsString('![Company Logo](/logo.png)', $markdown);
        $this->assertStringContainsString('```', $markdown);
        $this->assertStringContainsString('echo "hello world";', $markdown);
    }

    public function test_it_transforms_html_to_plain_text(): void
    {
        $html = <<<'HTML'
        <h1>Article Title</h1>
        <p>This is a paragraph with <strong>bold</strong> text.</p>
        <blockquote>Wise words from someone.</blockquote>
HTML;

        $plainText = ContentTransformer::toPlainText($html);

        $this->assertStringNotContainsString('#', $plainText);
        $this->assertStringNotContainsString('**', $plainText);
        $this->assertStringContainsString('Article Title', $plainText);
        $this->assertStringContainsString('Wise words from someone.', $plainText);
    }
}
