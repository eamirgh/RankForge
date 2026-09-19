<?php

namespace Eamirgh\RankForge\Tests\Unit\Sitemap;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Eamirgh\RankForge\Facades\RankForge;
use Eamirgh\RankForge\Sitemap\Contracts\SitemapSource;
use Eamirgh\RankForge\Sitemap\SitemapManager;
use Eamirgh\RankForge\Sitemap\SitemapUrl;
use Eamirgh\RankForge\Tests\TestCase;

class SitemapManagerTest extends TestCase
{
    public function test_it_renders_index_and_paginated_sections(): void
    {
        $manager = new SitemapManager([
            'sitemap' => [
                'enabled' => true,
                'max_urls' => 2,
                'cache_ttl' => 0,
            ],
        ]);

        $manager->register('posts', function () {
            return [
                SitemapUrl::make('https://example.com/post-1'),
                SitemapUrl::make('https://example.com/post-2'),
                SitemapUrl::make('https://example.com/post-3'),
            ];
        });

        // 3 items with max_urls 2 -> 2 pages
        $indexXml = $manager->renderIndex();
        $this->assertStringContainsString('sitemap-posts-1.xml', $indexXml);
        $this->assertStringContainsString('sitemap-posts-2.xml', $indexXml);

        // Render page 1 (2 items)
        $section1Xml = $manager->renderSection('posts', 1);
        $this->assertStringContainsString('<loc>https://example.com/post-1</loc>', $section1Xml);
        $this->assertStringContainsString('<loc>https://example.com/post-2</loc>', $section1Xml);
        $this->assertStringNotContainsString('<loc>https://example.com/post-3</loc>', $section1Xml);

        // Render page 2 (1 item)
        $section2Xml = $manager->renderSection('posts', 2);
        $this->assertStringContainsString('<loc>https://example.com/post-3</loc>', $section2Xml);
        $this->assertStringNotContainsString('<loc>https://example.com/post-1</loc>', $section2Xml);
    }

    public function test_it_handles_custom_sitemap_source_contract(): void
    {
        $source = new class implements SitemapSource {
            public function toSitemap(): iterable
            {
                return [
                    ['loc' => 'https://example.com/custom-1', 'priority' => 0.9],
                    ['loc' => 'https://example.com/custom-2', 'priority' => 0.7],
                ];
            }
        };

        $manager = new SitemapManager([
            'sitemap' => ['cache_ttl' => 0, 'max_urls' => 10],
        ]);

        $manager->register('custom', $source);

        $sectionXml = $manager->renderSection('custom', 1);
        $this->assertStringContainsString('<loc>https://example.com/custom-1</loc>', $sectionXml);
        $this->assertStringContainsString('<priority>0.9</priority>', $sectionXml);
        $this->assertStringContainsString('<loc>https://example.com/custom-2</loc>', $sectionXml);
        $this->assertStringContainsString('<priority>0.7</priority>', $sectionXml);
    }

    public function test_it_handles_caching_and_cache_clearing(): void
    {
        Cache::flush();

        $manager = new SitemapManager([
            'sitemap' => [
                'enabled' => true,
                'max_urls' => 10,
                'cache_ttl' => 60,
            ],
        ]);

        $manager->register('pages', [
            'https://example.com/about',
            'https://example.com/contact',
        ]);

        $manager->renderIndex();
        $this->assertTrue(Cache::has('rankforge.sitemap.index'));

        $manager->clearCache();
        $this->assertFalse(Cache::has('rankforge.sitemap.index'));
    }

    public function test_it_writes_sitemaps_to_disk(): void
    {
        Storage::fake('public');

        $manager = new SitemapManager([
            'sitemap' => [
                'enabled' => true,
                'max_urls' => 10,
                'cache_ttl' => 0,
                'disk' => 'public',
                'path' => 'sitemaps',
            ],
        ]);

        $manager->register('pages', [
            'https://example.com/about',
            'https://example.com/contact',
        ]);

        $written = $manager->writeToDisk('public', 'sitemaps');

        $this->assertContains('sitemaps/sitemap.xml', $written);
        $this->assertContains('sitemaps/sitemap-pages-1.xml', $written);

        Storage::disk('public')->assertExists('sitemaps/sitemap.xml');
        Storage::disk('public')->assertExists('sitemaps/sitemap-pages-1.xml');
    }

    public function test_it_accesses_sitemap_via_facade(): void
    {
        $this->assertInstanceOf(SitemapManager::class, RankForge::sitemap());
    }
}
