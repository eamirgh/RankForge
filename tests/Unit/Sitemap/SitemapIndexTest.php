<?php

namespace Eamirgh\RankForge\Tests\Unit\Sitemap;

use DateTimeImmutable;
use Eamirgh\RankForge\Sitemap\SitemapIndex;
use Eamirgh\RankForge\Tests\TestCase;

class SitemapIndexTest extends TestCase
{
    public function test_it_generates_sitemap_index_xml(): void
    {
        $index = SitemapIndex::make()
            ->addSitemap('https://example.com/sitemap-posts-1.xml', new DateTimeImmutable('2026-01-01T00:00:00+00:00'))
            ->addSitemap('https://example.com/sitemap-pages-1.xml');

        $xml = $index->toXml();

        $this->assertStringContainsString('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $xml);
        $this->assertStringContainsString('<loc>https://example.com/sitemap-posts-1.xml</loc>', $xml);
        $this->assertStringContainsString('<lastmod>2026-01-01T00:00:00+00:00</lastmod>', $xml);
        $this->assertStringContainsString('<loc>https://example.com/sitemap-pages-1.xml</loc>', $xml);
    }

    public function test_it_handles_empty_sitemap_index(): void
    {
        $index = SitemapIndex::make();
        $xml = $index->toXml();

        $this->assertStringContainsString('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $xml);
        $this->assertStringContainsString('</sitemapindex>', $xml);
    }
}
