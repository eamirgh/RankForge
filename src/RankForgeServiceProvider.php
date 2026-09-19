<?php

namespace Eamirgh\RankForge;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Eamirgh\RankForge\Commands\GenerateLlmsCommand;
use Eamirgh\RankForge\Commands\GenerateSitemapCommand;
use Eamirgh\RankForge\Commands\HealthCheckCommand;
use Eamirgh\RankForge\Commands\InstallCommand;
use Eamirgh\RankForge\Commands\PingSitemapCommand;
use Eamirgh\RankForge\Crawlers\LlmsTxtManager;
use Eamirgh\RankForge\Crawlers\RobotsTxtManager;
use Eamirgh\RankForge\Sitemap\IndexNow;
use Eamirgh\RankForge\Sitemap\SitemapManager;

class RankForgeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/rankforge.php', 'rankforge');

        $this->app->singleton(RankForgeManager::class, function ($app) {
            return new RankForgeManager($app['config']->get('rankforge', []));
        });
        $this->app->alias(RankForgeManager::class, 'rankforge');

        $this->app->singleton(SitemapManager::class, function ($app) {
            return new SitemapManager($app['config']->get('rankforge', []));
        });
        $this->app->alias(SitemapManager::class, 'rankforge.sitemap');

        $this->app->singleton(RobotsTxtManager::class, function ($app) {
            return new RobotsTxtManager($app['config']->get('rankforge', []));
        });
        $this->app->alias(RobotsTxtManager::class, 'rankforge.robots');

        $this->app->singleton(LlmsTxtManager::class, function ($app) {
            return new LlmsTxtManager($app['config']->get('rankforge', []));
        });
        $this->app->alias(LlmsTxtManager::class, 'rankforge.llms');

        $this->app->singleton(IndexNow::class, function ($app) {
            return new IndexNow($app['config']->get('rankforge', []));
        });
        $this->app->alias(IndexNow::class, 'rankforge.indexnow');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/rankforge.php' => config_path('rankforge.php'),
        ], 'rankforge-config');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'rankforge');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/rankforge'),
        ], 'rankforge-views');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        Blade::directive('rankforgeHead', function () {
            return "<?php echo app('rankforge')->renderHead(); ?>";
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                GenerateSitemapCommand::class,
                PingSitemapCommand::class,
                GenerateLlmsCommand::class,
                HealthCheckCommand::class,
            ]);
        }
    }
}
