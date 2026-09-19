<?php

namespace RankForge\Commands;

use Illuminate\Console\Command;
use RankForge\Sitemap\SitemapManager;

class GenerateSitemapCommand extends Command
{
    protected $signature = 'rankforge:sitemap:generate {--disk= : The storage disk to use} {--path= : The directory path within the disk}';

    protected $aliases = ['rankforge:sitemap'];

    protected $description = 'Generate static XML sitemap files to disk';

    public function handle(SitemapManager $sitemapManager): int
    {
        $disk = $this->option('disk') ?: config('rankforge.sitemap.disk', 'public');
        $path = $this->option('path') !== null ? (string) $this->option('path') : (string) config('rankforge.sitemap.path', 'sitemaps');

        $this->info("Generating sitemaps to disk [{$disk}] at path [{$path}]...");

        $files = $sitemapManager->writeToDisk($disk, $path);

        foreach ($files as $file) {
            $this->line(" - Generated: {$file}");
        }

        $this->info('Sitemap generation complete: '.count($files).' file(s) generated.');

        return self::SUCCESS;
    }
}
