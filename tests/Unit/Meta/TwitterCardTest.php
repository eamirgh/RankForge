<?php

namespace Eamirgh\RankForge\Tests\Unit\Meta;

use Eamirgh\RankForge\RankForgeManager;
use Eamirgh\RankForge\Tests\TestCase;

class TwitterCardTest extends TestCase
{
    public function test_it_sets_twitter_card_properties(): void
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

    public function test_it_falls_back_to_open_graph_values(): void
    {
        $manager = new RankForgeManager([
            'twitter' => ['enabled' => true],
            'open_graph' => ['enabled' => true],
        ]);

        $manager->ogTitle('OG Fallback Title')
            ->ogDescription('OG Fallback Description')
            ->ogImage('https://example.com/og-image.jpg', alt: 'OG Alt');

        $tw = $manager->getTwitter();
        $this->assertEquals('OG Fallback Title', $tw['title']);
        $this->assertEquals('OG Fallback Description', $tw['description']);
        $this->assertEquals('https://example.com/og-image.jpg', $tw['image']);
        $this->assertEquals('OG Alt', $tw['image:alt']);
    }

    public function test_it_falls_back_to_page_title_and_description(): void
    {
        $manager = new RankForgeManager([
            'twitter' => ['enabled' => true],
            'title' => ['default' => 'Default Site Title'],
            'description' => ['default' => 'Default Site Description'],
        ]);

        $tw = $manager->getTwitter();
        $this->assertEquals('Default Site Title', $tw['title']);
        $this->assertEquals('Default Site Description', $tw['description']);
    }

    public function test_it_does_not_render_twitter_tags_when_disabled(): void
    {
        $manager = new RankForgeManager([
            'twitter' => ['enabled' => false],
        ]);

        $manager->twitterTitle('Not rendered');

        $html = $manager->renderHead();
        $this->assertStringNotContainsString('twitter:title', $html);
    }
}
