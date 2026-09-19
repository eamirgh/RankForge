# Robots.txt & LLMs.txt (GEO)

RankForge equips Laravel applications with full control over both conventional search engine crawlers and modern Generative Engine Optimization (GEO) / AI agents.

---

## 1. robots.txt Management

RankForge serves dynamic `robots.txt` at `/robots.txt` based on your application environment and configuration.

### Environment Safety
- **Non-production environments** (`local`, `staging`, `testing`) automatically serve:
  ```txt
  User-agent: *
  Disallow: /
  ```
- **Production environments** serve your configured allow/disallow lists, user-agent directives, AI crawler policies, crawl delays, and sitemap indices.

### Production Directives Example
```txt
User-agent: *
Allow: /
Disallow: /admin
Disallow: /api/internal
Disallow: /telescope

User-agent: Googlebot
Allow: /
Disallow: /admin

# AI Crawlers
User-agent: GPTBot
Allow: /

User-agent: ClaudeBot
Allow: /

User-agent: CCBot
Disallow: /

Sitemap: https://example.com/sitemap.xml
```

---

## 2. llms.txt (Generative Engine Optimization)

The emerging `/llms.txt` standard provides curated, markdown-formatted summaries of your website specifically designed for Large Language Models (ChatGPT, Claude, Perplexity, Cursor, Copilot).

### Standard /llms.txt
Served at `/llms.txt`:
```markdown
# Acme Platform

> Enterprise Cloud Infrastructure for Modern Software Teams.

## Core Documentation
- [API Reference](https://example.com/docs/api): Complete REST and GraphQL endpoints.
- [Authentication](https://example.com/docs/auth): OAuth2 and Personal Access Tokens.
- [CLI Tool](https://example.com/docs/cli): Developer command line usage.

## Optional Details
- [Pricing](https://example.com/pricing): Team and Enterprise tiers.
- [Changelog](https://example.com/changelog): Version release notes.
```

### /llms-full.txt (Comprehensive Bundle)
Served at `/llms-full.txt`:
An unpaginated, consolidated Markdown file combining your full documentation, API specifications, or product guides into a single stream, optimized for LLM context windows.

---

## 3. Dynamic LLM Compilation

### Programmatic Definition
```php
use Eamirgh\RankForge\Facades\RankForge;

RankForge::llmsTxt()
    ->title('Acme Developer Docs')
    ->description('Official API specifications and guides.')
    ->addSection('Guides', 'Key implementation guides', [
        [
            'title' => 'Quickstart',
            'url' => 'https://example.com/docs/quickstart',
            'description' => 'Get up and running in 5 minutes',
        ],
        [
            'title' => 'Webhooks',
            'url' => 'https://example.com/docs/webhooks',
            'description' => 'Handling real-time event notifications',
        ],
    ])
    ->addDocument('Authentication Guide', '# Auth\n\nUse Bearer tokens.');
```

### ContentTransformer Utility
RankForge includes a `ContentTransformer` to strip HTML chrome, navigation, scripts, and CSS from rendered views, leaving clean semantic Markdown:

```php
use Eamirgh\RankForge\Crawlers\Transformers\ContentTransformer;

$cleanMarkdown = ContentTransformer::toMarkdown($rawHtml);
$plainText = ContentTransformer::toPlainText($rawHtml);
```

---

## 4. Pre-Compiling LLM Files

Compile physical `llms.txt` and `llms-full.txt` files to your `public/` directory:

```bash
php artisan rankforge:llms:generate
```
