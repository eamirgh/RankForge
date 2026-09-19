<?php

namespace Eamirgh\RankForge\Sitemap\Observers;

use Eamirgh\RankForge\Facades\RankForge;
use Eamirgh\RankForge\Sitemap\SitemapUrl;
use Illuminate\Database\Eloquent\Model;

class SitemapObserver
{
    public function saved(Model $model): void
    {
        if (function_exists('app') && app()->bound('rankforge.sitemap')) {
            app('rankforge.sitemap')->clearCache();
        } else {
            RankForge::sitemap()->clearCache();
        }

        $url = null;
        if (method_exists($model, 'getSitemapUrl')) {
            $url = $model->getSitemapUrl();
        } elseif (method_exists($model, 'toSitemapUrl')) {
            $url = $model->toSitemapUrl();
        }

        if ($url instanceof SitemapUrl) {
            $url = $url->getLoc();
        }

        if (is_string($url) && $url !== '' && config('rankforge.index_now.enabled')) {
            RankForge::indexNow()->submit($url);
        }
    }

    public function deleted(Model $model): void
    {
        if (function_exists('app') && app()->bound('rankforge.sitemap')) {
            app('rankforge.sitemap')->clearCache();
        } else {
            RankForge::sitemap()->clearCache();
        }
    }
}
