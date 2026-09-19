# Agent Guidelines for RankForge Development

This document guides AI agents and developers contributing to **RankForge** (`eamirgh/rankforge`), an enterprise-grade SEO and Generative Engine Optimization (GEO) engine for Laravel.

---

## 1. Core Principles & The 3-Step Development Cycle

When adding features or modifying existing logic, **always follow this strict 3-step cycle**:

```
1. TEST FIRST (TDD) ──> 2. IMPLEMENT CODE ──> 3. DOCUMENT (VitePress)
```

1. **Test First**: Write a dedicated, failing test in `tests/Unit/` or `tests/Feature/` verifying the desired behavior.
2. **Implement Code**: Write the minimum, clean, strictly typed PHP code in `src/` to make the test pass.
3. **Document**: Update or create relevant documentation and runnable code examples in `docs/` (VitePress).

Never submit code without accompanying tests and documentation updates.

---

## 2. Package Architecture & Technology Stack

- **Package**: `eamirgh/rankforge`
- **Root Namespace**: `Eamirgh\RankForge\`
- **Test Namespace**: `Eamirgh\RankForge\Tests\`
- **Language**: PHP 8.2+ (strict types, match expressions, typed properties)
- **Supported Frameworks**: Laravel 11.x, 12.x, and 13.x
- **Test Framework**: Pest PHP / PHPUnit with Orchestra Testbench
- **Documentation**: VitePress (`docs/`)

### Directory Layout

```
rankforge/
├── config/
│   └── rankforge.php               # Central configuration defaults
├── src/
│   ├── RankForge.php               # Facade accessor class
│   ├── RankForgeManager.php        # Core orchestrator / fluent manager
│   ├── RankForgeServiceProvider.php# Laravel service provider & bindings
│   ├── Commands/                   # Artisan commands (generate, ping, check, install)
│   ├── Crawlers/                   # robots.txt, llms.txt, ContentTransformer
│   ├── Facades/                    # RankForge facade definition
│   ├── Http/Controllers/          # Dynamic HTTP endpoints (sitemap, robots, llms)
│   ├── Meta/                       # Dynamic Meta Tags renderers (Title, OG, Twitter, Canonical)
│   ├── Schema/                     # Strongly-typed Schema.org JSON-LD generators & @graph
│   │   ├── Concerns/               # HasJsonLd model trait
│   │   └── Types/                  # WebSite, Article, Product, FAQPage, etc.
│   ├── Sitemap/                    # XML sitemaps, chunking, IndexNow protocol
│   │   ├── Concerns/               # InvalidatesSitemapCache model trait
│   │   ├── Contracts/              # SitemapSource interface
│   │   └── Observers/              # SitemapObserver for Eloquent models
│   └── Support/                    # Sanitizer & string/URL utility helpers
├── routes/
│   └── web.php                     # Package HTTP routes (sitemap, robots, llms)
├── resources/views/                # Blade view templates
├── docs/                           # VitePress documentation
│   ├── .vitepress/config.mts       # VitePress sidebar & navigation config
│   ├── guide/                      # Core feature guides
│   └── advanced/                   # Headless, Inertia, Model integration
├── tests/
│   ├── TestCase.php                # Base Orchestra Testbench test case
│   ├── Unit/                       # Unit tests organized by domain
│   └── Feature/                    # Feature tests for HTTP routes and commands
├── benchmarks/
│   └── benchmark.php               # High-volume local performance benchmarks
└── Makefile                        # Standard developer & CI automation targets
```

---

## 3. Step 1: Testing Guidelines (Test First)

### Where to Add Tests

- **Unit tests**: `tests/Unit/<Domain>/<Feature>Test.php`
  - `Meta/`: Titles, descriptions, robots, canonicals, hreflang, OpenGraph, Twitter cards.
  - `Schema/`: JSON-LD schema types, Graph, model traits.
  - `Sitemap/`: URL DTO, Index, Manager, chunking, IndexNow, observers.
  - `Crawlers/`: `robots.txt`, `llms.txt`, `ContentTransformer`.
  - `Support/`: `Sanitizer`.
- **Feature tests**: `tests/Feature/<Domain>/<Feature>Test.php`
  - `Http/`: Routes and HTTP controller responses (`sitemap.xml`, `robots.txt`, `llms.txt`).
  - `Commands/`: Artisan commands (`rankforge:*`).
  - `Headless/`: Inertia / JSON API serialization.

### Test Rules & Conventions

1. Extend `Eamirgh\RankForge\Tests\TestCase`.
2. Use descriptive `it_*` method names that describe the expected behavior:
   ```php
   public function test_it_truncates_title_to_configured_maximum_length(): void
   ```
3. Test edge cases: empty strings, null values, malicious inputs, character length boundaries, and multi-byte unicode strings.
4. Run tests frequently using:
   ```bash
   make test
   # or specific suite:
   make test-unit
   make test-feature
   ```

---

## 4. Step 2: Implementation Guidelines (Code)

### General Rules

1. **Stdlib & Native First**: Prefer PHP built-ins and native Laravel features over external dependencies.
2. **Memory Efficiency**: Always use generators (`yield`), Eloquent cursors (`cursor()`), or lazy collections (`lazy()`) when processing large datasets (such as 50,000+ sitemap URLs).
3. **Fluent Chaining**: Setter methods in `RankForgeManager` and Schema types must return `$this` to support chaining.
4. **Fallback Cascading**: Respect the fallback hierarchy:
   - View / explicit setter override -> Model getter -> Model attribute -> Global config default.
5. **Sanitization**: All user-supplied or model-extracted strings must pass through `Eamirgh\RankForge\Support\Sanitizer` to strip HTML, normalize whitespace, and escape attributes.
6. **No Breaking Changes**: Maintain backward compatibility with existing facade and configuration signatures.

### Adding a New Schema Type

When adding a new Schema.org type:
1. Create `src/Schema/Types/<TypeName>.php` extending `AbstractType`.
2. Implement static `make()` and fluent property setters.
3. Handle nested Schema types and arrays in `toArray()`.
4. Add a unit test in `tests/Unit/Schema/<TypeName>SchemaTest.php`.

### Adding an Artisan Command

When adding a new Artisan command:
1. Create `src/Commands/<CommandName>.php`.
2. Register in `RankForgeServiceProvider.php` under `$this->commands([...])`.
3. Add a feature test in `tests/Feature/Commands/<CommandName>Test.php`.

---

## 5. Step 3: Documentation Guidelines (VitePress)

Every new feature or modified behavior **must** be reflected in `docs/`:

1. **Identify the Target Guide**:
   - Dynamic Meta Tags -> `docs/guide/dynamic-meta-tags.md`
   - Structured Data -> `docs/guide/json-ld-schemas.md`
   - Sitemaps & IndexNow -> `docs/guide/xml-sitemaps.md`
   - Robots & LLMs.txt -> `docs/guide/crawlers-and-llms-txt.md`
   - Commands -> `docs/guide/artisan-commands.md`
   - Headless / Inertia -> `docs/advanced/headless-and-inertia.md`
   - Model Integration -> `docs/advanced/model-integration.md`
2. **If adding a new page**:
   - Create `docs/<section>/<page-name>.md`.
   - Update `docs/.vitepress/config.mts` to add the page to `nav` and `sidebar`.
3. **Provide Runnable Code Examples**:
   - Always include realistic, copy-pasteable PHP and Blade/Vue/React snippets.
4. **Verify Documentation Build**:
   ```bash
   make docs-build
   ```
   Ensure the build completes with zero errors or broken links.

---

## 6. Developer & Automation Commands

| Command | Purpose |
|---|---|
| `make test` | Runs the full Pest test suite (all unit and feature tests) |
| `make test-unit` | Runs only unit tests (`tests/Unit`) |
| `make test-feature` | Runs only feature tests (`tests/Feature`) |
| `make benchmark` | Runs local performance benchmark suite (`benchmarks/benchmark.php`) |
| `make docs-dev` | Starts local VitePress development server |
| `make docs-build` | Builds production-ready VitePress static documentation |
| `make docs-preview` | Previews built VitePress documentation locally |
| `make clean` | Cleans `.phpunit.cache`, `docs/.vitepress/dist`, and temporary caches |

---

## 7. Pre-Submission Checklist

Before finalizing any contribution, confirm:

- [ ] New or modified code has test coverage in `tests/`.
- [ ] All 130+ tests pass (`make test`).
- [ ] Benchmarks pass without performance regression (`make benchmark`).
- [ ] Documentation is updated in `docs/`.
- [ ] VitePress build succeeds without broken links (`make docs-build`).
- [ ] No unwanted debug statements, `dd()`, `dump()`, or temporary files remain.
