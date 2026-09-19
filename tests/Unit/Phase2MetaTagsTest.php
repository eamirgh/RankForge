<?php

namespace Eamirgh\RankForge\Tests\Unit;

use DateTimeImmutable;
use Eamirgh\RankForge\Facades\RankForge;
use Eamirgh\RankForge\RankForgeManager;
use Eamirgh\RankForge\Schema\Types\Article;
use Eamirgh\RankForge\Tests\TestCase;

class Phase2MetaTagsTest extends TestCase
{
    public function test_robots_directives(): void
    {
        $manager = new RankForgeManager([
            'robots' => ['default' => 'index, follow'],
        ]);

        $manager->noarchive()
            ->nosnippet()
            ->maxSnippet(150)
            ->maxImagePreview('large')
            ->maxVideoPreview(30)
            ->customRobots('googlebot', 'noindex, follow');

        $robots = $manager->getRobots();

        $this->assertStringContainsString('noarchive', $robots);
        $this->assertStringContainsString('max-snippet:150', $robots);
        $this->assertStringContainsString('max-image-preview:large', $robots);
        $this->assertStringContainsString('max-video-preview:30', $robots);

        $custom = $manager->getCustomRobots();
        $this->assertArrayHasKey('googlebot', $custom);
        $this->assertEquals('noindex, follow', $custom['googlebot']);

        $html = $manager->renderHead();
        $this->assertStringContainsString('<meta name="robots" content="', $html);
        $this->assertStringContainsString('<meta name="googlebot" content="noindex, follow">', $html);
    }

    public function test_open_graph_image_and_article_metadata(): void
    {
        $manager = new RankForgeManager([
            'open_graph' => ['enabled' => true],
        ]);

        $date = new DateTimeImmutable('2025-01-15T12:00:00Z');

        $manager->ogImage('https://example.com/img.jpg', 1200, 630, 'An example image', 'image/jpeg', 'https://secure.example.com/img.jpg')
            ->ogLocaleAlternate(['fr_FR', 'es_ES'])
            ->articlePublishedTime($date)
            ->articleModifiedTime('2025-01-16T12:00:00Z')
            ->articleAuthor(['Alice', 'Bob'])
            ->articleSection('Technology')
            ->articleTags(['php', 'laravel']);

        $og = $manager->getOpenGraph();
        $this->assertEquals('https://example.com/img.jpg', $og['image']);
        $this->assertEquals(1200, $og['image_width']);
        $this->assertEquals(630, $og['image_height']);
        $this->assertEquals('An example image', $og['image_alt']);
        $this->assertEquals('image/jpeg', $og['image_type']);
        $this->assertEquals('https://secure.example.com/img.jpg', $og['image_secure_url']);
        $this->assertEquals(['fr_FR', 'es_ES'], $og['locale_alternate']);

        $html = $manager->renderHead();
        $this->assertStringContainsString('<meta property="og:image" content="https://example.com/img.jpg">', $html);
        $this->assertStringContainsString('<meta property="og:image:width" content="1200">', $html);
        $this->assertStringContainsString('<meta property="og:image:height" content="630">', $html);
        $this->assertStringContainsString('<meta property="og:image:alt" content="An example image">', $html);
        $this->assertStringContainsString('<meta property="og:image:type" content="image/jpeg">', $html);
        $this->assertStringContainsString('<meta property="og:image:secure_url" content="https://secure.example.com/img.jpg">', $html);
        $this->assertStringContainsString('<meta property="og:locale:alternate" content="fr_FR">', $html);
        $this->assertStringContainsString('<meta property="og:locale:alternate" content="es_ES">', $html);
        $this->assertStringContainsString('<meta property="article:published_time" content="2025-01-15T12:00:00+00:00">', $html);
        $this->assertStringContainsString('<meta property="article:modified_time" content="2025-01-16T12:00:00Z">', $html);
        $this->assertStringContainsString('<meta property="article:author" content="Alice">', $html);
        $this->assertStringContainsString('<meta property="article:author" content="Bob">', $html);
        $this->assertStringContainsString('<meta property="article:section" content="Technology">', $html);
        $this->assertStringContainsString('<meta property="article:tag" content="php">', $html);
        $this->assertStringContainsString('<meta property="article:tag" content="laravel">', $html);
    }

    public function test_twitter_card(): void
    {
        $manager = new RankForgeManager([
            'twitter' => ['enabled' => true],
        ]);

        $manager->twitterCard('summary')
            ->twitterTitle('Custom Tweet Title')
            ->twitterDescription('Custom tweet description')
            ->twitterImage('https://example.com/tw.jpg', 'Twitter Alt')
            ->twitterSite('@mysite')
            ->twitterCreator('@creator');

        $html = $manager->renderHead();
        $this->assertStringContainsString('<meta name="twitter:card" content="summary">', $html);
        $this->assertStringContainsString('<meta name="twitter:title" content="Custom Tweet Title">', $html);
        $this->assertStringContainsString('<meta name="twitter:description" content="Custom tweet description">', $html);
        $this->assertStringContainsString('<meta name="twitter:image" content="https://example.com/tw.jpg">', $html);
        $this->assertStringContainsString('<meta name="twitter:image:alt" content="Twitter Alt">', $html);
        $this->assertStringContainsString('<meta name="twitter:site" content="@mysite">', $html);
        $this->assertStringContainsString('<meta name="twitter:creator" content="@creator">', $html);
    }

    public function test_hreflang_tags(): void
    {
        $manager = new RankForgeManager();

        $manager->xDefault('https://example.com')
            ->hreflang('en', 'https://example.com/en')
            ->hreflangs([
                'de' => 'https://example.com/de',
                'fr' => 'https://example.com/fr',
            ]);

        $entries = $manager->getHreflangEntries();
        $this->assertEquals('https://example.com', $entries['x-default']);
        $this->assertEquals('https://example.com/en', $entries['en']);
        $this->assertEquals('https://example.com/de', $entries['de']);
        $this->assertEquals('https://example.com/fr', $entries['fr']);

        $html = $manager->renderHead();
        $this->assertStringContainsString('<link rel="alternate" hreflang="x-default" href="https://example.com">', $html);
        $this->assertStringContainsString('<link rel="alternate" hreflang="en" href="https://example.com/en">', $html);
        $this->assertStringContainsString('<link rel="alternate" hreflang="de" href="https://example.com/de">', $html);
        $this->assertStringContainsString('<link rel="alternate" hreflang="fr" href="https://example.com/fr">', $html);
    }

    public function test_for_model_integration_and_image_cascading(): void
    {
        $model = new class {
            public string $title = 'Model Title';
            public string $description = 'Model Description';
            public string $featured_image = 'https://example.com/featured.jpg';

            public function toJsonLd(): Article
            {
                return (new Article())
                    ->headline($this->title)
                    ->description($this->description);
            }
        };

        $manager = new RankForgeManager([
            'site_name' => 'My Site',
            'open_graph' => ['image' => 'https://example.com/default-og.jpg'],
        ]);

        $manager->forModel($model);

        $this->assertStringContainsString('Model Title', $manager->getRenderedTitle());
        $this->assertEquals('Model Description', $manager->getDescription());

        // Cascading: Model image overrides config default
        $og = $manager->getOpenGraph();
        $this->assertEquals('https://example.com/featured.jpg', $og['image']);

        // Cascading: View-specific image overrides model image
        $manager->ogImage('https://example.com/view-specific.jpg');
        $ogUpdated = $manager->getOpenGraph();
        $this->assertEquals('https://example.com/view-specific.jpg', $ogUpdated['image']);

        // JSON-LD schema added from model
        $this->assertCount(1, $manager->getJsonLdSchemas());
    }
}
