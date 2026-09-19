<?php

namespace Eamirgh\RankForge\Tests\Feature\Http;

use Eamirgh\RankForge\Sitemap\SitemapManager;
use Eamirgh\RankForge\Sitemap\SitemapUrl;
use Eamirgh\RankForge\Tests\TestCase;

class SitemapRouteTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('rankforge.sitemap.enabled', true);
    }

    public function test_it_serves_sitemap_index_route(): void
    {
        $sitemapManager = app(SitemapManager::class);
        $sitemapManager->register('articles', function () {
            return [
                SitemapUrl::make('https://example.com/articles/1'),
                SitemapUrl::make('https://example.com/articles/2'),
            ];
        });

        $indexResponse = $this->get('/sitemap.xml');
        $indexResponse->assertStatus(200);
        $this->assertStringContainsString('application/xml', $indexResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('<sitemapindex', $indexResponse->getContent());
        $this->assertStringContainsString('sitemap-articles-1.xml', $indexResponse->getContent());
    }

    public function test_it_serves_sitemap_section_route(): void
    {
        $sitemapManager = app(SitemapManager::class);
        $sitemapManager->register('articles', function () {
            return [
                SitemapUrl::make('https://example.com/articles/1'),
            ];
        });

        $sectionResponse = $this->get('/sitemap-articles-1.xml');
        $sectionResponse->assertStatus(200);
        $this->assertStringContainsString('application/xml', $sectionResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('<urlset', $sectionResponse->getContent());
        $this->assertStringContainsString('https://example.com/articles/1', $sectionResponse->getContent());
    }
}
