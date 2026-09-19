# Configuration

The RankForge configuration file is located at `config/rankforge.php`. It allows you to define global defaults, crawler rules, sitemap generation parameters, and LLM optimization settings.

---

## Complete Annotated Configuration

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Site Identity
    |--------------------------------------------------------------------------
    | Global fallback values used when no route or model overrides exist.
    */

    'site_name' => env('APP_NAME', 'Laravel'),

    'title' => [
        // Default title when none is specified
        'default' => env('APP_NAME', 'Laravel'),

        // Separator between page title and site name: '|', '—', '•', '-'
        'separator' => '|',

        // Title template pattern. Available tokens: {title}, {separator}, {site_name}
        'template' => '{title} {separator} {site_name}',

        // Google search maximum character length recommendation
        'max_length' => 60,
    ],

    'description' => [
        // Default meta description
        'default' => 'Welcome to ' . env('APP_NAME', 'our website') . '.',

        // Maximum character length recommendation for search snippets
        'max_length' => 160,
    ],

    // Default global keywords (string or array)
    'keywords' => ['laravel', 'seo', 'rankforge'],

    /*
    |--------------------------------------------------------------------------
    | Robots & Indexing
    |--------------------------------------------------------------------------
    | Default crawler directives and environment safety controls.
    */

    'robots' => [
        'default' => 'index, follow',

        // Environments where indexing is strictly forbidden by default.
        // Automatically outputs: <meta name="robots" content="noindex, nofollow">
        'noindex_environments' => ['local', 'testing', 'staging'],
    ],

    'canonical' => [
        'enabled' => true,
        'trailing_slash' => false,

        // Tracking query parameters stripped from canonical URLs to prevent duplicate penalties
        'strip_query_params' => [
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'utm_term',
            'utm_content',
            'fbclid',
            'gclid',
            'msclkid',
            'mc_cid',
            'mc_eid',
            'ref',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Open Graph (Facebook, LinkedIn, Slack, Pinterest)
    |--------------------------------------------------------------------------
    */

    'open_graph' => [
        'enabled' => true,
        'type' => 'website',
        'image' => env('APP_URL') . '/images/og-default.jpg',
        'image_width' => 1200,
        'image_height' => 630,
        'locale' => 'en_US',
    ],

    /*
    |--------------------------------------------------------------------------
    | Twitter / X Cards
    |--------------------------------------------------------------------------
    */

    'twitter' => [
        'enabled' => true,
        'card' => 'summary_large_image', // 'summary', 'summary_large_image', 'app', 'player'
        'site' => '@yourbrand',
        'creator' => '@eamirgh',
    ],

    /*
    |--------------------------------------------------------------------------
    | JSON-LD Structured Data
    |--------------------------------------------------------------------------
    */

    'json_ld' => [
        'enabled' => true,
        'pretty_print' => env('APP_DEBUG', false),
    ],

    'organization' => [
        'name' => env('APP_NAME', 'Laravel'),
        'url' => env('APP_URL', 'http://localhost'),
        'logo' => env('APP_URL') . '/images/logo.png',
        'same_as' => [
            'https://twitter.com/yourbrand',
            'https://github.com/yourbrand',
            'https://linkedin.com/company/yourbrand',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | XML Sitemaps
    |--------------------------------------------------------------------------
    */

    'sitemap' => [
        'enabled' => true,

        // Maximum URLs per sitemap chunk before auto-splitting (Google maximum: 50,000)
        'max_urls' => 50000,

        // Maximum uncompressed size in bytes (Google maximum: 50MB)
        'max_size' => 50 * 1024 * 1024,

        // Cache TTL in seconds for dynamic sitemap responses
        'cache_ttl' => 3600,

        // Storage disk for pre-rendered sitemaps
        'disk' => 'public',

        // Path within disk
        'path' => 'sitemaps',

        // Registered sources (Model classes, SitemapSource classes, or Callables)
        'sources' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | IndexNow Protocol
    |--------------------------------------------------------------------------
    */

    'index_now' => [
        'enabled' => env('INDEXNOW_ENABLED', false),
        'key' => env('INDEXNOW_KEY', ''),
        'engine' => 'https://api.indexnow.org',
    ],

    /*
    |--------------------------------------------------------------------------
    | robots.txt Management
    |--------------------------------------------------------------------------
    */

    'robots_txt' => [
        'enabled' => true,
        'dynamic' => true,

        'production' => [
            'allow' => ['/'],
            'disallow' => [
                '/admin',
                '/api/internal',
                '/telescope',
                '/horizon',
                '/_debugbar',
            ],
            'user_agents' => [
                '*' => [
                    'allow' => ['/'],
                    'disallow' => ['/admin', '/api/internal'],
                ],
                'Googlebot' => [
                    'allow' => ['/'],
                    'disallow' => ['/admin'],
                ],
                'Bingbot' => [
                    'allow' => ['/'],
                    'disallow' => ['/admin'],
                ],
            ],
            'ai_crawlers' => [
                'GPTBot' => ['allow' => ['/']],
                'ClaudeBot' => ['allow' => ['/']],
                'PerplexityBot' => ['allow' => ['/']],
                'CCBot' => ['disallow' => ['/']],
                'Google-Extended' => ['allow' => ['/']],
            ],
        ],

        'sitemaps' => [],
        'crawl_delay' => null,
        'host' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | llms.txt & llms-full.txt (GEO Engine)
    |--------------------------------------------------------------------------
    */

    'llms_txt' => [
        'enabled' => true,
        'title' => env('APP_NAME', 'Laravel'),
        'description' => 'Semantic API and knowledge documentation for AI agents.',
        'sections' => [],
        'sources' => [],
        'strip_html' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Hreflang (Internationalization)
    |--------------------------------------------------------------------------
    */

    'hreflang' => [
        'enabled' => false,
        'locales' => [
            // 'en' => 'https://example.com',
            // 'es' => 'https://example.com/es',
        ],
        'x_default' => null,
    ],
];
```

---

## Environment Configuration (.env)

Add these helpful environment variables to your `.env` file:

```dotenv
# Site settings
APP_NAME="RankForge Enterprise"
APP_URL="https://example.com"

# IndexNow Instant Notification
INDEXNOW_ENABLED=true
INDEXNOW_KEY="a1b2c3d4e5f6789012345678abcdef01"
```
