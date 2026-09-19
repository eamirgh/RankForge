<?php

namespace Eamirgh\RankForge\Tests\Unit\Sitemap;

use Illuminate\Support\Facades\Http;
use Eamirgh\RankForge\Facades\RankForge;
use Eamirgh\RankForge\Sitemap\IndexNow;
use Eamirgh\RankForge\Tests\TestCase;

class IndexNowTest extends TestCase
{
    public function test_it_validates_index_now_key(): void
    {
        $indexNow = new IndexNow([
            'index_now' => [
                'key' => '12345678abcdef90',
            ],
        ]);

        $this->assertTrue($indexNow->isValidKey('12345678abcdef90'));
        $this->assertFalse($indexNow->isValidKey('short'));
        $this->assertFalse($indexNow->isValidKey('invalid key with spaces!'));
    }

    public function test_it_submits_urls_successfully(): void
    {
        Http::fake([
            'https://api.indexnow.org/indexnow' => Http::response(['message' => 'OK'], 200),
        ]);

        $indexNow = new IndexNow([
            'index_now' => [
                'key' => '12345678abcdef90',
                'engine' => 'https://api.indexnow.org/indexnow',
            ],
        ]);

        $result = $indexNow->submit([
            'https://example.com/post-1',
            'https://example.com/post-2',
        ]);

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            $data = $request->data();

            return $data['host'] === 'example.com'
                && $data['key'] === '12345678abcdef90'
                && count($data['urlList']) === 2;
        });
    }

    public function test_it_handles_submission_errors(): void
    {
        Http::fake([
            'https://api.indexnow.org/indexnow' => Http::response(['message' => 'Forbidden'], 403),
        ]);

        $indexNow = new IndexNow([
            'index_now' => [
                'key' => '12345678abcdef90',
            ],
        ]);

        // Empty URLs
        $this->assertFalse($indexNow->submit([]));

        // Invalid key
        $this->assertFalse($indexNow->submit('https://example.com/test', 'bad_key'));

        // HTTP failure
        $this->assertFalse($indexNow->submit('https://example.com/test'));
    }

    public function test_it_accesses_index_now_via_facade(): void
    {
        $this->assertInstanceOf(IndexNow::class, RankForge::indexNow());
    }
}
