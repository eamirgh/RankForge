<?php

namespace Eamirgh\RankForge\Tests\Unit;

use DateTimeImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Eamirgh\RankForge\Facades\RankForge;
use Eamirgh\RankForge\Sitemap\Contracts\SitemapSource;
use Eamirgh\RankForge\Sitemap\IndexNow;
use Eamirgh\RankForge\Sitemap\SitemapIndex;
use Eamirgh\RankForge\Sitemap\SitemapManager;
use Eamirgh\RankForge\Sitemap\SitemapUrl;
use Eamirgh\RankForge\Tests\TestCase;

class Phase4SitemapTest extends TestCase
{
    public function test_sitemap_url_fluent_building_and_xml(): void
    {
        $date = new DateTimeImmutable('2025-01-15T10:30:00+00:00');

        $url = SitemapUrl::make('https://example.com/posts/hello-world')
            ->lastmod($date)
            ->changefreq('daily')
            ->priority(0.8)
            ->image('https://example.com/images/hero.jpg', 'Hero Title', 'Hero Caption')
            ->video('https://example.com/thumb.jpg', 'Intro Video', 'Video description', 'https://example.com/video.mp4')
            ->news('The Daily News', 'en', $date, 'Big News Today')
            ->alternate('es', 'https://example.com/es/posts/hello-world');

        $xml = $url->toXml();

        $this->assertStringContainsString('<loc>https://example.com/posts/hello-world</loc>', $xml);
        $this->assertStringContainsString('<lastmod>2025-01-15T10:30:00+00:00</lastmod>', $xml);
        $this->assertStringContainsString('<changefreq>daily</changefreq>', $xml);
        $this->assertStringContainsString('<priority>0.8</priority>', $xml);
        $this->assertStringContainsString('<image:loc>https://example.com/images/hero.jpg</image:loc>', $xml);
        $this->assertStringContainsString('<image:title>Hero Title</image:title>', $xml);
        $this->assertStringContainsString('<video:thumbnail_loc>https://example.com/thumb.jpg</video:thumbnail_loc>', $xml);
        $this->assertStringContainsString('<video:content_loc>https://example.com/video.mp4</video:content_loc>', $xml);
        $this->assertStringContainsString('<news:name>The Daily News</news:name>', $xml);
        $this->assertStringContainsString('<news:title>Big News Today</news:title>', $xml);
        $this->assertStringContainsString('hreflang="es"', $xml);

        $array = $url->toArray();
        $this->assertEquals('https://example.com/posts/hello-world', $array['loc']);
        $this->assertEquals('daily', $array['changefreq']);
        $this->assertEquals(0.8, $array['priority']);
    }

    public function test_sitemap_url_validation(): void
    {
        $url = SitemapUrl::make('https://example.com');

        $this->expectException(InvalidArgumentException::class);
        $url->changefreq('invalid_freq');
    }

    public function test_sitemap_url_priority_validation(): void
    {
        $url = SitemapUrl::make('https://example.com');

        $this->expectException(InvalidArgumentException::class);
        $url->priority(1.5);
    }

    public function test_sitemap_index_xml_generation(): void
    {
        $index = SitemapIndex::make()
            ->addSitemap('https://example.com/sitemap-posts-1.xml', new DateTimeImmutable('2025-01-01T00:00:00+00:00'))
            ->addSitemap('https://example.com/sitemap-pages-1.xml');

        $xml = $index->toXml();

        $this->assertStringContainsString('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $xml);
        $this->assertStringContainsString('<loc>https://example.com/sitemap-posts-1.xml</loc>', $xml);
        $this->assertStringContainsString('<lastmod>2025-01-01T00:00:00+00:00</lastmod>', $xml);
        $this->assertStringContainsString('<loc>https://example.com/sitemap-pages-1.xml</loc>', $xml);
    }

    public function test_sitemap_manager_sources_and_rendering(): void
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

    public function test_sitemap_manager_with_custom_sitemap_source(): void
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

    public function test_sitemap_manager_caching_and_disk_writing(): void
    {
        Storage::fake('public');
        Cache::flush();

        $manager = new SitemapManager([
            'sitemap' => [
                'enabled' => true,
                'max_urls' => 10,
                'cache_ttl' => 60,
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

        // Test cache hit & clear
        $manager->renderIndex();
        $this->assertTrue(Cache::has('rankforge.sitemap.index'));
        $manager->clearCache();
        $this->assertFalse(Cache::has('rankforge.sitemap.index'));
    }

    public function test_index_now_submission(): void
    {
        Http::fake([
            'https://api.indexnow.org/indexnow' => Http::response(['message' => 'OK'], 200),
        ]);

        $indexNow = new IndexNow([
            'index_now' => [
                'key' => '12345678abcdef90',
                'engine' => 'https://api.indexnow.org/indexnow',
            ],
        ]);

        $this->assertTrue($indexNow->isValidKey('12345678abcdef90'));
        $this->assertFalse($indexNow->isValidKey('short'));
        $this->assertFalse($indexNow->isValidKey('invalid key with spaces!'));

        $result = $indexNow->submit([
            'https://example.com/post-1',
            'https://example.com/post-2',
        ]);

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            $data = $request->data();

            return $data['host'] === 'example.com'
                && $data['key'] === '12345678abcdef90'
                && count($data['urlList']) === 2;
        });
    }

    public function test_index_now_error_handling(): void
    {
        Http::fake([
            'https://api.indexnow.org/indexnow' => Http::response(['message' => 'Forbidden'], 403),
        ]);

        $indexNow = new IndexNow([
            'index_now' => [
                'key' => '12345678abcdef90',
            ],
        ]);

        // Empty URLs
        $this->assertFalse($indexNow->submit([]));

        // Invalid key
        $this->assertFalse($indexNow->submit('https://example.com/test', 'bad_key'));

        // HTTP failure
        $this->assertFalse($indexNow->submit('https://example.com/test'));
    }

    public function test_sitemap_url_serialization(): void
    {
        $url = SitemapUrl::make('https://example.com/item')
            ->changefreq('weekly')
            ->priority(0.5);

        $json = $url->toJson();
        $this->assertJson($json);
        $this->assertStringContainsString('https://example.com/item', $json);
        $this->assertStringContainsString('weekly', $json);
    }

    public function test_facade_access_to_sitemap_and_index_now(): void
    {
        $this->assertInstanceOf(SitemapManager::class, RankForge::sitemap());
        $this->assertInstanceOf(IndexNow::class, RankForge::indexNow());
    }
}
