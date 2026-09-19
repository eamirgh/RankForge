<?php

namespace Eamirgh\RankForge\Sitemap\Concerns;

use Eamirgh\RankForge\Sitemap\Observers\SitemapObserver;
use Illuminate\Database\Eloquent\Model;

trait InvalidatesSitemapCache
{
    public static function bootInvalidatesSitemapCache(): void
    {
        static::saved(function (Model $model): void {
            (new SitemapObserver())->saved($model);
        });

        static::deleted(function (Model $model): void {
            (new SitemapObserver())->deleted($model);
        });
    }
}
