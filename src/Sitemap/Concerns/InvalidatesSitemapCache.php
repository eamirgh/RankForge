<?php

namespace Eamirgh\RankForge\Sitemap\Concerns;

use Eamirgh\RankForge\Sitemap\Observers\SitemapObserver;

trait InvalidatesSitemapCache
{
    public static function bootInvalidatesSitemapCache(): void
    {
        static::observe(SitemapObserver::class);
    }
}
