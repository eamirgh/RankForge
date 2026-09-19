# Skill: rankforge

# RankForge: Laravel SEO & GEO Development Guidelines

## What I Do

I provide comprehensive guidance for implementing modern SEO (Search Engine Optimization) and GEO (Generative Engine Optimization) in Laravel applications using the `eamirgh/rankforge` package:
- Dynamic Meta Tags (titles, descriptions, robots, canonicals, hreflang, OpenGraph, Twitter cards)
- Strongly-typed Schema.org JSON-LD structured data & `@graph` builder
- Scalable XML sitemaps with automatic 50,000 URL chunking and IndexNow instant notification
- AI crawler discovery files: `robots.txt` and `llms.txt` / `llms-full.txt`
- Headless & Inertia.js (Vue 3 / React) `<Head>` integration
- Native Model Context Protocol (MCP) server integration for AI coding assistants

## When to Use Me

Load this skill when:
- Adding or optimizing SEO metadata on Laravel routes, controllers, or models
- Adding JSON-LD structured data for Google Rich Results (Articles, Products, FAQs, Breadcrumbs, etc.)
- Setting up or troubleshooting XML sitemaps and search engine submission (IndexNow)
- Configuring `robots.txt` or optimizing for LLM search engines (`/llms.txt`)
- Integrating SEO in Inertia.js (Vue 3 / React) or headless Laravel APIs
- Interacting with or troubleshooting the RankForge MCP server (`php artisan rankforge:mcp`)

## Framework Information

- **Package**: `eamirgh/rankforge`
- **Namespace**: `Eamirgh\RankForge\`
- **Supported Frameworks**: Laravel 11.x, 12.x, 13.x
- **Language**: PHP 8.2+

---

## Core Code Patterns

### 1. Blade Layout Setup

Include `@rankforgeHead` inside the `<head>` of your root Blade layout:

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

---

### 2. Controller Fluent SEO Setup

```php
use Eamirgh\RankForge\Facades\RankForge;
use Eamirgh\RankForge\Schema\Types\BlogPosting;

public function show(Post $post)
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
```

---

### 3. Eloquent Model Integration

RankForge provides two core traits for Eloquent models:

#### A. Automatic SEO & JSON-LD: `HasJsonLd`
```php
namespace App\Models;

use Eamirgh\RankForge\Schema\Concerns\HasJsonLd;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasJsonLd;

    // Optional overrides:
    public function getSeoTitle(): string
    {
        return $this->meta_title ?? $this->title;
    }

    public function getSeoDescription(): string
    {
        return $this->meta_description ?? $this->excerpt;
    }

    public function getSeoImage(): string
    {
        return $this->featured_image_url;
    }
}
```

In your controller, pass the model directly:
```php
RankForge::forModel($article);
```

#### B. Automatic Sitemap Cache Invalidation: `InvalidatesSitemapCache`
```php
namespace App\Models;

use Eamirgh\RankForge\Sitemap\Concerns\InvalidatesSitemapCache;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use InvalidatesSitemapCache;

    // Optional: Return URL for instant IndexNow submission on save
    public function getSitemapUrl(): string
    {
        return route('products.show', $this);
    }
}
```

---

### 4. Schema.org JSON-LD Types Cheatsheet

```php
use Eamirgh\RankForge\Facades\RankForge;
use Eamirgh\RankForge\Schema\Types\WebSite;
use Eamirgh\RankForge\Schema\Types\LocalBusiness;
use Eamirgh\RankForge\Schema\Types\Product;
use Eamirgh\RankForge\Schema\Types\Offer;
use Eamirgh\RankForge\Schema\Types\FAQPage;
use Eamirgh\RankForge\Schema\Types\BreadcrumbList;
use Eamirgh\RankForge\Schema\Types\HowTo;
use Eamirgh\RankForge\Schema\Graph;

// WebSite with Sitelinks Searchbox
RankForge::jsonLd(
    WebSite::make()->name('App')->url('https://example.com')->searchAction('https://example.com/search?q={search_term_string}')
);

// E-commerce Product & Offer
RankForge::jsonLd(
    Product::make()
        ->name('Headphones')
        ->sku('HP-100')
        ->offers(Offer::make()->price(199.99)->priceCurrency('USD')->availability('https://schema.org/InStock'))
        ->aggregateRating(4.8, 150)
);

// FAQPage
RankForge::jsonLd(
    FAQPage::make()
        ->addQuestion('Question 1?', 'Answer 1.')
        ->addQuestion('Question 2?', 'Answer 2.')
);

// Breadcrumbs from URL segments
RankForge::jsonLd(BreadcrumbList::fromUrl('https://example.com/shop/audio/headphones'));

// Cohesive @graph
RankForge::jsonLd(
    Graph::make()->add($organization)->add($website)->add($article)
);
```

---

### 5. XML Sitemaps & IndexNow

Register sources in a ServiceProvider using memory-safe Eloquent cursors:

```php
use Eamirgh\RankForge\Facades\RankForge;
use Eamirgh\RankForge\Sitemap\SitemapUrl;
use App\Models\Post;

RankForge::sitemap()->register('posts', function () {
    return Post::published()
        ->cursor()
        ->map(fn (Post $post) => SitemapUrl::make(route('posts.show', $post))
            ->lastmod($post->updated_at)
            ->changefreq('weekly')
            ->priority(0.8)
            ->image($post->featured_image_url, $post->title)
        );
});
```

Submit updated URLs to IndexNow:
```php
RankForge::indexNow()->submit([
    'https://example.com/posts/new-post',
]);
```

---

### 6. Robots.txt & LLMs.txt (GEO)

- Dynamic `/robots.txt` automatically serves `Disallow: /` in `local`, `staging`, `testing`.
- Dynamic `/llms.txt` and `/llms-full.txt` present clean semantic Markdown to LLMs:

```php
RankForge::llmsTxt()
    ->title('Acme Developer Docs')
    ->description('Comprehensive guides and API reference.')
    ->addSection('Guides', 'Key guides', [
        ['title' => 'Quickstart', 'url' => 'https://example.com/quickstart', 'description' => '5-minute setup'],
    ])
    ->addDirectory(base_path('docs'), 'https://example.com/docs');
```

---

### 7. Headless & Inertia.js (Vue 3 / React)

In `HandleInertiaRequests` middleware:
```php
public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'seo' => fn () => RankForge::toArray(),
    ]);
}
```

In Vue 3 `<Head>`:
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

### 8. Model Context Protocol (MCP) Server

Start the stdio MCP server for AI assistants:
```bash
php artisan rankforge:mcp
```

Tools exposed to AI agents:
- `check_seo_health`: Runs automated health check.
- `get_llms_txt`: Returns `/llms.txt` or `/llms-full.txt`.
- `get_robots_txt`: Returns current `robots.txt`.
- `get_sitemap`: Queries sitemap index or section XML.
- `inspect_html_meta`: Parses and audits SEO metadata from raw HTML.
- `submit_indexnow`: Submits URLs to IndexNow.

Resources exposed:
- `rankforge://llms.txt`
- `rankforge://llms-full.txt`
- `rankforge://robots.txt`
- `rankforge://sitemap.xml`
- `rankforge://skill.md`

---

## Artisan Commands Reference

| Command | Purpose |
|---|---|
| `php artisan rankforge:install` | Publishes config and views |
| `php artisan rankforge:skill` | Publishes this skill file (`.agents/skills/rankforge/SKILL.md`) to the app |
| `php artisan rankforge:sitemap:generate` | Builds static sitemap files to disk |
| `php artisan rankforge:sitemap:ping` | Pings search engines and IndexNow |
| `php artisan rankforge:robots:generate` | Generates physical `robots.txt` file |
| `php artisan rankforge:llms:generate` | Compiles physical `llms.txt` and `llms-full.txt` files |
| `php artisan rankforge:check` | Audits SEO health and reports diagnosis table |
| `php artisan rankforge:mcp` | Starts the Model Context Protocol (MCP) server over stdio |

---

## Best Practices & Anti-Patterns

### ✅ DO:
- Use `cursor()` or `lazy()` when streaming Eloquent models to sitemaps to prevent memory exhaustion.
- Use `RankForge::forModel($model)` for automatic fallback cascading from model attributes to config defaults.
- Attach `use InvalidatesSitemapCache;` to models whose changes should trigger sitemap regeneration.
- Configure `INDEXNOW_KEY` in `.env` to enable instant search engine indexing.

### ❌ DO NOT:
- Do not call `->all()` or `->get()` without chunking when generating sitemaps for large tables.
- Do not output raw unescaped HTML into meta tags; always let RankForge sanitize inputs.
- Do not disable environment-aware robots directives in staging/testing without explicit user intent.
