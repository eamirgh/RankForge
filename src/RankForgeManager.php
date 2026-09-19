<?php

namespace Eamirgh\RankForge;

use DateTimeInterface;
use JsonSerializable;
use Eamirgh\RankForge\Crawlers\LlmsTxtManager;
use Eamirgh\RankForge\Crawlers\RobotsTxtManager;
use Eamirgh\RankForge\Meta\MetaTagsRenderer;
use Eamirgh\RankForge\Meta\OpenGraphRenderer;
use Eamirgh\RankForge\Meta\TwitterCardRenderer;
use Eamirgh\RankForge\Schema\Graph;
use Eamirgh\RankForge\Schema\SchemaManager;
use Eamirgh\RankForge\Schema\Types\AbstractType;
use Eamirgh\RankForge\Sitemap\IndexNow;
use Eamirgh\RankForge\Sitemap\SitemapManager;
use Eamirgh\RankForge\Support\Sanitizer;

class RankForgeManager implements JsonSerializable
{
    /** @var array<string, mixed> */
    protected array $config;

    protected ?SitemapManager $sitemapManager = null;

    protected ?RobotsTxtManager $robotsTxtManager = null;

    protected ?LlmsTxtManager $llmsTxtManager = null;

    protected ?IndexNow $indexNow = null;

    protected ?string $title = null;

    protected ?string $description = null;

    /** @var string[] */
    protected array $keywords = [];

    protected ?string $robots = null;

    /** @var array<string, string> */
    protected array $customRobots = [];

    protected ?string $canonicalUrl = null;

    /** @var array<string, mixed> */
    protected array $openGraph = [];

    /** @var array<string, mixed> */
    protected array $twitter = [];

    /** @var array<string, string> */
    protected array $hreflangEntries = [];

    protected mixed $model = null;

    protected ?string $modelImage = null;

    protected MetaTagsRenderer $metaTagsRenderer;

    protected OpenGraphRenderer $openGraphRenderer;

    protected TwitterCardRenderer $twitterCardRenderer;

    protected SchemaManager $schemaManager;

    public function __construct(array $config = [])
    {
        $this->config = $config;

        $this->metaTagsRenderer = new MetaTagsRenderer($this);
        $this->openGraphRenderer = new OpenGraphRenderer($this);
        $this->twitterCardRenderer = new TwitterCardRenderer($this);
        $this->schemaManager = new SchemaManager($this);
    }

    // -------------------------------------------------------------------------
    //  Title
    // -------------------------------------------------------------------------

    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Build the final rendered title string using the configured template.
     */
    public function getRenderedTitle(): string
    {
        $title = $this->title ?? $this->configGet('title.default', '');
        $siteName = $this->configGet('site_name', '');
        $separator = $this->configGet('title.separator', '|');
        $template = $this->configGet('title.template', '{title} {separator} {site_name}');
        $maxLength = (int) $this->configGet('title.max_length', 60);

        if ($title === '' && $siteName !== '') {
            return Sanitizer::text($siteName, $maxLength);
        }

        if ($title === '') {
            return '';
        }

        $rendered = str_replace(
            ['{title}', '{separator}', '{site_name}'],
            [$title, $separator, $siteName],
            $template,
        );

        return Sanitizer::text($rendered, $maxLength);
    }

    // -------------------------------------------------------------------------
    //  Description
    // -------------------------------------------------------------------------

    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): string
    {
        $description = $this->description ?? $this->configGet('description.default', '');
        $maxLength = (int) $this->configGet('description.max_length', 160);

        return Sanitizer::text($description, $maxLength);
    }

    // -------------------------------------------------------------------------
    //  Keywords
    // -------------------------------------------------------------------------

    /**
     * @param  array<string>|string  $keywords
     */
    public function keywords(array|string $keywords): static
    {
        if (is_string($keywords)) {
            $keywords = array_map('trim', explode(',', $keywords));
        }

        $this->keywords = array_values(array_unique(array_merge($this->keywords, $keywords)));

        return $this;
    }

    /**
     * @return string[]
     */
    public function getKeywords(): array
    {
        return array_merge(
            (array) $this->configGet('keywords', []),
            $this->keywords,
        );
    }

    public function getKeywordsString(): string
    {
        return Sanitizer::keywords($this->getKeywords());
    }

    // -------------------------------------------------------------------------
    //  Robots
    // -------------------------------------------------------------------------

    public function robots(string $robots): static
    {
        $this->robots = $robots;

        return $this;
    }

    public function noindex(): static
    {
        return $this->applyRobotsDirective('noindex', 'index');
    }

    public function nofollow(): static
    {
        return $this->applyRobotsDirective('nofollow', 'follow');
    }

    public function index(): static
    {
        return $this->applyRobotsDirective('index', 'noindex');
    }

    public function follow(): static
    {
        return $this->applyRobotsDirective('follow', 'nofollow');
    }

    public function noarchive(): static
    {
        return $this->applyRobotsDirective('noarchive', 'archive');
    }

    public function nosnippet(): static
    {
        $this->removeRobotsDirectivePrefix('max-snippet:');

        return $this->applyRobotsDirective('nosnippet', 'snippet');
    }

    public function maxSnippet(int $chars): static
    {
        $this->removeRobotsDirectivePrefix('max-snippet:');

        return $this->applyRobotsDirective("max-snippet:{$chars}", 'nosnippet');
    }

    public function maxImagePreview(string $size): static
    {
        $size = match (strtolower($size)) {
            'none', 'standard', 'large' => strtolower($size),
            default => 'standard',
        };

        $this->removeRobotsDirectivePrefix('max-image-preview:');

        return $this->applyRobotsDirective("max-image-preview:{$size}");
    }

    public function maxVideoPreview(int $seconds): static
    {
        $this->removeRobotsDirectivePrefix('max-video-preview:');

        return $this->applyRobotsDirective("max-video-preview:{$seconds}");
    }

    public function customRobots(string $name, string $content): static
    {
        $this->customRobots[$name] = $content;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getCustomRobots(): array
    {
        return $this->customRobots;
    }

    public function getRobots(): string
    {
        if ($this->robots !== null) {
            return $this->robots;
        }

        return $this->getDefaultRobots();
    }

    protected function getDefaultRobots(): string
    {
        $noindexEnvironments = (array) $this->configGet('robots.noindex_environments', []);

        if (function_exists('app') && in_array(app()->environment(), $noindexEnvironments, true)) {
            return 'noindex, nofollow';
        }

        return (string) $this->configGet('robots.default', 'index, follow');
    }

    // -------------------------------------------------------------------------
    //  Canonical URL
    // -------------------------------------------------------------------------

    public function canonical(string $url): static
    {
        $this->canonicalUrl = $url;

        return $this;
    }

    public function getCanonicalUrl(): ?string
    {
        if (! $this->configGet('canonical.enabled', true)) {
            return null;
        }

        $url = $this->canonicalUrl ?? (function_exists('request') ? request()->url() : '/');

        $stripParams = (array) $this->configGet('canonical.strip_query_params', []);

        if ($stripParams !== []) {
            $url = Sanitizer::stripQueryParams($url, $stripParams);
        }

        $trailingSlash = (bool) $this->configGet('canonical.trailing_slash', false);

        $parsed = parse_url($url);
        $path = $parsed['path'] ?? '/';

        if ($trailingSlash && $path !== '/' && ! str_ends_with($path, '/')) {
            $path .= '/';
        } elseif (! $trailingSlash && $path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        $canonical = ($parsed['scheme'] ?? 'https').'://'.($parsed['host'] ?? '');

        if (isset($parsed['port'])) {
            $canonical .= ':'.$parsed['port'];
        }

        $canonical .= $path;

        if (isset($parsed['query'])) {
            $canonical .= '?'.$parsed['query'];
        }

        return $canonical;
    }

    // -------------------------------------------------------------------------
    //  Open Graph
    // -------------------------------------------------------------------------

    public function ogTitle(string $title): static
    {
        $this->openGraph['title'] = $title;

        return $this;
    }

    public function ogDescription(string $description): static
    {
        $this->openGraph['description'] = $description;

        return $this;
    }

    public function ogImage(
        string $url,
        ?int $width = null,
        ?int $height = null,
        ?string $alt = null,
        ?string $type = null,
        ?string $secureUrl = null,
    ): static {
        $this->openGraph['image'] = $url;

        if ($width !== null) {
            $this->openGraph['image_width'] = $width;
        }

        if ($height !== null) {
            $this->openGraph['image_height'] = $height;
        }

        if ($alt !== null) {
            $this->openGraph['image_alt'] = $alt;
        }

        if ($type !== null) {
            $this->openGraph['image_type'] = $type;
        }

        if ($secureUrl !== null) {
            $this->openGraph['image_secure_url'] = $secureUrl;
        }

        return $this;
    }

    public function ogImageAlt(string $alt): static
    {
        $this->openGraph['image_alt'] = $alt;

        return $this;
    }

    public function ogImageType(string $type): static
    {
        $this->openGraph['image_type'] = $type;

        return $this;
    }

    public function ogImageSecureUrl(string $url): static
    {
        $this->openGraph['image_secure_url'] = $url;

        return $this;
    }

    public function ogLocaleAlternate(string|array $locales): static
    {
        $locales = is_array($locales) ? $locales : [$locales];
        $current = (array) ($this->openGraph['locale_alternate'] ?? []);
        $this->openGraph['locale_alternate'] = array_values(array_unique(array_merge($current, $locales)));

        return $this;
    }

    public function articlePublishedTime(string|DateTimeInterface $time): static
    {
        $this->openGraph['article:published_time'] = $this->formatDateTime($time);

        return $this;
    }

    public function articleModifiedTime(string|DateTimeInterface $time): static
    {
        $this->openGraph['article:modified_time'] = $this->formatDateTime($time);

        return $this;
    }

    public function articleAuthor(string|array $author): static
    {
        $this->openGraph['article:author'] = $author;

        return $this;
    }

    public function articleSection(string $section): static
    {
        $this->openGraph['article:section'] = $section;

        return $this;
    }

    public function articleTags(array|string $tags): static
    {
        if (is_string($tags)) {
            $tags = array_map('trim', explode(',', $tags));
        }

        $current = (array) ($this->openGraph['article:tag'] ?? []);
        $this->openGraph['article:tag'] = array_values(array_unique(array_merge($current, $tags)));

        return $this;
    }

    public function ogType(string $type): static
    {
        $this->openGraph['type'] = $type;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOpenGraph(): array
    {
        $defaults = [
            'type' => $this->configGet('open_graph.type', 'website'),
            'locale' => $this->configGet('open_graph.locale', 'en_US'),
        ];

        // Cascading fallback: View-specific OG Image -> Model featured image -> Category/Config default -> Global site fallback
        $resolvedImage = $this->openGraph['image']
            ?? $this->modelImage
            ?? $this->configGet('open_graph.image');

        if ($resolvedImage !== null) {
            $defaults['image'] = $resolvedImage;
            $defaults['image_width'] = $this->configGet('open_graph.image_width', 1200);
            $defaults['image_height'] = $this->configGet('open_graph.image_height', 630);
        }

        $og = array_merge($defaults, $this->openGraph);

        // Fallback to page title/description if not explicitly set
        if (! isset($og['title'])) {
            $og['title'] = $this->title ?? $this->configGet('title.default', '');
        }

        if (! isset($og['description'])) {
            $og['description'] = $this->getDescription();
        }

        $canonical = $this->getCanonicalUrl();
        if ($canonical !== null && ! isset($og['url'])) {
            $og['url'] = $canonical;
        }

        $siteName = $this->configGet('site_name');
        if ($siteName !== null && ! isset($og['site_name'])) {
            $og['site_name'] = $siteName;
        }

        return $og;
    }

    // -------------------------------------------------------------------------
    //  Twitter Card
    // -------------------------------------------------------------------------

    public function twitterCard(string $card): static
    {
        $this->twitter['card'] = $card;

        return $this;
    }

    public function twitterTitle(string $title): static
    {
        $this->twitter['title'] = $title;

        return $this;
    }

    public function twitterDescription(string $description): static
    {
        $this->twitter['description'] = $description;

        return $this;
    }

    public function twitterImage(string $url, ?string $alt = null): static
    {
        $this->twitter['image'] = $url;

        if ($alt !== null) {
            $this->twitter['image:alt'] = $alt;
        }

        return $this;
    }

    public function twitterImageAlt(string $alt): static
    {
        $this->twitter['image:alt'] = $alt;

        return $this;
    }

    public function twitterSite(string $site): static
    {
        $this->twitter['site'] = $site;

        return $this;
    }

    public function twitterCreator(string $creator): static
    {
        $this->twitter['creator'] = $creator;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getTwitter(): array
    {
        $defaults = [
            'card' => $this->configGet('twitter.card', 'summary_large_image'),
        ];

        $site = $this->configGet('twitter.site');
        if ($site !== null) {
            $defaults['site'] = $site;
        }

        $creator = $this->configGet('twitter.creator');
        if ($creator !== null) {
            $defaults['creator'] = $creator;
        }

        $tw = array_merge($defaults, $this->twitter);

        // Fallback to OG / page values
        if (! isset($tw['title'])) {
            $tw['title'] = $this->openGraph['title'] ?? $this->title ?? $this->configGet('title.default', '');
        }

        if (! isset($tw['description'])) {
            $tw['description'] = $this->openGraph['description'] ?? $this->getDescription();
        }

        if (! isset($tw['image'])) {
            $image = $this->openGraph['image']
                ?? $this->modelImage
                ?? $this->configGet('open_graph.image');

            if ($image !== null) {
                $tw['image'] = $image;
            }
        }

        if (! isset($tw['image:alt']) && isset($this->openGraph['image_alt'])) {
            $tw['image:alt'] = $this->openGraph['image_alt'];
        }

        return $tw;
    }

    // -------------------------------------------------------------------------
    //  Model Integration
    // -------------------------------------------------------------------------

    public function forModel(mixed $model): static
    {
        $this->model = $model;

        if (! is_object($model)) {
            return $this;
        }

        if (method_exists($model, 'hasSeo') && ! $model->hasSeo()) {
            return $this;
        }

        // Title
        if ($this->title === null) {
            $title = $this->resolveModelProperty($model, [
                'getSeoTitle', 'seo_title', 'title', 'name',
            ]);
            if ($title !== null && is_string($title)) {
                $this->title($title);
            }
        }

        // Description
        if ($this->description === null) {
            $description = $this->resolveModelProperty($model, [
                'getSeoDescription', 'seo_description', 'description', 'excerpt', 'summary',
            ]);
            if ($description !== null && is_string($description)) {
                $this->description($description);
            }
        }

        // Image
        $image = $this->resolveModelProperty($model, [
            'getSeoImage', 'seo_image', 'featured_image', 'image', 'cover_image', 'thumbnail',
        ]);
        if ($image !== null && is_string($image)) {
            $this->modelImage = $image;
        }

        // Canonical URL
        if ($this->canonicalUrl === null) {
            $canonical = $this->resolveModelProperty($model, [
                'getCanonicalUrl', 'canonical_url', 'getUrl', 'url',
            ]);
            if ($canonical !== null && is_string($canonical)) {
                $this->canonical($canonical);
            }
        }

        // JSON-LD structured data
        if (method_exists($model, 'toJsonLd')) {
            $jsonLd = $model->toJsonLd();
            if ($jsonLd instanceof AbstractType || $jsonLd instanceof Graph) {
                $this->schemaManager->add($jsonLd);
            } elseif (is_array($jsonLd) && $jsonLd !== []) {
                $this->schemaManager->addRaw($jsonLd);
            }
        }

        return $this;
    }

    /**
     * @param  string[]  $candidates
     */
    protected function resolveModelProperty(object $model, array $candidates): mixed
    {
        foreach ($candidates as $candidate) {
            if (method_exists($model, $candidate)) {
                $value = $model->{$candidate}();
                if ($value !== null && $value !== '') {
                    return $value;
                }
            }

            try {
                if (isset($model->{$candidate})) {
                    $value = $model->{$candidate};
                    if ($value !== null && $value !== '') {
                        return $value;
                    }
                }
            } catch (\Throwable) {
                // Ignore dynamic attribute retrieval errors
            }
        }

        return null;
    }

    // -------------------------------------------------------------------------
    //  JSON-LD & Schema
    // -------------------------------------------------------------------------

    public function jsonLd(AbstractType|Graph $schema): static
    {
        $this->schemaManager->add($schema);

        return $this;
    }

    public function schema(AbstractType|Graph $schema): static
    {
        return $this->jsonLd($schema);
    }

    public function graph(?Graph $graph = null): Graph
    {
        return $this->schemaManager->graph($graph);
    }

    public function getSchemaManager(): SchemaManager
    {
        return $this->schemaManager;
    }

    /**
     * @return array<AbstractType|Graph>
     */
    public function getJsonLdSchemas(): array
    {
        return $this->schemaManager->getSchemas();
    }

    public function isJsonLdEnabled(): bool
    {
        return (bool) $this->configGet('json_ld.enabled', true);
    }

    public function isJsonLdPrettyPrint(): bool
    {
        return (bool) $this->configGet('json_ld.pretty_print', false);
    }

    // -------------------------------------------------------------------------
    //  Hreflang
    // -------------------------------------------------------------------------

    public function xDefault(string $url): static
    {
        return $this->hreflang('x-default', $url);
    }

    public function hreflang(string $locale, string $url): static
    {
        $this->hreflangEntries[$locale] = $url;

        return $this;
    }

    /**
     * @param  array<string, string>  $locales
     */
    public function hreflangs(array $locales): static
    {
        foreach ($locales as $locale => $url) {
            $this->hreflang($locale, $url);
        }

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getHreflangEntries(): array
    {
        $entries = array_merge(
            (array) $this->configGet('hreflang.locales', []),
            $this->hreflangEntries,
        );

        $xDefault = $this->configGet('hreflang.x_default');
        if ($xDefault !== null && ! isset($entries['x-default'])) {
            $entries['x-default'] = $xDefault;
        }

        return $entries;
    }

    public function isHreflangEnabled(): bool
    {
        return $this->hreflangEntries !== [] || (bool) $this->configGet('hreflang.enabled', false);
    }

    // -------------------------------------------------------------------------
    //  Rendering
    // -------------------------------------------------------------------------

    /**
     * Render all SEO meta tags as a single HTML string.
     */
    public function renderHead(): string
    {
        $sections = array_filter([
            $this->metaTagsRenderer->render(),
            $this->openGraphRenderer->render(),
            $this->twitterCardRenderer->render(),
            $this->schemaManager->render(),
        ]);

        return implode(PHP_EOL, $sections);
    }

    /**
     * Return all meta data as a structured array (useful for Inertia / headless).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'title' => $this->getRenderedTitle(),
            'description' => $this->getDescription(),
            'robots' => $this->getRobots(),
        ];

        $customRobots = $this->getCustomRobots();
        if ($customRobots !== []) {
            $data['custom_robots'] = $customRobots;
        }

        $keywords = $this->getKeywords();
        if ($keywords !== []) {
            $data['keywords'] = $keywords;
        }

        $canonical = $this->getCanonicalUrl();
        if ($canonical !== null) {
            $data['canonical'] = $canonical;
        }

        if ($this->configGet('open_graph.enabled', true)) {
            $data['open_graph'] = $this->getOpenGraph();
        }

        if ($this->configGet('twitter.enabled', true)) {
            $data['twitter'] = $this->getTwitter();
        }

        if ($this->isJsonLdEnabled() && $this->schemaManager->hasSchemas()) {
            $data['json_ld'] = $this->schemaManager->toArray();
        }

        if ($this->isHreflangEnabled()) {
            $entries = $this->getHreflangEntries();
            if ($entries !== []) {
                $data['hreflang'] = $entries;
            }
        }

        return $data;
    }

    /**
     * Return all meta data as a JSON string.
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    // -------------------------------------------------------------------------
    //  Config Access
    // -------------------------------------------------------------------------

    /**
     * Read a value from the config array using dot notation.
     */
    public function configGet(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    // -------------------------------------------------------------------------
    //  Sitemap, Robots, LLMs, IndexNow
    // -------------------------------------------------------------------------

    public function sitemap(): SitemapManager
    {
        if ($this->sitemapManager === null) {
            $this->sitemapManager = function_exists('app') && app()->bound(SitemapManager::class)
                ? app(SitemapManager::class)
                : new SitemapManager($this->config);
        }

        return $this->sitemapManager;
    }

    public function robotsTxt(): RobotsTxtManager
    {
        if ($this->robotsTxtManager === null) {
            $this->robotsTxtManager = function_exists('app') && app()->bound(RobotsTxtManager::class)
                ? app(RobotsTxtManager::class)
                : new RobotsTxtManager($this->config);
        }

        return $this->robotsTxtManager;
    }

    public function llmsTxt(): LlmsTxtManager
    {
        if ($this->llmsTxtManager === null) {
            $this->llmsTxtManager = function_exists('app') && app()->bound(LlmsTxtManager::class)
                ? app(LlmsTxtManager::class)
                : new LlmsTxtManager($this->config);
        }

        return $this->llmsTxtManager;
    }

    public function indexNow(): IndexNow
    {
        if ($this->indexNow === null) {
            $this->indexNow = function_exists('app') && app()->bound(IndexNow::class)
                ? app(IndexNow::class)
                : new IndexNow($this->config);
        }

        return $this->indexNow;
    }

    // -------------------------------------------------------------------------
    //  Internal Helpers
    // -------------------------------------------------------------------------

    /**
     * Toggle a robots directive, removing the opposing one if present.
     */
    protected function applyRobotsDirective(string $add, ?string $remove = null): static
    {
        $current = $this->robots ?? $this->getDefaultRobots();

        $parts = array_filter(array_map('trim', explode(',', $current)));

        if ($remove !== null) {
            $parts = array_filter($parts, fn (string $part) => strtolower($part) !== strtolower($remove));
        }

        if (! in_array(strtolower($add), array_map('strtolower', $parts), true)) {
            $parts[] = $add;
        }

        $this->robots = implode(', ', array_values($parts));

        return $this;
    }

    protected function removeRobotsDirectivePrefix(string $prefix): static
    {
        $current = $this->robots ?? $this->getDefaultRobots();

        $parts = array_filter(array_map('trim', explode(',', $current)));
        $prefixLower = strtolower($prefix);

        $parts = array_filter($parts, fn (string $part) => ! str_starts_with(strtolower($part), $prefixLower));

        $this->robots = implode(', ', array_values($parts));

        return $this;
    }

    protected function formatDateTime(string|DateTimeInterface $time): string
    {
        if ($time instanceof DateTimeInterface) {
            return $time->format(DateTimeInterface::ATOM);
        }

        return $time;
    }
}
