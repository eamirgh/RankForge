<?php

namespace RankForge\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use RankForge\Sitemap\SitemapManager;

class SitemapController extends Controller
{
    public function __construct(
        protected SitemapManager $sitemapManager
    ) {}

    public function index(): Response
    {
        $xml = $this->sitemapManager->renderIndex();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    public function show(string $section, int $page = 1): Response
    {
        $xml = $this->sitemapManager->renderSection($section, (int) $page);

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
