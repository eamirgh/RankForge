<?php

namespace Eamirgh\RankForge\Tests\Unit\Crawlers;

use Eamirgh\RankForge\Crawlers\RobotsTxtManager;
use Eamirgh\RankForge\Facades\RankForge;
use Eamirgh\RankForge\Tests\TestCase;

class RobotsTxtManagerTest extends TestCase
{
    public function test_it_renders_disallow_all_in_non_production_environments(): void
    {
        $manager = new RobotsTxtManager([
            'robots' => ['noindex_environments' => ['testing', 'local']],
        ]);

        $rendered = $manager->render();

        $this->assertEquals("User-agent: *\nDisallow: /\n", $rendered);
    }

    public function test_it_renders_production_robots_txt_with_user_agents_and_ai_crawlers(): void
    {
        $manager = new RobotsTxtManager([
            'robots_txt' => [
                'production' => [
                    'user_agents' => [
                        '*' => [
                            'allow' => ['/'],
                            'disallow' => ['/admin', '/api'],
                        ],
                        'Googlebot' => [
                            'allow' => ['/'],
                            'disallow' => [],
                        ],
                    ],
                    'ai_crawlers' => [
                        'GPTBot' => [
                            'disallow' => ['/'],
                        ],
                        'ClaudeBot' => [
                            'disallow' => ['/'],
                        ],
                    ],
                ],
                'crawl_delay' => 10,
                'host' => 'example.com',
                'sitemaps' => ['https://example.com/sitemap.xml'],
            ],
            'sitemap' => ['enabled' => true],
        ]);

        $manager->forceProduction(true);
        $rendered = $manager->render();

        $this->assertStringContainsString("User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /api", $rendered);
        $this->assertStringContainsString("User-agent: Googlebot\nAllow: /", $rendered);
        $this->assertStringContainsString("User-agent: GPTBot\nDisallow: /", $rendered);
        $this->assertStringContainsString("User-agent: ClaudeBot\nDisallow: /", $rendered);
        $this->assertStringContainsString('Crawl-delay: 10', $rendered);
        $this->assertStringContainsString('Host: example.com', $rendered);
        $this->assertStringContainsString('Sitemap: https://example.com/sitemap.xml', $rendered);
    }

    public function test_it_configures_robots_txt_fluently_and_writes_to_disk(): void
    {
        $tempFile = sys_get_temp_dir().'/robots-test-'.uniqid().'.txt';

        $manager = new RobotsTxtManager([]);
        $manager->forceProduction(true)
            ->allow('/public')
            ->disallow('/secret')
            ->sitemap('https://example.com/custom-sitemap.xml')
            ->crawlDelay(5)
            ->host('custom.example.com');

        $rendered = $manager->render();

        $this->assertStringContainsString('Allow: /public', $rendered);
        $this->assertStringContainsString('Disallow: /secret', $rendered);
        $this->assertStringContainsString('Sitemap: https://example.com/custom-sitemap.xml', $rendered);
        $this->assertStringContainsString('Crawl-delay: 5', $rendered);
        $this->assertStringContainsString('Host: custom.example.com', $rendered);

        $written = $manager->writeToDisk($tempFile);
        $this->assertTrue($written);
        $this->assertFileExists($tempFile);
        $this->assertEquals($rendered, file_get_contents($tempFile));

        @unlink($tempFile);
    }

    public function test_it_accesses_robots_txt_via_facade(): void
    {
        $this->assertInstanceOf(RobotsTxtManager::class, RankForge::robotsTxt());
    }
}
