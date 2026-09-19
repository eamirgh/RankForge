# Dynamic Meta Tags Engine

The Dynamic Meta Tags Engine gives you complete control over HTML `<head>` metadata dynamically across controllers, views, routes, and Eloquent models.

---

## 1. Page Title Management

RankForge manages page titles using configurable templates, custom separators, and character length enforcement.

```php
use Eamirgh\RankForge\Facades\RankForge;

// Standard title
RankForge::title('High Performance Cloud Hosting');
// Rendered output:
// <title>High Performance Cloud Hosting | Acme Cloud</title>
```

### Title Templating & Separators
You can customize the template in `config/rankforge.php`:
```php
'title' => [
    'separator' => '—', // '|', '—', '•', '-'
    'template' => '{title} {separator} {site_name}',
    'max_length' => 60,
],
```

### Truncation Behavior
If a title exceeds `max_length` (default: 60 characters), RankForge automatically truncates the string cleanly at the last word boundary and appends an ellipsis (`...`), preventing awkward cuts in Google search result snippets.

---

## 2. Meta Descriptions & Keywords

```php
RankForge::description('Deploy scalable Laravel microservices with one click. 99.99% uptime SLA guaranteed.')
    ->keywords(['laravel', 'cloud hosting', 'php 8.4', 'microservices']);
```

- **Descriptions** are stripped of any HTML tags, whitespace-normalized, and truncated to 160 characters at word boundaries.
- **Keywords** can be supplied as a PHP array or a comma-separated string. RankForge merges and deduplicates keywords with the default configured keywords:

```php
RankForge::keywords('laravel, redis, queue worker');
```

---

## 3. Robots Directives

Control how search engines crawl, index, and display snippets for your pages.

### Directives API
```php
// Standard indexing controls
RankForge::index();     // Enables indexing
RankForge::noindex();   // Disables indexing
RankForge::follow();    // Follows links
RankForge::nofollow();  // Does not follow links

// Specialized crawler hints
RankForge::noarchive(); // Prevents search engines from caching the page
RankForge::nosnippet();  // Prevents showing text snippets in search results

// Advanced snippet controls
RankForge::maxSnippet(160);              // Limits snippet length in characters (-1 for unlimited)
RankForge::maxImagePreview('large');     // 'none', 'standard', 'large'
RankForge::maxVideoPreview(30);          // Max video preview in seconds (-1 for unlimited)

// Custom crawler-specific directives
RankForge::customRobots('googlebot', 'max-snippet:100, noarchive');
RankForge::customRobots('bingbot', 'noindex');
```

### Environment Automation
RankForge prevents test and staging leaks out of the box. In `local`, `testing`, or `staging` environments, RankForge automatically enforces:
```html
<meta name="robots" content="noindex, nofollow">
```
unless explicitly overridden.

---

## 4. Canonical & Alternative URLs

Canonical URLs tell search engines which URL represents the master copy of a page, preventing duplicate content penalties.

```php
RankForge::canonical('https://example.com/products/headphones?utm_source=fb&gclid=12345');
// Output:
// <link rel="canonical" href="https://example.com/products/headphones">
```

### Automatic Query Parameter Stripping
RankForge automatically strips tracking query parameters configured in `canonical.strip_query_params`:
- `utm_source`, `utm_medium`, `utm_campaign`, `utm_term`, `utm_content`
- `fbclid`, `gclid`, `msclkid`
- `mc_cid`, `mc_eid`, `ref`

### Trailing Slash Handling
Configure `canonical.trailing_slash` to `true` or `false` to strictly enforce consistent URL structure across your site.

---

## 5. Hreflang & Internationalization (i18n)

Support multi-language and multi-regional websites with standard `<link rel="alternate" hreflang="...">` tags:

```php
RankForge::hreflang('en-US', 'https://example.com/en-us/pricing')
    ->hreflang('en-GB', 'https://example.com/en-gb/pricing')
    ->hreflang('es-ES', 'https://example.com/es/precios')
    ->hreflang('fr-FR', 'https://example.com/fr/tarifs')
    ->xDefault('https://example.com/pricing');
```

Rendered output:
```html
<link rel="alternate" hreflang="en-US" href="https://example.com/en-us/pricing">
<link rel="alternate" hreflang="en-GB" href="https://example.com/en-gb/pricing">
<link rel="alternate" hreflang="es-ES" href="https://example.com/es/precios">
<link rel="alternate" hreflang="fr-FR" href="https://example.com/fr/tarifs">
<link rel="alternate" hreflang="x-default" href="https://example.com/pricing">
```

---

## 6. Open Graph (Social Graph)

RankForge outputs comprehensive Open Graph tags compatible with Facebook, LinkedIn, Pinterest, and Slack:

```php
RankForge::ogTitle('RankForge v1.0 Released')
    ->ogDescription('Explore the next-generation SEO engine for Laravel.')
    ->ogType('article')
    ->ogImage(
        url: 'https://example.com/images/og-release.jpg',
        width: 1200,
        height: 630,
        alt: 'RankForge Announcement Banner',
        type: 'image/jpeg',
        secureUrl: 'https://example.com/images/og-release.jpg'
    )
    ->ogLocaleAlternate(['es_ES', 'fr_FR', 'de_DE'])
    ->articlePublishedTime(now()->subHours(4))
    ->articleModifiedTime(now())
    ->articleAuthor(['Amir Ghaffari', 'Laravel Team'])
    ->articleSection('Announcements')
    ->articleTags(['laravel', 'release', 'seo']);
```

---

## 7. Twitter / X Cards

RankForge provides full support for Twitter/X cards:

```php
RankForge::twitterCard('summary_large_image') // 'summary', 'summary_large_image', 'app', 'player'
    ->twitterSite('@rankforge')
    ->twitterCreator('@eamirgh')
    ->twitterTitle('RankForge v1.0 Released')
    ->twitterDescription('Enterprise SEO engine for modern Laravel.')
    ->twitterImage('https://example.com/images/twitter.jpg', alt: 'Feature Banner');
```

### Fallback Cascading
If you do not explicitly define Twitter tags, RankForge automatically falls back to:
1. Open Graph values (`og:title`, `og:description`, `og:image`)
2. Page title and description
3. Global configuration defaults
