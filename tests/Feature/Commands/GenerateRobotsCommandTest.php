<?php

namespace Eamirgh\RankForge\Tests\Feature\Commands;

use Eamirgh\RankForge\Crawlers\RobotsTxtManager;
use Eamirgh\RankForge\Tests\TestCase;
use Mockery;

class GenerateRobotsCommandTest extends TestCase
{
    public function test_it_generates_robots_txt_file_with_default_path(): void
    {
        $robotsMock = Mockery::mock(RobotsTxtManager::class);
        $robotsMock->shouldReceive('writeToDisk')->with(null)->once()->andReturn(true);
        $this->app->instance('rankforge.robots', $robotsMock);

        $this->artisan('rankforge:robots:generate')
            ->expectsOutputToContain('Generating robots.txt file to disk...')
            ->expectsOutputToContain('Robots.txt successfully generated')
            ->assertSuccessful();
    }

    public function test_it_generates_robots_txt_file_with_custom_path(): void
    {
        $customPath = sys_get_temp_dir().'/custom_robots.txt';

        $robotsMock = Mockery::mock(RobotsTxtManager::class);
        $robotsMock->shouldReceive('writeToDisk')->with($customPath)->once()->andReturn(true);
        $this->app->instance('rankforge.robots', $robotsMock);

        $this->artisan('rankforge:robots:generate', ['--path' => $customPath])
            ->expectsOutputToContain('Generating robots.txt file to disk...')
            ->expectsOutputToContain("Robots.txt successfully generated at [{$customPath}].")
            ->assertSuccessful();
    }

    public function test_it_works_via_alias(): void
    {
        $robotsMock = Mockery::mock(RobotsTxtManager::class);
        $robotsMock->shouldReceive('writeToDisk')->with(null)->once()->andReturn(true);
        $this->app->instance('rankforge.robots', $robotsMock);

        $this->artisan('rankforge:robots')
            ->assertSuccessful();
    }

    public function test_it_handles_failure_to_write(): void
    {
        $robotsMock = Mockery::mock(RobotsTxtManager::class);
        $robotsMock->shouldReceive('writeToDisk')->with(null)->once()->andReturn(false);
        $this->app->instance('rankforge.robots', $robotsMock);

        $this->artisan('rankforge:robots:generate')
            ->expectsOutputToContain('Failed to generate robots.txt file.')
            ->assertFailed();
    }
}
