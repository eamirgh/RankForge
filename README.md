<p align="center">
  <img src="docs/public/logo.svg" alt="RankForge Logo" width="128" height="128">
</p>

<h1 align="center">RankForge</h1>

<p align="center">
  <strong>Enterprise-Grade SEO & Generative Engine Optimization (GEO) Engine for Laravel 11, 12, and 13.</strong>
</p>

<p align="center">
  <a href="https://packagist.org/packages/eamirgh/rankforge"><img src="https://img.shields.io/packagist/v/eamirgh/rankforge.svg?style=flat-square" alt="Latest Version on Packagist"></a>
  <a href="https://packagist.org/packages/eamirgh/rankforge"><img src="https://img.shields.io/badge/laravel-11.x%20%7C%2012.x%20%7C%2013.x-red.svg?style=flat-square" alt="Laravel Versions"></a>
  <a href="https://packagist.org/packages/eamirgh/rankforge"><img src="https://img.shields.io/packagist/php-v/eamirgh/rankforge.svg?style=flat-square" alt="PHP Version"></a>
  <a href="https://github.com/eamirgh/rankforge/actions/workflows/tests.yml"><img src="https://img.shields.io/github/actions/workflow/status/eamirgh/rankforge/tests.yml?branch=main&label=tests&style=flat-square" alt="Tests Status"></a>
  <a href="https://codecov.io/gh/eamirgh/rankforge"><img src="https://img.shields.io/codecov/c/github/eamirgh/rankforge/main.svg?style=flat-square" alt="Coverage Status"></a>
  <a href="https://eamirgh.github.io/RankForge"><img src="https://img.shields.io/badge/docs-eamirgh.github.io%2FRankForge-blue.svg?style=flat-square" alt="Documentation"></a>
  <a href="https://packagist.org/packages/eamirgh/rankforge"><img src="https://img.shields.io/packagist/dt/eamirgh/rankforge.svg?style=flat-square" alt="Total Downloads"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="License"></a>
</p>

---

## Overview

**RankForge** bridges traditional search engine optimization (Google, Bing) and modern **Generative Engine Optimization (GEO)** for AI crawlers (ChatGPT Search, Perplexity, Claude, Apple Intelligence).

Designed for high-throughput applications, RankForge combines an ergonomic fluent API, strongly-typed Schema.org JSON-LD generation, memory-safe streaming sitemaps, automated `/llms.txt` compilation, and native **Model Context Protocol (MCP)** support for AI coding assistants.

### Core Highlights

* **🏷️ Dynamic Meta Tags Engine**: Configurable title templates, separators, word-boundary truncation, sanitized descriptions, keywords, robots directives, and canonical URLs with query parameter filtering.
* **📱 Social Graph & Rich Media**: OpenGraph (articles, images with dimensions/alt/type, multi-locale) and Twitter/X Cards with fallback cascading.
* **📜 Strongly-Typed JSON-LD**: Schema.org compliant generators for `WebSite`, `Organization`, `LocalBusiness`, `BreadcrumbList`, `Article`, `BlogPosting`, `NewsArticle`, `Product`, `Offer`, `FAQPage`, `HowTo`, and `SoftwareApplication` with a cohesive `@graph` builder.
* **🗺️ Scalable XML Sitemaps**: Automatic 50,000 URL / 50 MB chunking, memory-safe Eloquent cursors, image/video/news/hreflang extensions, and instant **IndexNow** search engine pinging.
* **🤖 Generative Engine Optimization (GEO)**: Native `/llms.txt` and `/llms-full.txt` generators to present clean, structured Markdown summaries to LLMs.
* **🔌 Model Context Protocol (MCP)**: Built-in MCP server (`php artisan rankforge:mcp`) allowing Claude Desktop, Cursor, and AI agents to audit and interact with your application's SEO engine.
* **⚡ Headless & Inertia.js Ready**: Full JSON serialization (`toArray()`, `toJson()`) for Vue 3, React, Next.js, and Nuxt frontends alongside the `@rankforgeHead` Blade directive.
* **🛡️ Environment-Safe Indexing**: Automatic enforcement of `noindex, nofollow` in `local`, `testing`, and `staging` environments to prevent staging leakage.

---

## Requirements

| Platform | Minimum Version | Supported |
|---|---|---|
| **PHP** | `^8.2` | `8.2`, `8.3`, `8.4` |
| **Laravel** | `^11.0` | `11.x`, `12.x`, `13.x` |

---

## Installation

Install RankForge via Composer:

```bash
composer require eamirgh/rankforge
```

Publish the configuration file and views:

```bash
php artisan rankforge:install
```

This publishes `config/rankforge.php` and view templates to `resources/views/vendor/rankforge/`.

---

## Quick Start

### 1. Blade Layout

Include the `@rankforgeHead` directive inside your `<head>` section:

```html
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Renders all SEO meta tags, OpenGraph, Twitter Cards, and JSON-LD --}}
    @rankforgeHead

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @yield('content')
</body>
</html>
```

### 2. Controller Usage

Configure metadata fluently within any controller or route:

```php
namespace App\Http\Controllers;

use App\Models\Post;
use Eamirgh\RankForge\Facades\RankForge;
use Eamirgh\RankForge\Schema\Types\BlogPosting;
use Illuminate\View\View;

class PostController extends Controller
{
    public function show(Post $post): View
    {
        RankForge::title($post->title)
            ->description($post->excerpt)
            ->canonical(route('posts.show', $post))
            ->keywords($post->tags->pluck('name')->toArray())
            ->ogImage(
                url: $post->featured_image_url,
                width: 1200,
                height: 630,
                alt: $post->title
            )
            ->articlePublishedTime($post->published_at)
            ->articleAuthor($post->author->name)
            ->articleSection($post->category->name)
            ->jsonLd(
                BlogPosting::make()
                    ->headline($post->title)
                    ->description($post->excerpt)
                    ->image($post->featured_image_url)
                    ->datePublished($post->published_at)
                    ->dateModified($post->updated_at)
                    ->author($post->author->name)
                    ->publisher(config('app.name'), logo: asset('images/logo.png'))
            );

        return view('posts.show', compact('post'));
    }
}
```

### 3. Automatic Model Fallback

Alternatively, pass your Eloquent model directly using `forModel()`. RankForge inspects the model for SEO getters/attributes and JSON-LD traits automatically:

```php
public function show(Post $post): View
{
    RankForge::forModel($post);

    return view('posts.show', compact('post'));
}
```

---

## Detailed Features

### 1. Dynamic Meta Tags Engine

#### Title Management
```php
RankForge::title('Cloud Hosting');
// Output: <title>Cloud Hosting | Acme Enterprise</title>
```
* **Templates**: Configurable pattern (`{title} {separator} {site_name}`).
* **Separators**: `|`, `—`, `•`, `-`.
* **Truncation**: Automatic word-boundary truncation to recommended character lengths (default: 60 chars).
* **Warnings**: Query character threshold warnings via `RankForge::hasTitleWarning()` and `RankForge::getTitleWarning()`.

#### Descriptions & Keywords
```php
RankForge::description('Deploy scalable Laravel apps in seconds.')
    ->keywords(['laravel', 'hosting', 'cloud']);
```
* Descriptions are stripped of HTML tags, whitespace-normalized, and truncated to 160 characters.
* Keywords accept either arrays or comma-separated strings and are deduplicated with configuration defaults.

#### Robots Directives
```php
RankForge::index();
RankForge::noindex();
RankForge::follow();
RankForge::nofollow();
RankForge::noarchive();
RankForge::nosnippet();
RankForge::maxSnippet(160);
RankForge::maxImagePreview('large'); // 'none', 'standard', 'large'
RankForge::maxVideoPreview(30);

// Custom bot directives
RankForge::customRobots('googlebot', 'max-snippet:100');
```

#### Canonical URLs & Query Filtering
```php
RankForge::canonical('https://example.com/shop?utm_source=fb&gclid=123&sort=price');
// Output: <link rel="canonical" href="https://example.com/shop?sort=price">
```
* Strips tracking parameters (`utm_*`, `fbclid`, `gclid`, `ref`, etc.).
* Supports both blacklist (`strip_query_params`) and whitelist (`whitelist_query_params`) modes.
* Configurable trailing slash enforcement.

#### Hreflang & Internationalization
```php
RankForge::hreflang('en-US', 'https://example.com/pricing')
    ->hreflang('es-ES', 'https://example.com/es/precios')
    ->hreflang('fr-FR', 'https://example.com/fr/tarifs')
    ->xDefault('https://example.com/pricing');
```

---

### 2. Social Media Cards

#### Open Graph
```php
RankForge::ogTitle('RankForge Launch')
    ->ogDescription('Explore modern SEO for Laravel.')
    ->ogType('article')
    ->ogImage(
        url: 'https://example.com/og.jpg',
        width: 1200,
        height: 630,
        alt: 'Banner',
        type: 'image/jpeg'
    )
    ->ogLocaleAlternate(['es_ES', 'fr_FR'])
    ->articlePublishedTime(now())
    ->articleAuthor('Amir Ghaffari')
    ->articleSection('Technology')
    ->articleTags(['laravel', 'seo']);
```

#### Twitter / X Cards
```php
RankForge::twitterCard('summary_large_image') // 'summary', 'summary_large_image', 'app', 'player'
    ->twitterSite('@rankforge')
    ->twitterCreator('@eamirgh')
    ->twitterImage('https://example.com/twitter.jpg', alt: 'Feature Banner');
```

---

### 3. JSON-LD Structured Data Engine

All Schema types strictly adhere to Schema.org standards:

```php
use Eamirgh\RankForge\Schema\Types\WebSite;
use Eamirgh\RankForge\Schema\Types\LocalBusiness;
use Eamirgh\RankForge\Schema\Types\Product;
use Eamirgh\RankForge\Schema\Types\Offer;
use Eamirgh\RankForge\Schema\Types\FAQPage;
use Eamirgh\RankForge\Schema\Types\BreadcrumbList;

// WebSite with Sitelinks Searchbox
RankForge::jsonLd(
    WebSite::make()
        ->name('Acme Inc')
        ->url('https://example.com')
        ->searchAction('https://example.com/search?q={search_term_string}')
);

// E-commerce Product & Offer
RankForge::jsonLd(
    Product::make()
        ->name('Noise-Cancelling Headphones')
        ->sku('AUDIO-400')
        ->brand('SonicSound')
        ->offers(
            Offer::make()
                ->price(249.99)
                ->priceCurrency('USD')
                ->availability('https://schema.org/InStock')
                ->priceValidUntil(now()->addYear())
        )
        ->aggregateRating(ratingValue: 4.8, reviewCount: 350)
);

// Breadcrumbs from URL segments
RankForge::jsonLd(
    BreadcrumbList::fromUrl('https://example.com/docs/getting-started/installation')
);

// Dynamic FAQ Page
RankForge::jsonLd(
    FAQPage::make()
        ->addQuestion('Does RankForge support Laravel 13?', 'Yes, Laravel 11, 12, and 13 are supported.')
        ->addQuestion('Does it support Inertia.js?', 'Yes, via toArray() and toJson() serialization.')
);
```

#### Schema Graph Builder (`@graph`)
Link multiple entities in a single cohesive graph:
```php
use Eamirgh\RankForge\Schema\Graph;

$graph = Graph::make()
    ->add($organization)
    ->add($website)
    ->add($article);

RankForge::jsonLd($graph);
```

---

### 4. XML Sitemaps & IndexNow

Built for high-volume catalogs without memory exhaustion.

#### Registering Sources
```php
use Eamirgh\RankForge\Facades\RankForge;
use Eamirgh\RankForge\Sitemap\SitemapUrl;
use App\Models\Product;

RankForge::sitemap()->register('products', function () {
    return Product::query()
        ->where('is_active', true)
        ->cursor()
        ->map(fn (Product $p) => SitemapUrl::make(route('products.show', $p))
            ->lastmod($p->updated_at)
            ->changefreq('daily')
            ->priority(0.9)
            ->image($p->featured_image_url, $p->name)
        );
});
```

* **Automatic Chunking**: Auto-splits when exceeding 50,000 URLs or 50 MB (`sitemap.xml`, `sitemap-products-1.xml`, etc.).
* **Extensions**: Full support for `<image:image>`, `<video:video>`, `<news:news>`, and `<xhtml:link rel="alternate">`.
* **Automatic Cache Invalidation**: Add `use InvalidatesSitemapCache;` to your Eloquent models to clear the sitemap cache on `saved` and `deleted`.

#### Instant IndexNow Pinging
Submit updated URLs directly to Microsoft Bing and Yandex:
```php
RankForge::indexNow()->submit([
    'https://example.com/posts/new-post',
    'https://example.com/products/updated-product',
]);
```

---

### 5. Crawlers: `robots.txt` & `llms.txt` (GEO)

#### Dynamic `robots.txt`
Served at `/robots.txt`:
* **Non-Production**: Automatically returns `User-agent: * Disallow: /` in `local`, `staging`, and `testing`.
* **Production**: Serves configured allow/disallow paths, bot rules (Googlebot, Bingbot), and AI crawler policies (`GPTBot`, `ClaudeBot`, `PerplexityBot`, `CCBot`, `Google-Extended`).

#### `llms.txt` & `llms-full.txt` (Generative Engine Optimization)
Served at `/llms.txt` and `/llms-full.txt`:
```php
RankForge::llmsTxt()
    ->title('Acme Developer Platform')
    ->description('Official API documentation and architectural guides.')
    ->addSection('Guides', 'Key guides', [
        ['title' => 'Quickstart', 'url' => 'https://example.com/quickstart', 'description' => '5-minute setup'],
    ])
    ->addDirectory(base_path('docs'), 'https://example.com/docs');
```

---

### 6. Model Context Protocol (MCP) Server

RankForge provides a built-in, native MCP server over stdio for AI assistants (Claude Desktop, Cursor, Windsurf, Zed):

```bash
php artisan rankforge:mcp
```

#### MCP Tools Provided to LLMs:
* `check_seo_health`: Runs an automated SEO health audit.
* `get_llms_txt`: Generates semantic Markdown context (`/llms.txt` or `/llms-full.txt`).
* `get_robots_txt`: Returns active `robots.txt` directives.
* `get_sitemap`: Queries root sitemap index or specific chunked sections.
* `inspect_html_meta`: Parses and audits SEO metadata from raw HTML.
* `submit_indexnow`: Submits URLs to IndexNow.

#### MCP Resources:
* `rankforge://llms.txt` (`text/markdown`)
* `rankforge://llms-full.txt` (`text/markdown`)
* `rankforge://robots.txt` (`text/plain`)
* `rankforge://sitemap.xml` (`application/xml`)

---

### 7. Headless & Inertia.js (Vue 3 / React)

#### Share in Middleware
```php
namespace App\Http\Middleware;

use Eamirgh\RankForge\Facades\RankForge;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'seo' => fn () => RankForge::toArray(),
        ]);
    }
}
```

#### Vue 3 (`<Head>`)
```vue
<script setup>
import { Head, usePage } from '@inertiajs/vue3';
const { props } = usePage();
</script>

<template>
  <Head>
    <title>{{ props.seo.title }}</title>
    <meta name="description" :content="props.seo.description" />
    <meta name="robots" :content="props.seo.robots" />
    <link v-if="props.seo.canonical" rel="canonical" :href="props.seo.canonical" />

    <meta v-for="(val, key) in props.seo.open_graph" :key="key" :property="`og:${key}`" :content="val" />
    <meta v-for="(val, key) in props.seo.twitter" :key="key" :name="`twitter:${key}`" :content="val" />

    <component
      :is="'script'"
      v-for="(schema, i) in props.seo.json_ld"
      :key="i"
      type="application/ld+json"
      v-html="JSON.stringify(schema)"
    />
  </Head>
</template>
```

---

## Artisan Command Palette

| Command | Description |
|---|---|
| `php artisan rankforge:install` | Publishes package configuration and Blade views |
| `php artisan rankforge:sitemap:generate` | Pre-renders XML sitemaps to disk storage |
| `php artisan rankforge:sitemap:ping` | Pings search engines and IndexNow with updated sitemaps |
| `php artisan rankforge:robots:generate` | Generates physical `robots.txt` file to `public/` |
| `php artisan rankforge:llms:generate` | Compiles physical `llms.txt` and `llms-full.txt` files |
| `php artisan rankforge:check` | Audits SEO health (title, description, robots, sitemaps, JSON-LD) |
| `php artisan rankforge:mcp` | Starts the Model Context Protocol (MCP) server over stdio |

---

## Performance Benchmarks

Measured on PHP 8.4:

| Benchmark Feature | Throughput | Average Latency | Peak Memory |
|---|---|---|---|
| **Meta Tags Rendering (`renderHead`)** | **~7,000 ops/sec** | 0.15 ms/op | 2.00 MB |
| **JSON-LD `@graph` Serialization** | **~55,000 ops/sec** | 0.02 ms/op | ~0.00 MB |
| **BreadcrumbList URL Parsing** | **~95,000 ops/sec** | 0.01 ms/op | ~0.00 MB |
| **50,000 URL XML Sitemap Generation** | **~105,000 URLs/sec** | 0.48 s (14.5 MB XML) | 35.1 MB |
| **Canonical URL Filtering (Wildcards & Whitelist)** | **~120,000 ops/sec** | 0.008 ms/op | 14.5 MB |
| **Robots.txt Rule Engine (Multi-Agent & AI Bots)** | **~72,000 ops/sec** | 0.014 ms/op | 14.5 MB |
| **LLMs.txt & LLMs-Full.txt Compilation (GEO)** | **~58,000 ops/sec** | 0.017 ms/op | 14.5 MB |
| **HTML to Markdown (`ContentTransformer`)** | **~34,000 ops/sec** | 0.030 ms/op | 14.5 MB |
| **Eloquent Model Integration (`forModel`)** | **~4,500 ops/sec** | 0.23 ms/op | 14.5 MB |
| **Headless Serialization (`toArray` / `toJson`)** | **~7,700 ops/sec** | 0.13 ms/op | 14.5 MB |
| **Model Context Protocol (MCP) JSON-RPC Handling** | **~230,000 ops/sec** | 0.004 ms/op | 14.5 MB |

Run benchmarks locally:
```bash
make benchmark
```

---

## Testing

RankForge is covered by 148+ automated tests and 558+ assertions across PHP 8.2, 8.3, 8.4 and Laravel 11, 12, 13:

```bash
make test
# or
./vendor/bin/pest
```

---

## Documentation

Full interactive documentation is available at [eamirgh.github.io/RankForge](https://eamirgh.github.io/RankForge).

To run documentation locally:
```bash
npm install
npm run docs:dev
```

---

## Contributing

Please review [AGENTS.md](AGENTS.md) before contributing. Follow the 3-step development cycle:
1. **Test First** in `tests/Unit/` or `tests/Feature/`.
2. **Implement Code** in `src/`.
3. **Document** in `docs/`.

---

## License

RankForge is open-sourced software licensed under the [MIT license](LICENSE).
