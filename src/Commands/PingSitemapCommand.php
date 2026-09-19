<?php

namespace Eamirgh\RankForge\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Eamirgh\RankForge\Sitemap\IndexNow;

class PingSitemapCommand extends Command
{
    protected $signature = 'rankforge:sitemap:ping {--url= : URL to submit to search engines / IndexNow}';

    protected $aliases = ['rankforge:ping'];

    protected $description = 'Ping search engines and dispatch IndexNow notifications';

    public function handle(IndexNow $indexNow): int
    {
        $url = $this->option('url') ?: (function_exists('url') ? url('sitemap.xml') : 'http://localhost/sitemap.xml');

        $this->info("Pinging search engines with URL: {$url}");

        try {
            $bingPing = Http::timeout(5)->get('https://www.bing.com/ping?sitemap='.urlencode($url));
            if ($bingPing->successful()) {
                $this->info('Bing sitemap ping: SUCCESS');
            } else {
                $this->warn("Bing sitemap ping returned status: {$bingPing->status()}");
            }
        } catch (\Throwable $e) {
            $this->warn('Bing sitemap ping failed: '.$e->getMessage());
        }

        $key = config('rankforge.index_now.key');
        if (! empty($key)) {
            $this->info('Submitting to IndexNow...');
            $submitted = $indexNow->submit($url);
            if ($submitted) {
                $this->info('IndexNow submission: SUCCESS');
            } else {
                $this->warn('IndexNow submission failed.');
            }
        } else {
            $this->line('IndexNow key not configured, skipping IndexNow dispatch.');
        }

        return self::SUCCESS;
    }
}
