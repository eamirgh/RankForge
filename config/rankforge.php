<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Site Name
    |--------------------------------------------------------------------------
    |
    | The global site name used in title templates and structured data.
    | Defaults to your application name from the APP_NAME env variable.
    |
    */

    'site_name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Title Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how page titles are generated. The template supports
    | placeholders: {title}, {separator}, {site_name}.
    |
    */

    'title' => [
        'default' => '',
        'separator' => '|',
        'template' => '{title} {separator} {site_name}',
        'max_length' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Meta Description
    |--------------------------------------------------------------------------
    |
    | Default meta description and maximum character length.
    | Google typically truncates descriptions at ~155-160 characters.
    |
    */

    'description' => [
        'default' => '',
        'max_length' => 160,
    ],

    /*
    |--------------------------------------------------------------------------
    | Meta Keywords
    |--------------------------------------------------------------------------
    |
    | Default keywords for all pages. While most search engines no longer
    | use meta keywords for ranking, some specialized engines still do.
    |
    */

    'keywords' => [],

    /*
    |--------------------------------------------------------------------------
    | Robots Configuration
    |--------------------------------------------------------------------------
    |
    | Control search engine crawler behavior. Pages will automatically
    | receive a "noindex, nofollow" directive in the listed environments.
    |
    */

    'robots' => [
        'default' => 'index, follow',
        'noindex_environments' => ['local', 'testing', 'staging'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Canonical URL
    |--------------------------------------------------------------------------
    |
    | Canonical URL settings to prevent duplicate content issues.
    | Specified query parameters will be stripped from canonical URLs.
    |
    */

    'canonical' => [
        'enabled' => true,
        'trailing_slash' => false,
        'strip_query_params' => [
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'utm_term',
            'utm_content',
            'fbclid',
            'gclid',
            'ref',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Open Graph
    |--------------------------------------------------------------------------
    |
    | Open Graph meta tags for rich social media previews on Facebook,
    | LinkedIn, and other platforms that support the OG protocol.
    |
    */

    'open_graph' => [
        'enabled' => true,
        'type' => 'website',
        'image' => null,
        'image_width' => 1200,
        'image_height' => 630,
        'locale' => 'en_US',
    ],

    /*
    |--------------------------------------------------------------------------
    | Twitter Card
    |--------------------------------------------------------------------------
    |
    | Twitter (X) card meta tags for rich previews when links are shared.
    | Supported card types: summary, summary_large_image, app, player.
    |
    */

    'twitter' => [
        'enabled' => true,
        'card' => 'summary_large_image',
        'site' => null,
        'creator' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | JSON-LD Structured Data
    |--------------------------------------------------------------------------
    |
    | Control JSON-LD structured data output. When pretty_print is enabled,
    | the JSON output will be formatted with indentation for readability.
    |
    */

    'json_ld' => [
        'enabled' => true,
        'pretty_print' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Organization
    |--------------------------------------------------------------------------
    |
    | Organization structured data used in JSON-LD schema. The same_as
    | array should contain URLs to your official social media profiles.
    |
    */

    'organization' => [
        'name' => null,
        'url' => null,
        'logo' => null,
        'same_as' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sitemap
    |--------------------------------------------------------------------------
    |
    | XML sitemap generation settings. Sources should be an array of class
    | names that implement RankForge\Sitemap\Contracts\SitemapSource.
    |
    */

    'sitemap' => [
        'enabled' => true,
        'max_urls' => 50000,
        'max_size' => 50 * 1024 * 1024, // 50 MB
        'cache_ttl' => 3600,
        'disk' => 'public',
        'path' => 'sitemaps',
        'sources' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | IndexNow
    |--------------------------------------------------------------------------
    |
    | IndexNow protocol integration for instant search engine indexing.
    | Supports Bing, Yandex, Seznam, and Naver via the IndexNow API.
    |
    */

    'index_now' => [
        'enabled' => false,
        'key' => env('INDEXNOW_KEY'),
        'engine' => 'https://api.indexnow.org/indexnow',
    ],

    /*
    |--------------------------------------------------------------------------
    | Robots.txt
    |--------------------------------------------------------------------------
    |
    | Dynamic robots.txt generation. When dynamic is enabled, the package
    | will serve a generated robots.txt via route. Production rules define
    | the directives for the production environment.
    |
    */

    'robots_txt' => [
        'enabled' => true,
        'dynamic' => true,

        'production' => [
            'allow' => [
                '/',
            ],
            'disallow' => [
                '/admin',
                '/api',
                '/*.json$',
            ],
            'user_agents' => [
                '*' => [
                    'allow' => ['/'],
                    'disallow' => ['/admin', '/api'],
                ],
                'Googlebot' => [
                    'allow' => ['/'],
                    'disallow' => [],
                ],
                'Bingbot' => [
                    'allow' => ['/'],
                    'disallow' => [],
                ],
            ],
            'ai_crawlers' => [
                'GPTBot' => [
                    'disallow' => ['/'],
                ],
                'ClaudeBot' => [
                    'disallow' => ['/'],
                ],
                'PerplexityBot' => [
                    'disallow' => ['/'],
                ],
                'CCBot' => [
                    'disallow' => ['/'],
                ],
                'Google-Extended' => [
                    'disallow' => ['/'],
                ],
            ],
        ],

        'sitemaps' => [],
        'crawl_delay' => null,
        'host' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | LLMs.txt
    |--------------------------------------------------------------------------
    |
    | LLMs.txt generation for AI crawlers and language models.
    | Provides machine-readable information about your site content.
    |
    */

    'llms_txt' => [
        'enabled' => true,
        'title' => null,
        'description' => null,
        'sections' => [],
        'sources' => [],
        'strip_html' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Hreflang
    |--------------------------------------------------------------------------
    |
    | Hreflang alternate link tags for multilingual and multi-regional
    | sites. Each locale entry should map a locale code to its base URL.
    |
    */

    'hreflang' => [
        'enabled' => false,
        'locales' => [],
        'x_default' => null,
    ],

];
