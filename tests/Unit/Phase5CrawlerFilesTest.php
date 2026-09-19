<?php

namespace Eamirgh\RankForge\Tests\Unit;

use Eamirgh\RankForge\Crawlers\LlmsTxtManager;
use Eamirgh\RankForge\Crawlers\RobotsTxtManager;
use Eamirgh\RankForge\Crawlers\Transformers\ContentTransformer;
use Eamirgh\RankForge\Facades\RankForge;
use Eamirgh\RankForge\Tests\TestCase;

class Phase5CrawlerFilesTest extends TestCase
{
    public function test_content_transformer_to_markdown(): void
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

        $plainText = ContentTransformer::toPlainText($html);
        $this->assertStringNotContainsString('#', $plainText);
        $this->assertStringNotContainsString('**', $plainText);
        $this->assertStringContainsString('Article Title', $plainText);
        $this->assertStringContainsString('Wise words from someone.', $plainText);
    }

    public function test_robots_txt_non_production_environment(): void
    {
        $manager = new RobotsTxtManager([
            'robots' => ['noindex_environments' => ['testing', 'local']],
        ]);

        $rendered = $manager->render();

        $this->assertEquals("User-agent: *\nDisallow: /\n", $rendered);
    }

    public function test_robots_txt_production_rendering(): void
    {
        $manager = new RobotsTxtManager([
            'robots_txt' => [
                'production' => [
                    'user_agents' => [
                        '*' => [
                            'allow' => ['/'],
                            'disallow' => ['/admin', '/api'],
                        ],
                        'Googlebot' => [
                            'allow' => ['/'],
                            'disallow' => [],
                        ],
                    ],
                    'ai_crawlers' => [
                        'GPTBot' => [
                            'disallow' => ['/'],
                        ],
                        'ClaudeBot' => [
                            'disallow' => ['/'],
                        ],
                    ],
                ],
                'crawl_delay' => 10,
                'host' => 'example.com',
                'sitemaps' => ['https://example.com/sitemap.xml'],
            ],
            'sitemap' => ['enabled' => true],
        ]);

        $manager->forceProduction(true);
        $rendered = $manager->render();

        $this->assertStringContainsString("User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /api", $rendered);
        $this->assertStringContainsString("User-agent: Googlebot\nAllow: /", $rendered);
        $this->assertStringContainsString("User-agent: GPTBot\nDisallow: /", $rendered);
        $this->assertStringContainsString("User-agent: ClaudeBot\nDisallow: /", $rendered);
        $this->assertStringContainsString('Crawl-delay: 10', $rendered);
        $this->assertStringContainsString('Host: example.com', $rendered);
        $this->assertStringContainsString('Sitemap: https://example.com/sitemap.xml', $rendered);
    }

    public function test_robots_txt_fluent_configuration_and_writing(): void
    {
        $tempFile = sys_get_temp_dir().'/robots-test-'.uniqid().'.txt';

        $manager = new RobotsTxtManager([]);
        $manager->forceProduction(true)
            ->allow('/public')
            ->disallow('/secret')
            ->sitemap('https://example.com/custom-sitemap.xml')
            ->crawlDelay(5)
            ->host('custom.example.com');

        $rendered = $manager->render();

        $this->assertStringContainsString('Allow: /public', $rendered);
        $this->assertStringContainsString('Disallow: /secret', $rendered);
        $this->assertStringContainsString('Sitemap: https://example.com/custom-sitemap.xml', $rendered);
        $this->assertStringContainsString('Crawl-delay: 5', $rendered);
        $this->assertStringContainsString('Host: custom.example.com', $rendered);

        $written = $manager->writeToDisk($tempFile);
        $this->assertTrue($written);
        $this->assertFileExists($tempFile);
        $this->assertEquals($rendered, file_get_contents($tempFile));

        @unlink($tempFile);
    }

    public function test_llms_txt_rendering(): void
    {
        $manager = new LlmsTxtManager([
            'site_name' => 'RankForge Docs',
            'llms_txt' => [
                'title' => 'RankForge',
                'description' => 'Enterprise SEO and GEO Engine for Laravel',
            ],
        ]);

        $manager->addSection('Core Links', 'Essential pages', [
            ['title' => 'Overview', 'url' => 'https://example.com/docs', 'description' => 'Introduction to features'],
            ['title' => 'Installation', 'url' => 'https://example.com/install'],
        ]);

        $manager->addSection('Optional', '', [
            ['title' => 'API Reference', 'url' => 'https://example.com/api', 'description' => 'Complete endpoints'],
        ]);

        $rendered = $manager->render();

        $this->assertStringContainsString('# RankForge', $rendered);
        $this->assertStringContainsString('> Enterprise SEO and GEO Engine for Laravel', $rendered);
        $this->assertStringContainsString('## Core Links', $rendered);
        $this->assertStringContainsString('Essential pages', $rendered);
        $this->assertStringContainsString('- [Overview](https://example.com/docs): Introduction to features', $rendered);
        $this->assertStringContainsString('- [Installation](https://example.com/install)', $rendered);
        $this->assertStringContainsString('## Optional', $rendered);
        $this->assertStringContainsString('- [API Reference](https://example.com/api): Complete endpoints', $rendered);
    }

    public function test_llms_full_txt_rendering_and_writing(): void
    {
        $tempDir = sys_get_temp_dir().'/llms-test-'.uniqid();
        mkdir($tempDir, 0755, true);

        $manager = new LlmsTxtManager([
            'llms_txt' => [
                'title' => 'My Project',
                'description' => 'Project Description',
                'strip_html' => true,
            ],
        ]);

        $manager->addDocument('Guide', '<h1>Guide</h1><p>Welcome to <strong>RankForge</strong>.</p>', 'https://example.com/guide');

        $full = $manager->renderFull();

        $this->assertStringContainsString('# My Project', $full);
        $this->assertStringContainsString('> Project Description', $full);
        $this->assertStringContainsString('## Guide', $full);
        $this->assertStringContainsString('Source: https://example.com/guide', $full);
        $this->assertStringContainsString('Welcome to **RankForge**.', $full);

        $written = $manager->writeToDisk($tempDir);

        $this->assertFileExists($written['llms.txt']);
        $this->assertFileExists($written['llms-full.txt']);

        @unlink($written['llms.txt']);
        @unlink($written['llms-full.txt']);
        @rmdir($tempDir);
    }

    public function test_facade_access_to_crawlers(): void
    {
        $this->assertInstanceOf(RobotsTxtManager::class, RankForge::robotsTxt());
        $this->assertInstanceOf(LlmsTxtManager::class, RankForge::llmsTxt());
    }
}
