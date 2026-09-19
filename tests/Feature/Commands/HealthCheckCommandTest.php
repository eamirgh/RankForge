<?php

namespace Eamirgh\RankForge\Tests\Feature\Commands;

use Eamirgh\RankForge\Tests\TestCase;

class HealthCheckCommandTest extends TestCase
{
    public function test_it_passes_seo_health_check_when_properly_configured(): void
    {
        $this->artisan('rankforge:check')
            ->expectsOutputToContain('Running RankForge SEO Health Check...')
            ->expectsOutputToContain('RankForge SEO health check passed.')
            ->assertSuccessful();
    }

    public function test_it_fails_seo_health_check_when_site_name_is_missing(): void
    {
        config()->set('rankforge.site_name', '');

        $this->artisan('rankforge:check')
            ->expectsOutputToContain('RankForge SEO health check found critical issues.')
            ->assertFailed();
    }
}
