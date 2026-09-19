<?php

namespace Eamirgh\RankForge\Tests\Feature\Commands;

use Illuminate\Support\Facades\Storage;
use Eamirgh\RankForge\Facades\RankForge;
use Eamirgh\RankForge\Tests\TestCase;

class GenerateSitemapCommandTest extends TestCase
{
    public function test_it_generates_sitemaps_to_disk(): void
    {
        Storage::fake('public');

        RankForge::sitemap()->register('posts', [
            'https://example.com/posts/1',
        ]);

        $this->artisan('rankforge:sitemap:generate', ['--disk' => 'public', '--path' => 'sitemaps'])
            ->expectsOutputToContain('Generating sitemaps to disk [public]')
            ->expectsOutputToContain('Sitemap generation complete')
            ->assertSuccessful();

        Storage::disk('public')->assertExists('sitemaps/sitemap.xml');
        Storage::disk('public')->assertExists('sitemaps/sitemap-posts-1.xml');
    }
}
