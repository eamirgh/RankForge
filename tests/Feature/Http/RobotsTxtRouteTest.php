<?php

namespace Eamirgh\RankForge\Tests\Feature\Http;

use Eamirgh\RankForge\Tests\TestCase;

class RobotsTxtRouteTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('rankforge.robots_txt.enabled', true);
        $app['config']->set('rankforge.robots_txt.dynamic', true);
    }

    public function test_it_serves_robots_txt_route(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/plain', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('User-agent:', $response->getContent());
    }
}
