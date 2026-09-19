<?php

namespace Eamirgh\RankForge\Tests\Unit\Meta;

use Eamirgh\RankForge\RankForgeManager;
use Eamirgh\RankForge\Tests\TestCase;

class CanonicalTest extends TestCase
{
    public function test_it_sets_and_retrieves_explicit_canonical_url(): void
    {
        $manager = new RankForgeManager();
        $manager->canonical('https://example.com/blog/my-post');

        $this->assertEquals('https://example.com/blog/my-post', $manager->getCanonicalUrl());
    }

    public function test_it_strips_configured_tracking_parameters_from_canonical_url(): void
    {
        $manager = new RankForgeManager([
            'canonical' => [
                'enabled' => true,
                'strip_query_params' => ['utm_source', 'utm_medium', 'fbclid', 'gclid'],
            ],
        ]);

        $manager->canonical('https://example.com/blog/my-post?utm_source=twitter&keep=1&fbclid=xyz123');

        $this->assertEquals('https://example.com/blog/my-post?keep=1', $manager->getCanonicalUrl());
    }

    public function test_it_enforces_trailing_slash_configuration(): void
    {
        $manager = new RankForgeManager([
            'canonical' => [
                'enabled' => true,
                'trailing_slash' => true,
            ],
        ]);

        $manager->canonical('https://example.com/about');

        $this->assertEquals('https://example.com/about/', $manager->getCanonicalUrl());
    }

    public function test_it_removes_trailing_slash_when_disabled(): void
    {
        $manager = new RankForgeManager([
            'canonical' => [
                'enabled' => true,
                'trailing_slash' => false,
            ],
        ]);

        $manager->canonical('https://example.com/about/');

        $this->assertEquals('https://example.com/about', $manager->getCanonicalUrl());
    }

    public function test_it_returns_null_and_omits_tag_when_canonical_is_disabled(): void
    {
        $manager = new RankForgeManager([
            'canonical' => [
                'enabled' => false,
            ],
        ]);

        $manager->canonical('https://example.com/about');

        $this->assertNull($manager->getCanonicalUrl());
        $this->assertStringNotContainsString('<link rel="canonical"', $manager->renderHead());
    }

    public function test_it_renders_canonical_link_tag(): void
    {
        $manager = new RankForgeManager();
        $manager->canonical('https://example.com/articles/seo-best-practices');

        $html = $manager->renderHead();
        $this->assertStringContainsString('<link rel="canonical" href="https://example.com/articles/seo-best-practices">', $html);
    }
}
