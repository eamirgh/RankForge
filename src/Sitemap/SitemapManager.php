<?php

namespace Eamirgh\RankForge\Sitemap;

use Closure;
use DateTimeInterface;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;
use InvalidArgumentException;
use Eamirgh\RankForge\Sitemap\Contracts\SitemapSource;

class SitemapManager
{
    /** @var array<string, mixed> */
    protected array $config;

    /** @var array<string, array{source: mixed, transformer: ?callable}> */
    protected array $sources = [];

    protected ?CacheRepository $cache = null;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->bootConfiguredSources();
    }

    protected function bootConfiguredSources(): void
    {
        $sources = (array) ($this->config['sitemap']['sources'] ?? []);

        foreach ($sources as $key => $source) {
            $name = is_string($key) ? $key : class_basename($source);
            $name = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $name));
            $this->register($name, $source);
        }
    }

    public function register(string $name, mixed $source, ?callable $transformer = null): static
    {
        $this->sources[$name] = [
            'source' => $source,
            'transformer' => $transformer,
        ];

        return $this;
    }

    /**
     * @return array<string, array{source: mixed, transformer: ?callable}>
     */
    public function getSources(): array
    {
        return $this->sources;
    }

    public function hasSource(string $name): bool
    {
        return isset($this->sources[$name]);
    }

    public function renderIndex(): string
    {
        $ttl = (int) ($this->config['sitemap']['cache_ttl'] ?? 3600);
        $cacheKey = 'rankforge.sitemap.index';

        if ($ttl > 0 && $this->getCache()->has($cacheKey)) {
            return (string) $this->getCache()->get($cacheKey);
        }

        $index = new SitemapIndex();
        $maxUrls = (int) ($this->config['sitemap']['max_urls'] ?? 50000);

        foreach ($this->sources as $name => $definition) {
            $total = $this->countSourceItems($definition['source']);
            $pages = max(1, (int) ceil($total / $maxUrls));

            for ($page = 1; $page <= $pages; $page++) {
                $loc = $this->generateSectionUrl($name, $page);
                $index->addSitemap($loc, now());
            }
        }

        $xml = $index->toXml();

        if ($ttl > 0) {
            $this->getCache()->put($cacheKey, $xml, $ttl);
        }

        return $xml;
    }

    public function renderSection(string $name, int $page = 1): string
    {
        if (! $this->hasSource($name)) {
            throw new InvalidArgumentException("Sitemap section [{$name}] is not registered.");
        }

        if ($page < 1) {
            $page = 1;
        }

        $ttl = (int) ($this->config['sitemap']['cache_ttl'] ?? 3600);
        $cacheKey = "rankforge.sitemap.{$name}.{$page}";

        if ($ttl > 0 && $this->getCache()->has($cacheKey)) {
            return (string) $this->getCache()->get($cacheKey);
        }

        $maxUrls = (int) ($this->config['sitemap']['max_urls'] ?? 50000);
        $definition = $this->sources[$name];
        $items = $this->getSourceItems($definition['source'], $page, $maxUrls);

        $urlsXml = '';
        foreach ($items as $item) {
            $sitemapUrl = $this->resolveToSitemapUrl($item, $definition['transformer']);
            if ($sitemapUrl !== null) {
                $urlsXml .= $sitemapUrl->toXml()."\n";
            }
        }

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"\n";
        $xml .= "        xmlns:image=\"http://www.google.com/schemas/sitemap-image/1.1\"\n";
        $xml .= "        xmlns:video=\"http://www.google.com/schemas/sitemap-video/1.1\"\n";
        $xml .= "        xmlns:news=\"http://www.google.com/schemas/sitemap-news/0.9\"\n";
        $xml .= "        xmlns:xhtml=\"http://www.w3.org/1999/xhtml\">\n";
        $xml .= $urlsXml;
        $xml .= '</urlset>';

        if ($ttl > 0) {
            $this->getCache()->put($cacheKey, $xml, $ttl);
        }

        return $xml;
    }

    /**
     * @return array<string>
     */
    public function writeToDisk(string $disk = 'public', string $path = 'sitemaps'): array
    {
        $storage = Storage::disk($disk);
        $path = trim($path, '/');
        $writtenFiles = [];

        // Write index
        $indexXml = $this->renderIndex();
        $indexPath = $path === '' ? 'sitemap.xml' : "{$path}/sitemap.xml";
        $storage->put($indexPath, $indexXml);
        $writtenFiles[] = $indexPath;

        // Write child sections
        $maxUrls = (int) ($this->config['sitemap']['max_urls'] ?? 50000);
        foreach ($this->sources as $name => $definition) {
            $total = $this->countSourceItems($definition['source']);
            $pages = max(1, (int) ceil($total / $maxUrls));

            for ($page = 1; $page <= $pages; $page++) {
                $sectionXml = $this->renderSection($name, $page);
                $sectionPath = $path === ''
                    ? "sitemap-{$name}-{$page}.xml"
                    : "{$path}/sitemap-{$name}-{$page}.xml";
                $storage->put($sectionPath, $sectionXml);
                $writtenFiles[] = $sectionPath;
            }
        }

        return $writtenFiles;
    }

    public function clearCache(): void
    {
        $cache = $this->getCache();
        $cache->forget('rankforge.sitemap.index');

        $maxUrls = (int) ($this->config['sitemap']['max_urls'] ?? 50000);
        foreach ($this->sources as $name => $definition) {
            $total = $this->countSourceItems($definition['source']);
            $pages = max(1, (int) ceil($total / $maxUrls));
            for ($page = 1; $page <= $pages; $page++) {
                $cache->forget("rankforge.sitemap.{$name}.{$page}");
            }
        }
    }

    protected function generateSectionUrl(string $name, int $page): string
    {
        if (function_exists('route') && app('router')->has('rankforge.sitemap.show')) {
            return route('rankforge.sitemap.show', ['section' => $name, 'page' => $page]);
        }

        $baseUrl = function_exists('url') ? url('/') : 'http://localhost';

        return rtrim($baseUrl, '/')."/sitemap-{$name}-{$page}.xml";
    }

    protected function countSourceItems(mixed $source): int
    {
        if (is_string($source) && class_exists($source)) {
            $instance = new $source();
            if ($instance instanceof Model) {
                return $source::query()->count();
            }
            if ($instance instanceof SitemapSource) {
                $items = $instance->toSitemap();

                return is_countable($items) ? count($items) : iterator_count($items);
            }
        }

        if ($source instanceof EloquentBuilder) {
            return (clone $source)->count();
        }

        if ($source instanceof Model) {
            return $source::query()->count();
        }

        if ($source instanceof SitemapSource) {
            $items = $source->toSitemap();

            return is_countable($items) ? count($items) : iterator_count($items);
        }

        if ($source instanceof Closure) {
            $result = $source();
            if ($result instanceof EloquentBuilder) {
                return (clone $result)->count();
            }
            if (is_countable($result)) {
                return count($result);
            }
            if ($result instanceof \Traversable) {
                return iterator_count($result);
            }

            return 0;
        }

        if (is_countable($source)) {
            return count($source);
        }

        if ($source instanceof \Traversable) {
            return iterator_count($source);
        }

        return 0;
    }

    /**
     * @return iterable<mixed>
     */
    protected function getSourceItems(mixed $source, int $page, int $perPage): iterable
    {
        $offset = ($page - 1) * $perPage;

        if (is_string($source) && class_exists($source)) {
            $instance = new $source();
            if ($instance instanceof Model) {
                return $source::query()->offset($offset)->limit($perPage)->cursor();
            }
            if ($instance instanceof SitemapSource) {
                return $this->sliceIterable($instance->toSitemap(), $offset, $perPage);
            }
        }

        if ($source instanceof EloquentBuilder) {
            return (clone $source)->offset($offset)->limit($perPage)->cursor();
        }

        if ($source instanceof Model) {
            return $source::query()->offset($offset)->limit($perPage)->cursor();
        }

        if ($source instanceof SitemapSource) {
            return $this->sliceIterable($source->toSitemap(), $offset, $perPage);
        }

        if ($source instanceof Closure) {
            $result = $source();
            if ($result instanceof EloquentBuilder) {
                return (clone $result)->offset($offset)->limit($perPage)->cursor();
            }
            if (is_iterable($result)) {
                return $this->sliceIterable($result, $offset, $perPage);
            }
        }

        if (is_iterable($source)) {
            return $this->sliceIterable($source, $offset, $perPage);
        }

        return [];
    }

    /**
     * @param iterable<mixed> $iterable
     * @return iterable<mixed>
     */
    protected function sliceIterable(iterable $iterable, int $offset, int $limit): iterable
    {
        if ($iterable instanceof Collection) {
            return $iterable->slice($offset, $limit);
        }

        if ($iterable instanceof LazyCollection) {
            return $iterable->skip($offset)->take($limit);
        }

        if (is_array($iterable)) {
            return array_slice($iterable, $offset, $limit);
        }

        return $this->yieldSlice($iterable, $offset, $limit);
    }

    /**
     * @param iterable<mixed> $iterable
     * @return \Generator<mixed>
     */
    protected function yieldSlice(iterable $iterable, int $offset, int $limit): \Generator
    {
        $count = 0;
        $yielded = 0;
        foreach ($iterable as $item) {
            if ($count >= $offset && $yielded < $limit) {
                yield $item;
                $yielded++;
            }
            $count++;
            if ($yielded >= $limit) {
                break;
            }
        }
    }

    protected function resolveToSitemapUrl(mixed $item, ?callable $transformer = null): ?SitemapUrl
    {
        if ($transformer !== null) {
            $transformed = $transformer($item);
            if ($transformed instanceof SitemapUrl) {
                return $transformed;
            }
            $item = $transformed;
        }

        if ($item instanceof SitemapUrl) {
            return $item;
        }

        if (is_object($item) && method_exists($item, 'toSitemapUrl')) {
            $result = $item->toSitemapUrl();
            if ($result instanceof SitemapUrl) {
                return $result;
            }
        }

        if (is_string($item)) {
            return SitemapUrl::make($item);
        }

        if (is_array($item) && isset($item['loc'])) {
            $url = SitemapUrl::make($item['loc']);
            if (isset($item['lastmod'])) {
                $url->lastmod($item['lastmod']);
            }
            if (isset($item['changefreq'])) {
                $url->changefreq($item['changefreq']);
            }
            if (isset($item['priority'])) {
                $url->priority((float) $item['priority']);
            }
            if (! empty($item['images'])) {
                foreach ($item['images'] as $img) {
                    $url->addImage($img);
                }
            }
            if (! empty($item['videos'])) {
                foreach ($item['videos'] as $vid) {
                    $url->addVideo($vid);
                }
            }
            if (! empty($item['alternates'])) {
                foreach ($item['alternates'] as $alt) {
                    $url->addAlternate($alt);
                }
            }

            return $url;
        }

        if (is_object($item)) {
            $loc = $item->url ?? (method_exists($item, 'getUrl') ? $item->getUrl() : null);
            if ($loc !== null && is_string($loc)) {
                $url = SitemapUrl::make($loc);
                $lastmod = $item->updated_at ?? $item->created_at ?? null;
                if ($lastmod instanceof DateTimeInterface || is_string($lastmod)) {
                    $url->lastmod($lastmod);
                }

                return $url;
            }
        }

        return null;
    }

    protected function getCache(): CacheRepository
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        return Cache::store();
    }

    public function setCache(CacheRepository $cache): static
    {
        $this->cache = $cache;

        return $this;
    }
}
