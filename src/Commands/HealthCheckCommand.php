<?php

namespace Eamirgh\RankForge\Commands;

use Illuminate\Console\Command;
use Eamirgh\RankForge\Schema\Types\WebSite;

class HealthCheckCommand extends Command
{
    protected $signature = 'rankforge:check';

    protected $aliases = ['rankforge:health'];

    protected $description = 'Audit SEO health and configuration completeness';

    public function handle(): int
    {
        $this->info('Running RankForge SEO Health Check...');
        $hasErrors = false;

        $rows = [];

        // 1. Site Name
        $siteName = config('rankforge.site_name');
        if (! empty($siteName) && $siteName !== 'Laravel') {
            $rows[] = ['Site Name', 'OK', $siteName];
        } elseif ($siteName === 'Laravel') {
            $rows[] = ['Site Name', 'WARN', 'Default "Laravel" in use. Configure APP_NAME or rankforge.site_name.'];
        } else {
            $rows[] = ['Site Name', 'FAIL', 'Site name is empty.'];
            $hasErrors = true;
        }

        // 2. Title default
        $titleDefault = config('rankforge.title.default');
        if (! empty($titleDefault)) {
            $rows[] = ['Default Title', 'OK', $titleDefault];
        } else {
            $rows[] = ['Default Title', 'WARN', 'Default page title is empty.'];
        }

        // 3. Description default
        $descDefault = config('rankforge.description.default');
        if (! empty($descDefault)) {
            $rows[] = ['Default Description', 'OK', $descDefault];
        } else {
            $rows[] = ['Default Description', 'WARN', 'Default meta description is empty.'];
        }

        // 4. Robots.txt
        $robotsEnabled = config('rankforge.robots_txt.enabled', true);
        if ($robotsEnabled) {
            $dynamic = config('rankforge.robots_txt.dynamic', true) ? 'yes' : 'no';
            $rows[] = ['Robots.txt', 'OK', "Enabled (dynamic: {$dynamic})"];
        } else {
            $rows[] = ['Robots.txt', 'WARN', 'Robots.txt is disabled in config.'];
        }

        // 5. Sitemaps
        $sitemapEnabled = config('rankforge.sitemap.enabled', true);
        $sources = config('rankforge.sitemap.sources', []);
        if ($sitemapEnabled) {
            $count = count((array) $sources);
            $rows[] = ['Sitemaps', 'OK', "Enabled ({$count} source(s) configured)"];
        } else {
            $rows[] = ['Sitemaps', 'WARN', 'Sitemaps are disabled in config.'];
        }

        // 6. IndexNow
        $indexNowKey = config('rankforge.index_now.key');
        if (! empty($indexNowKey)) {
            $rows[] = ['IndexNow', 'OK', 'API Key is configured'];
        } else {
            $rows[] = ['IndexNow', 'WARN', 'IndexNow key not set (INDEXNOW_KEY env).'];
        }

        // 7. JSON-LD validation
        try {
            $siteUrl = function_exists('url') ? url('/') : 'https://example.com';
            $sample = WebSite::make()->name($siteName ?: 'Site')->url($siteUrl)->toArray();
            if (isset($sample['@context'], $sample['@type'])) {
                $rows[] = ['JSON-LD Engine', 'OK', 'Schema structures validate correctly.'];
            } else {
                $rows[] = ['JSON-LD Engine', 'FAIL', 'Schema generation missing required fields.'];
                $hasErrors = true;
            }
        } catch (\Throwable $e) {
            $rows[] = ['JSON-LD Engine', 'FAIL', $e->getMessage()];
            $hasErrors = true;
        }

        $this->table(['Check', 'Status', 'Details'], $rows);

        if ($hasErrors) {
            $this->error('RankForge SEO health check found critical issues.');

            return self::FAILURE;
        }

        $this->info('RankForge SEO health check passed.');

        return self::SUCCESS;
    }
}
