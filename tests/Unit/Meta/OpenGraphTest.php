<?php

namespace Eamirgh\RankForge\Tests\Unit\Meta;

use DateTimeImmutable;
use Eamirgh\RankForge\RankForgeManager;
use Eamirgh\RankForge\Tests\TestCase;

class OpenGraphTest extends TestCase
{
    public function test_it_sets_basic_open_graph_properties(): void
    {
        $manager = new RankForgeManager([
            'open_graph' => ['enabled' => true],
        ]);

        $manager->ogTitle('OG Title')
            ->ogDescription('OG Description')
            ->ogType('website');

        $og = $manager->getOpenGraph();
        $this->assertEquals('OG Title', $og['title']);
        $this->assertEquals('OG Description', $og['description']);
        $this->assertEquals('website', $og['type']);
    }

    public function test_it_sets_open_graph_image_with_full_metadata(): void
    {
        $manager = new RankForgeManager([
            'open_graph' => ['enabled' => true],
        ]);

        $manager->ogImage(
            'https://example.com/img.jpg',
            1200,
            630,
            'An example image',
            'image/jpeg',
            'https://secure.example.com/img.jpg'
        );

        $og = $manager->getOpenGraph();
        $this->assertEquals('https://example.com/img.jpg', $og['image']);
        $this->assertEquals(1200, $og['image_width']);
        $this->assertEquals(630, $og['image_height']);
        $this->assertEquals('An example image', $og['image_alt']);
        $this->assertEquals('image/jpeg', $og['image_type']);
        $this->assertEquals('https://secure.example.com/img.jpg', $og['image_secure_url']);

        $html = $manager->renderHead();
        $this->assertStringContainsString('<meta property="og:image" content="https://example.com/img.jpg">', $html);
        $this->assertStringContainsString('<meta property="og:image:width" content="1200">', $html);
        $this->assertStringContainsString('<meta property="og:image:height" content="630">', $html);
        $this->assertStringContainsString('<meta property="og:image:alt" content="An example image">', $html);
        $this->assertStringContainsString('<meta property="og:image:type" content="image/jpeg">', $html);
        $this->assertStringContainsString('<meta property="og:image:secure_url" content="https://secure.example.com/img.jpg">', $html);
    }

    public function test_it_sets_alternate_locales(): void
    {
        $manager = new RankForgeManager([
            'open_graph' => ['enabled' => true],
        ]);

        $manager->ogLocaleAlternate(['fr_FR', 'es_ES'])
            ->ogLocaleAlternate('de_DE');

        $og = $manager->getOpenGraph();
        $this->assertEquals(['fr_FR', 'es_ES', 'de_DE'], $og['locale_alternate']);

        $html = $manager->renderHead();
        $this->assertStringContainsString('<meta property="og:locale:alternate" content="fr_FR">', $html);
        $this->assertStringContainsString('<meta property="og:locale:alternate" content="es_ES">', $html);
        $this->assertStringContainsString('<meta property="og:locale:alternate" content="de_DE">', $html);
    }

    public function test_it_sets_article_metadata(): void
    {
        $manager = new RankForgeManager([
            'open_graph' => ['enabled' => true],
        ]);

        $date = new DateTimeImmutable('2025-01-15T12:00:00Z');

        $manager->articlePublishedTime($date)
            ->articleModifiedTime('2025-01-16T12:00:00Z')
            ->articleAuthor(['Alice', 'Bob'])
            ->articleSection('Technology')
            ->articleTags(['php', 'laravel']);

        $html = $manager->renderHead();
        $this->assertStringContainsString('<meta property="article:published_time" content="2025-01-15T12:00:00+00:00">', $html);
        $this->assertStringContainsString('<meta property="article:modified_time" content="2025-01-16T12:00:00Z">', $html);
        $this->assertStringContainsString('<meta property="article:author" content="Alice">', $html);
        $this->assertStringContainsString('<meta property="article:author" content="Bob">', $html);
        $this->assertStringContainsString('<meta property="article:section" content="Technology">', $html);
        $this->assertStringContainsString('<meta property="article:tag" content="php">', $html);
        $this->assertStringContainsString('<meta property="article:tag" content="laravel">', $html);
    }

    public function test_it_falls_back_to_page_title_description_canonical_and_site_name(): void
    {
        $manager = new RankForgeManager([
            'site_name' => 'Acme Site',
            'open_graph' => ['enabled' => true],
        ]);

        $manager->title('Page Title')
            ->description('Page Description')
            ->canonical('https://example.com/page');

        $og = $manager->getOpenGraph();
        $this->assertEquals('Page Title', $og['title']);
        $this->assertEquals('Page Description', $og['description']);
        $this->assertEquals('https://example.com/page', $og['url']);
        $this->assertEquals('Acme Site', $og['site_name']);
    }

    public function test_it_does_not_render_open_graph_when_disabled(): void
    {
        $manager = new RankForgeManager([
            'open_graph' => ['enabled' => false],
        ]);

        $manager->ogTitle('Test Title');

        $html = $manager->renderHead();
        $this->assertStringNotContainsString('og:title', $html);
    }
}
