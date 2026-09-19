<?php

namespace Eamirgh\RankForge\Tests\Unit\Meta;

use Eamirgh\RankForge\RankForgeManager;
use Eamirgh\RankForge\Tests\TestCase;

class RobotsDirectivesTest extends TestCase
{
    public function test_it_sets_explicit_robots_string(): void
    {
        $manager = new RankForgeManager();
        $manager->robots('noindex, nofollow');

        $this->assertEquals('noindex, nofollow', $manager->getRobots());
    }

    public function test_it_toggles_noindex_and_index_directives(): void
    {
        $manager = new RankForgeManager([
            'robots' => ['default' => 'index, follow'],
        ]);

        $manager->noindex();
        $this->assertStringContainsString('noindex', $manager->getRobots());
        $this->assertStringNotContainsString('index,', $manager->getRobots());

        $manager->index();
        $this->assertStringContainsString('index', $manager->getRobots());
        $this->assertStringNotContainsString('noindex', $manager->getRobots());
    }

    public function test_it_toggles_nofollow_and_follow_directives(): void
    {
        $manager = new RankForgeManager([
            'robots' => ['default' => 'index, follow'],
        ]);

        $manager->nofollow();
        $this->assertStringContainsString('nofollow', $manager->getRobots());
        $this->assertStringNotContainsString('follow,', $manager->getRobots());

        $manager->follow();
        $this->assertStringContainsString('follow', $manager->getRobots());
        $this->assertStringNotContainsString('nofollow', $manager->getRobots());
    }

    public function test_it_handles_noarchive_and_nosnippet_directives(): void
    {
        $manager = new RankForgeManager([
            'robots' => ['default' => 'index, follow'],
        ]);

        $manager->noarchive()->nosnippet();

        $robots = $manager->getRobots();
        $this->assertStringContainsString('noarchive', $robots);
        $this->assertStringContainsString('nosnippet', $robots);
    }

    public function test_it_handles_max_snippet_max_image_preview_and_max_video_preview(): void
    {
        $manager = new RankForgeManager([
            'robots' => ['default' => 'index, follow'],
        ]);

        $manager->maxSnippet(150)
            ->maxImagePreview('large')
            ->maxVideoPreview(30);

        $robots = $manager->getRobots();
        $this->assertStringContainsString('max-snippet:150', $robots);
        $this->assertStringContainsString('max-image-preview:large', $robots);
        $this->assertStringContainsString('max-video-preview:30', $robots);

        // Max snippet removes nosnippet if previously set
        $manager->nosnippet();
        $this->assertStringContainsString('nosnippet', $manager->getRobots());
        $this->assertStringNotContainsString('max-snippet:150', $manager->getRobots());

        $manager->maxSnippet(200);
        $this->assertStringContainsString('max-snippet:200', $manager->getRobots());
        $this->assertStringNotContainsString('nosnippet', $manager->getRobots());
    }

    public function test_it_sets_custom_robots_tags(): void
    {
        $manager = new RankForgeManager();
        $manager->customRobots('googlebot', 'noindex, follow')
            ->customRobots('bingbot', 'index, nofollow');

        $custom = $manager->getCustomRobots();
        $this->assertArrayHasKey('googlebot', $custom);
        $this->assertEquals('noindex, follow', $custom['googlebot']);
        $this->assertArrayHasKey('bingbot', $custom);
        $this->assertEquals('index, nofollow', $custom['bingbot']);
    }

    public function test_it_renders_robots_and_custom_robots_meta_tags(): void
    {
        $manager = new RankForgeManager([
            'robots' => ['default' => 'index, follow'],
        ]);

        $manager->noarchive()
            ->customRobots('googlebot', 'noindex, follow');

        $html = $manager->renderHead();
        $this->assertStringContainsString('<meta name="robots" content="', $html);
        $this->assertStringContainsString('noarchive', $html);
        $this->assertStringContainsString('<meta name="googlebot" content="noindex, follow">', $html);
    }

    public function test_it_applies_noindex_nofollow_in_configured_environments(): void
    {
        $manager = new RankForgeManager([
            'robots' => [
                'default' => 'index, follow',
                'noindex_environments' => ['testing', 'staging'],
            ],
        ]);

        // In orchestra/testbench, app()->environment() is 'testing'
        $this->assertEquals('noindex, nofollow', $manager->getRobots());
    }
}
