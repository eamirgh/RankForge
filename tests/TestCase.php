<?php

namespace Eamirgh\RankForge\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Eamirgh\RankForge\RankForgeServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            RankForgeServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('rankforge.site_name', 'Test App');
    }
}
