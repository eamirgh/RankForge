<?php

namespace Eamirgh\RankForge\Sitemap;

use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class IndexNow
{
    /** @var array<string, mixed> */
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Submit one or more URLs to the IndexNow API.
     *
     * @param string|array<string> $urls
     */
    public function submit(string|array $urls, ?string $key = null, ?string $keyLocation = null): bool
    {
        $urlList = is_array($urls) ? array_values(array_filter($urls)) : [trim($urls)];

        if ($urlList === []) {
            return false;
        }

        $resolvedKey = $key ?? (string) ($this->config['index_now']['key'] ?? config('rankforge.index_now.key', ''));

        if (! $this->isValidKey($resolvedKey)) {
            return false;
        }

        $firstUrl = $urlList[0];
        $host = parse_url($firstUrl, PHP_URL_HOST);

        if (! $host) {
            return false;
        }

        $resolvedKeyLocation = $keyLocation
            ?? ($this->config['index_now']['key_location'] ?? null)
            ?? "https://{$host}/{$resolvedKey}.txt";

        $engine = $this->config['index_now']['engine']
            ?? config('rankforge.index_now.engine', 'https://api.indexnow.org/indexnow');

        $payload = [
            'host' => $host,
            'key' => $resolvedKey,
            'keyLocation' => $resolvedKeyLocation,
            'urlList' => $urlList,
        ];

        try {
            $response = Http::asJson()
                ->timeout(10)
                ->post($engine, $payload);

            return in_array($response->status(), [200, 202], true);
        } catch (\Throwable) {
            return false;
        }
    }

    public function isValidKey(string $key): bool
    {
        if (strlen($key) < 8 || strlen($key) > 128) {
            return false;
        }

        return (bool) preg_match('/^[a-zA-Z0-9\-]+$/', $key);
    }
}
