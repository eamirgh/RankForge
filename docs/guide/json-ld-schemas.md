# JSON-LD Structured Data Engine

RankForge provides a strongly-typed, schema-compliant JSON-LD generator adhering strictly to [Schema.org](https://schema.org) specifications.

---

## Supported Schema Types

### 1. WebSite (with Sitelinks Searchbox)

Enable Google Sitelinks Searchbox so users can search your site directly from Google search results:

```php
use Eamirgh\RankForge\Facades\RankForge;
use Eamirgh\RankForge\Schema\Types\WebSite;

RankForge::jsonLd(
    WebSite::make()
        ->name('Acme Corporation')
        ->url('https://example.com')
        ->searchAction('https://example.com/search?q={search_term_string}')
);
```

---

### 2. Organization & LocalBusiness

Enhance Google Knowledge Graph panels and local search results:

```php
use Eamirgh\RankForge\Schema\Types\LocalBusiness;

RankForge::jsonLd(
    LocalBusiness::make()
        ->name('Acme Tech Headquarters')
        ->telephone('+1-555-0199')
        ->priceRange('$$$')
        ->address([
            'streetAddress' => '100 Innovation Blvd',
            'addressLocality' => 'Austin',
            'addressRegion' => 'TX',
            'postalCode' => '78701',
            'addressCountry' => 'US',
        ])
        ->geo(latitude: 30.2672, longitude: -97.7431)
        ->openingHours([
            'Mo-Fr 08:00-18:00',
            'Sa 10:00-16:00',
        ])
        ->sameAs([
            'https://twitter.com/acmetech',
            'https://linkedin.com/company/acmetech',
            'https://github.com/acmetech',
        ])
);
```

---

### 3. BreadcrumbList

Produce structured breadcrumb navigation for search engine snippets:

```php
use Eamirgh\RankForge\Schema\Types\BreadcrumbList;

// Method A: Explicitly defined items
RankForge::jsonLd(
    BreadcrumbList::make()
        ->add('Home', 'https://example.com')
        ->add('Products', 'https://example.com/products')
        ->add('Audio', 'https://example.com/products/audio')
        ->add('Wireless Headphones', 'https://example.com/products/audio/wireless-headphones')
);

// Method B: Automatic parsing from URL path segments
RankForge::jsonLd(
    BreadcrumbList::fromUrl('https://example.com/docs/getting-started/installation')
);
```

---

### 4. Article, BlogPosting, and NewsArticle

Boost visibility in Google Discover and News feeds:

```php
use Eamirgh\RankForge\Schema\Types\BlogPosting;
use Eamirgh\RankForge\Schema\Types\Organization;

RankForge::jsonLd(
    BlogPosting::make()
        ->headline('Scaling Laravel Applications to 100k RPS')
        ->description('Detailed architecture breakdown for high-throughput Laravel APIs.')
        ->image([
            'https://example.com/images/cover-16x9.jpg',
            'https://example.com/images/cover-4x3.jpg',
            'https://example.com/images/cover-1x1.jpg',
        ])
        ->datePublished('2025-01-15T08:00:00+00:00')
        ->dateModified('2025-01-20T14:30:00+00:00')
        ->author('Amir Ghafoori')
        ->publisher(
            Organization::make()
                ->name('Tech Daily')
                ->logo('https://example.com/logo.png')
        )
        ->mainEntityOfPage('https://example.com/blog/scaling-laravel')
);
```

---

### 5. Product & Offer (E-commerce)

Generate rich product snippets including prices, currency, availability, and customer reviews:

```php
use Eamirgh\RankForge\Schema\Types\Product;
use Eamirgh\RankForge\Schema\Types\Offer;

RankForge::jsonLd(
    Product::make()
        ->name('Smart Noise-Cancelling Headphones')
        ->description('Industry-leading active noise cancellation with 40-hour battery life.')
        ->image('https://example.com/products/headphones.jpg')
        ->sku('AUDIO-NC-400')
        ->mpn('987654321')
        ->brand('SonicSound')
        ->offers(
            Offer::make()
                ->price(249.99)
                ->priceCurrency('USD')
                ->availability('https://schema.org/InStock')
                ->priceValidUntil(now()->addMonths(6))
                ->url('https://example.com/products/headphones')
        )
        ->aggregateRating(
            ratingValue: 4.8,
            reviewCount: 412,
            bestRating: 5.0,
            worstRating: 1.0
        )
);
```

---

### 6. FAQPage

Produce expandable FAQ snippets directly in Google search results:

```php
use Eamirgh\RankForge\Schema\Types\FAQPage;

RankForge::jsonLd(
    FAQPage::make()
        ->addQuestion(
            'What versions of Laravel does RankForge support?',
            'RankForge supports Laravel 11.x, 12.x, and 13.x on PHP 8.2 or higher.'
        )
        ->addQuestion(
            'Does RankForge support Inertia.js?',
            'Yes, RankForge has built-in toArray() and toJson() serialization methods for Vue and React.'
        )
);
```

---

### 7. HowTo & SoftwareApplication

```php
use Eamirgh\RankForge\Schema\Types\HowTo;

RankForge::jsonLd(
    HowTo::make()
        ->name('How to Install RankForge')
        ->description('Quick installation guide for RankForge.')
        ->totalTime('PT5M')
        ->supply(['Composer', 'PHP 8.2+'])
        ->tool(['Terminal'])
        ->addStep('Require Package', 'Run composer require eamirgh/rankforge', 'https://example.com/step1')
        ->addStep('Publish Config', 'Run php artisan rankforge:install', 'https://example.com/step2')
);
```

---

## The Schema Graph Builder (@graph)

For complex pages requiring multiple linked entities, use the `Graph` builder to combine them into a single `@graph` JSON-LD structure:

```php
use Eamirgh\RankForge\Schema\Graph;
use Eamirgh\RankForge\Schema\Types\Organization;
use Eamirgh\RankForge\Schema\Types\WebSite;
use Eamirgh\RankForge\Schema\Types\Article;

$organization = Organization::make()
    ->name('Acme Inc')
    ->url('https://example.com')
    ->logo('https://example.com/logo.png');

$website = WebSite::make()
    ->name('Acme Blog')
    ->url('https://example.com/blog');

$article = Article::make()
    ->headline('Welcome to Acme Blog')
    ->publisher($organization);

// Combine into single @graph
$graph = Graph::make()
    ->add($organization)
    ->add($website)
    ->add($article);

RankForge::jsonLd($graph);
```

Output:
```html
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@graph": [
        { "@type": "Organization", "name": "Acme Inc", ... },
        { "@type": "WebSite", "name": "Acme Blog", ... },
        { "@type": "Article", "headline": "Welcome to Acme Blog", ... }
    ]
}
</script>
```
