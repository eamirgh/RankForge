<?php

namespace Eamirgh\RankForge\Tests\Feature\Commands;

use Illuminate\Support\Facades\Http;
use Eamirgh\RankForge\Tests\TestCase;

class PingSitemapCommandTest extends TestCase
{
    public function test_it_pings_search_engines_with_sitemap_url(): void
    {
        Http::fake([
            'https://www.bing.com/*' => Http::response('OK', 200),
            'https://api.indexnow.org/*' => Http::response(['message' => 'OK'], 200),
        ]);

        config()->set('rankforge.index_now.key', '12345678abcdef90');

        $this->artisan('rankforge:sitemap:ping', ['--url' => 'https://example.com/sitemap.xml'])
            ->expectsOutputToContain('Pinging search engines with URL')
            ->expectsOutputToContain('Bing sitemap ping: SUCCESS')
            ->expectsOutputToContain('IndexNow submission: SUCCESS')
            ->assertSuccessful();
    }
}
