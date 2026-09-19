<?php

namespace Eamirgh\RankForge\Tests\Unit\Sitemap;

use Eamirgh\RankForge\Sitemap\Concerns\InvalidatesSitemapCache;
use Eamirgh\RankForge\Sitemap\IndexNow;
use Eamirgh\RankForge\Sitemap\Observers\SitemapObserver;
use Eamirgh\RankForge\Sitemap\SitemapManager;
use Eamirgh\RankForge\Sitemap\SitemapUrl;
use Eamirgh\RankForge\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Mockery;

class TestObserverModel extends Model
{
    use InvalidatesSitemapCache;

    protected $guarded = [];

    public function getSitemapUrl(): string
    {
        return 'https://example.com/posts/1';
    }
}

class TestObserverModelWithToSitemapUrl extends Model
{
    protected $guarded = [];

    public function toSitemapUrl(): SitemapUrl
    {
        return SitemapUrl::make('https://example.com/posts/2');
    }
}

class SitemapObserverTest extends TestCase
{
    public function test_it_clears_sitemap_cache_on_saved(): void
    {
        $sitemapMock = Mockery::mock(SitemapManager::class);
        $sitemapMock->shouldReceive('clearCache')->once();
        $this->app->instance('rankforge.sitemap', $sitemapMock);

        $observer = new SitemapObserver();
        $model = new TestObserverModel();

        $observer->saved($model);
    }

    public function test_it_submits_to_index_now_on_saved_when_enabled(): void
    {
        config()->set('rankforge.index_now.enabled', true);

        $sitemapMock = Mockery::mock(SitemapManager::class);
        $sitemapMock->shouldReceive('clearCache')->once();
        $this->app->instance('rankforge.sitemap', $sitemapMock);

        $indexNowMock = Mockery::mock(IndexNow::class);
        $indexNowMock->shouldReceive('submit')->with('https://example.com/posts/1')->once()->andReturn(true);
        $this->app->instance(IndexNow::class, $indexNowMock);
        $this->app->alias(IndexNow::class, 'rankforge.indexnow');

        $observer = new SitemapObserver();
        $model = new TestObserverModel();

        $observer->saved($model);
    }

    public function test_it_supports_to_sitemap_url_returning_sitemap_url_object(): void
    {
        config()->set('rankforge.index_now.enabled', true);

        $sitemapMock = Mockery::mock(SitemapManager::class);
        $sitemapMock->shouldReceive('clearCache')->once();
        $this->app->instance('rankforge.sitemap', $sitemapMock);

        $indexNowMock = Mockery::mock(IndexNow::class);
        $indexNowMock->shouldReceive('submit')->with('https://example.com/posts/2')->once()->andReturn(true);
        $this->app->instance(IndexNow::class, $indexNowMock);
        $this->app->alias(IndexNow::class, 'rankforge.indexnow');

        $observer = new SitemapObserver();
        $model = new TestObserverModelWithToSitemapUrl();

        $observer->saved($model);
    }

    public function test_it_does_not_submit_to_index_now_when_disabled(): void
    {
        config()->set('rankforge.index_now.enabled', false);

        $sitemapMock = Mockery::mock(SitemapManager::class);
        $sitemapMock->shouldReceive('clearCache')->once();
        $this->app->instance('rankforge.sitemap', $sitemapMock);

        $indexNowMock = Mockery::mock(IndexNow::class);
        $indexNowMock->shouldNotReceive('submit');
        $this->app->instance(IndexNow::class, $indexNowMock);

        $observer = new SitemapObserver();
        $model = new TestObserverModel();

        $observer->saved($model);
    }

    public function test_it_clears_sitemap_cache_on_deleted(): void
    {
        $sitemapMock = Mockery::mock(SitemapManager::class);
        $sitemapMock->shouldReceive('clearCache')->once();
        $this->app->instance('rankforge.sitemap', $sitemapMock);

        $observer = new SitemapObserver();
        $model = new TestObserverModel();

        $observer->deleted($model);
    }

    public function test_invalidates_sitemap_cache_trait_registers_observer(): void
    {
        new TestObserverModel();
        $dispatcher = Model::getEventDispatcher();
        $this->assertNotNull($dispatcher);

        $this->assertTrue($dispatcher->hasListeners('eloquent.saved: '.TestObserverModel::class));
        $this->assertTrue($dispatcher->hasListeners('eloquent.deleted: '.TestObserverModel::class));
    }
}
