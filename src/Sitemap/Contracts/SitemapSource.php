<?php

namespace Eamirgh\RankForge\Sitemap\Contracts;

interface SitemapSource
{
    /**
     * Return an iterable of SitemapUrl instances, arrays, or model objects.
     *
     * @return iterable<mixed>
     */
    public function toSitemap(): iterable;
}
