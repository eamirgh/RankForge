<?php

namespace Eamirgh\RankForge\Tests\Unit\Sitemap;

use DateTimeImmutable;
use InvalidArgumentException;
use Eamirgh\RankForge\Sitemap\SitemapUrl;
use Eamirgh\RankForge\Tests\TestCase;

class SitemapUrlTest extends TestCase
{
    public function test_it_builds_sitemap_url_with_all_properties_and_xml(): void
    {
        $date = new DateTimeImmutable('2026-01-15T10:30:00+00:00');

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
        $this->assertStringContainsString('<lastmod>2026-01-15T10:30:00+00:00</lastmod>', $xml);
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

    public function test_it_validates_changefreq(): void
    {
        $url = SitemapUrl::make('https://example.com');

        $this->expectException(InvalidArgumentException::class);
        $url->changefreq('invalid_freq');
    }

    public function test_it_validates_priority(): void
    {
        $url = SitemapUrl::make('https://example.com');

        $this->expectException(InvalidArgumentException::class);
        $url->priority(1.5);
    }

    public function test_it_serializes_to_json(): void
    {
        $url = SitemapUrl::make('https://example.com/item')
            ->changefreq('weekly')
            ->priority(0.5);

        $json = $url->toJson();
        $this->assertJson($json);
        $this->assertStringContainsString('https://example.com/item', $json);
        $this->assertStringContainsString('weekly', $json);
    }
}
