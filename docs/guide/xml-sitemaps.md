# XML Sitemaps & IndexNow

RankForge includes an enterprise-grade XML sitemap generator built to handle hundreds of thousands of URLs without memory exhaustion.

---

## Scalable Architecture

Google Webmaster guidelines require that any single sitemap file must:
1. Not contain more than **50,000 URLs**
2. Not exceed **50 MB uncompressed**

RankForge satisfies these rules through **automatic chunking**:
- Master Sitemap Index: `/sitemap.xml`
- Chunked Child Sitemaps: `/sitemap-{section}-{page}.xml` (e.g. `sitemap-products-1.xml`, `sitemap-products-2.xml`)

---

## Registering Sources

Sitemap sources can be registered dynamically in a service provider (such as `AppServiceProvider`) or defined via configuration.

### 1. Using Eloquent Cursors (Zero Memory Overhead)

For large database tables, always use `cursor()` or `lazy()` to prevent loading thousands of models into RAM at once:

```php
namespace App\Providers;

use App\Models\Product;
use Eamirgh\RankForge\Facades\RankForge;
use Eamirgh\RankForge\Sitemap\SitemapUrl;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RankForge::sitemap()->addSource('products', function () {
            return Product::query()
                ->where('is_active', true)
                ->cursor()
                ->map(fn (Product $product) => 
                    SitemapUrl::make(route('products.show', $product))
                        ->lastmod($product->updated_at)
                        ->changefreq('daily')
                        ->priority(0.9)
                        ->addImage(
                            loc: $product->featured_image_url,
                            title: $product->name,
                            caption: $product->short_description
                        )
                );
        });
    }
}
```

---

## Specialized Sitemap Extensions

### Image Sitemaps
```php
$url = SitemapUrl::make('https://example.com/gallery')
    ->addImage('https://example.com/images/photo1.jpg', title: 'Sunset over Mountains', caption: 'Shot on location in Banff')
    ->addImage('https://example.com/images/photo2.jpg', title: 'Lake Louise');
```

### Video Sitemaps
```php
$url = SitemapUrl::make('https://example.com/tutorials/laravel-setup')
    ->addVideo(
        thumbnailLoc: 'https://example.com/thumbnails/setup.jpg',
        title: 'Laravel 13 Full Setup Tutorial',
        description: 'Complete walk-through of setting up a new Laravel 13 project.',
        contentLoc: 'https://example.com/videos/setup.mp4',
        playerLoc: 'https://example.com/embed/setup'
    );
```

### Google News Sitemaps
For articles published in the last 48 hours:
```php
$url = SitemapUrl::make('https://example.com/news/breakthrough')
    ->news(
        publicationName: 'The Tech Chronicle',
        publicationLanguage: 'en',
        publicationDate: now()->subHours(2),
        title: 'Quantum Computing Breakthrough Announced'
    );
```

### Multilingual Alternates
```php
$url = SitemapUrl::make('https://example.com/pricing')
    ->addAlternate('es', 'https://example.com/es/precios')
    ->addAlternate('fr', 'https://example.com/fr/tarifs');
```

---

## Pre-Rendering Sitemaps to Disk

For high-traffic sites, pre-render your sitemaps to public disk storage using Artisan:

```bash
php artisan rankforge:sitemap:generate --disk=public --path=sitemaps
```

### Schedule in Laravel Console
In `routes/console.php` (Laravel 11+) or `app/Console/Kernel.php`:
```php
use Illuminate\Support\Facades\Schedule;

// Regenerate sitemaps every night at midnight
Schedule::command('rankforge:sitemap:generate')->dailyAt('00:00');
```

---

## IndexNow Protocol Support

IndexNow allows you to instantly notify search engines (Microsoft Bing, Yandex, Naver, Seznam) whenever pages are added, modified, or deleted, bypassing crawler queue delays.

### 1. Configure in `.env`
```dotenv
INDEXNOW_ENABLED=true
INDEXNOW_KEY="your-32-character-hex-key"
```

### 2. Submit URLs Programmatically
```php
use Eamirgh\RankForge\Facades\RankForge;

// Single URL or array of URLs
RankForge::indexNow()->submit([
    'https://example.com/blog/new-article',
    'https://example.com/products/discounted-item',
]);
```

### 3. Model Observer Integration
Notify search engines automatically when Eloquent models are updated:

```php
namespace App\Observers;

use App\Models\Post;
use Eamirgh\RankForge\Facades\RankForge;

class PostObserver
{
    public function saved(Post $post): void
    {
        if ($post->is_published) {
            RankForge::indexNow()->submit(route('posts.show', $post));
        }
    }
}
```
