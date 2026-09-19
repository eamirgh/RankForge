<?php

namespace RankForge\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RankForge\Facades\RankForge;
use RankForge\Sitemap\SitemapManager;
use RankForge\Sitemap\SitemapUrl;
use RankForge\Tests\TestCase;

class Phase6HttpAndCommandsTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('rankforge.site_name', 'Test App');
        $app['config']->set('rankforge.title.default', 'Default Title');
        $app['config']->set('rankforge.description.default', 'Default Description');
        $app['config']->set('rankforge.robots_txt.enabled', true);
        $app['config']->set('rankforge.robots_txt.dynamic', true);
        $app['config']->set('rankforge.sitemap.enabled', true);
        $app['config']->set('rankforge.llms_txt.enabled', true);
    }

    public function test_robots_txt_route(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/plain', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('User-agent:', $response->getContent());
    }

    public function test_sitemap_routes(): void
    {
        $sitemapManager = app(SitemapManager::class);
        $sitemapManager->register('articles', function () {
            return [
                SitemapUrl::make('https://example.com/articles/1'),
                SitemapUrl::make('https://example.com/articles/2'),
            ];
        });

        // Index
        $indexResponse = $this->get('/sitemap.xml');
        $indexResponse->assertStatus(200);
        $this->assertStringContainsString('application/xml', $indexResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('<sitemapindex', $indexResponse->getContent());
        $this->assertStringContainsString('sitemap-articles-1.xml', $indexResponse->getContent());

        // Section show
        $sectionResponse = $this->get('/sitemap-articles-1.xml');
        $sectionResponse->assertStatus(200);
        $this->assertStringContainsString('application/xml', $sectionResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('<urlset', $sectionResponse->getContent());
        $this->assertStringContainsString('https://example.com/articles/1', $sectionResponse->getContent());
    }

    public function test_llms_txt_routes(): void
    {
        RankForge::llmsTxt()
            ->title('Test App Docs')
            ->description('Testing LLMs route')
            ->addSection('Quickstart', 'Getting started', [
                ['title' => 'Start', 'url' => 'https://example.com/start'],
            ])
            ->addDocument('Doc 1', '<h1>Hello</h1><p>World</p>');

        $response = $this->get('/llms.txt');
        $response->assertStatus(200);
        $this->assertStringContainsString('text/plain', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('# Test App Docs', $response->getContent());
        $this->assertStringContainsString('- [Start](https://example.com/start)', $response->getContent());

        $fullResponse = $this->get('/llms-full.txt');
        $fullResponse->assertStatus(200);
        $this->assertStringContainsString('text/plain', $fullResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('## Doc 1', $fullResponse->getContent());
        $this->assertStringContainsString('World', $fullResponse->getContent());
    }

    public function test_install_command(): void
    {
        $this->artisan('rankforge:install')
            ->expectsOutputToContain('Publishing RankForge configuration...')
            ->expectsOutputToContain('RankForge installed successfully.')
            ->assertSuccessful();
    }

    public function test_generate_sitemap_command(): void
    {
        Storage::fake('public');

        RankForge::sitemap()->register('posts', [
            'https://example.com/posts/1',
        ]);

        $this->artisan('rankforge:sitemap:generate', ['--disk' => 'public', '--path' => 'sitemaps'])
            ->expectsOutputToContain('Generating sitemaps to disk [public]')
            ->expectsOutputToContain('Sitemap generation complete')
            ->assertSuccessful();

        Storage::disk('public')->assertExists('sitemaps/sitemap.xml');
        Storage::disk('public')->assertExists('sitemaps/sitemap-posts-1.xml');
    }

    public function test_ping_sitemap_command(): void
    {
        Http::fake([
            'https://www.bing.com/*' => Http::response('OK', 200),
            'https://api.indexnow.org/*' => Http::response(['message' => 'OK'], 200),
        ]);

        config()->set('rankforge.index_now.key', '12345678abcdef90');

        $this->artisan('rankforge:sitemap:ping', ['--url' => 'https://example.com/sitemap.xml'])
            ->expectsOutputToContain('Pinging search engines with URL')
            ->expectsOutputToContain('Bing sitemap ping: SUCCESS')
            ->expectsOutputToContain('IndexNow submission: SUCCESS')
            ->assertSuccessful();
    }

    public function test_generate_llms_command(): void
    {
        $this->artisan('rankforge:llms:generate')
            ->expectsOutputToContain('Generating llms.txt and llms-full.txt...')
            ->expectsOutputToContain('LLMs crawler files generated successfully.')
            ->assertSuccessful();
    }

    public function test_health_check_command(): void
    {
        $this->artisan('rankforge:check')
            ->expectsOutputToContain('Running RankForge SEO Health Check...')
            ->expectsOutputToContain('RankForge SEO health check passed.')
            ->assertSuccessful();
    }

    public function test_health_check_command_failure(): void
    {
        config()->set('rankforge.site_name', '');

        $this->artisan('rankforge:check')
            ->expectsOutputToContain('RankForge SEO health check found critical issues.')
            ->assertFailed();
    }
}
