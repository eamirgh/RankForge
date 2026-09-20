# Getting Started

RankForge is an enterprise-grade SEO and Generative Engine Optimization (GEO) engine designed specifically for modern Laravel applications (Laravel 11, 12, and 13).

It bridges the gap between traditional search engine optimization (Google, Bing) and next-generation AI search engines (ChatGPT Search, Perplexity, Claude, Apple Intelligence).

---

## Requirements

| Requirement | Minimum Version | Recommended |
|---|---|---|
| **PHP** | 8.2 | 8.3 / 8.4 |
| **Laravel** | 11.0 | 12.x / 13.x |
| **Extensions** | `json`, `mbstring`, `xml` | Enabled by default in PHP |

---

## Installation

Install RankForge via Composer:

```bash
composer require eamirgh/rankforge
```

### Publish Configuration & Assets

RankForge utilizes Laravel's package auto-discovery. Run the install command to publish the configuration file and view templates:

```bash
php artisan rankforge:install
```

This creates:
- `config/rankforge.php` — The central configuration file
- `resources/views/vendor/rankforge/` — View templates (optional overrides)

---

## Quick Start (Blade Application)

### 1. Register the Blade Directive

Add `@rankforgeHead` inside the `<head>` section of your main layout file (e.g., `resources/views/layouts/app.blade.php`):

```html
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- RankForge: renders all title, meta, OG, Twitter, and JSON-LD tags --}}
    @rankforgeHead

    {{-- Application scripts and stylesheets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @yield('content')
    </div>
</body>
</html>
```

### 2. Configure in Controllers

You can customize SEO metadata fluently within any controller, middleware, or route closure:

```php
namespace App\Http\Controllers;

use App\Models\Article;
use Eamirgh\RankForge\Facades\RankForge;
use Eamirgh\RankForge\Schema\Types\BlogPosting;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function show(Article $article): View
    {
        // Fluent SEO setup
        RankForge::title($article->title)
            ->description($article->excerpt)
            ->canonical(route('articles.show', $article))
            ->keywords($article->tags->pluck('name')->toArray())
            ->ogImage(
                url: $article->featured_image_url,
                width: 1200,
                height: 630,
                alt: $article->title
            )
            ->articlePublishedTime($article->published_at)
            ->articleAuthor($article->author->name)
            ->articleSection($article->category->name)
            ->jsonLd(
                BlogPosting::make()
                    ->headline($article->title)
                    ->description($article->excerpt)
                    ->image($article->featured_image_url)
                    ->datePublished($article->published_at)
                    ->dateModified($article->updated_at)
                    ->author($article->author->name)
                    ->publisher(config('app.name'), logo: asset('images/logo.png'))
            );

        return view('articles.show', compact('article'));
    }
}
```

### 3. Automatic Model Fallback

Alternatively, pass your Eloquent model directly using `forModel()`. RankForge will automatically inspect the model for SEO attributes, getters, and JSON-LD schema:

```php
public function show(Article $article): View
{
    // Automatically extracts title, description, image, and JSON-LD
    RankForge::forModel($article);

    return view('articles.show', compact('article'));
}
```

---

## Route-Level Setup Example

For static or marketing pages, define SEO metadata directly in your route definitions:

```php
use Eamirgh\RankForge\Facades\RankForge;
use Illuminate\Support\Facades\Route;

Route::get('/pricing', function () {
    RankForge::title('Pricing Plans')
        ->description('Flexible, transparent pricing for teams of all sizes.')
        ->canonical(url('/pricing'))
        ->ogType('website')
        ->hreflang('en', url('/pricing'))
        ->hreflang('es', url('/es/precios'))
        ->xDefault(url('/pricing'));

    return view('pricing');
})->name('pricing');
```

---

## Example Application

A complete demo application showcasing RankForge with dynamic meta tags, JSON-LD schemas, XML sitemaps, `llms.txt`, and MCP is available on GitHub:

👉 **[eamirgh/rankforge-blog-example](https://github.com/eamirgh/rankforge-blog-example)**
