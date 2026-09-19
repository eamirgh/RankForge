<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Eamirgh\RankForge\Crawlers\LlmsTxtManager;
use Eamirgh\RankForge\Crawlers\RobotsTxtManager;
use Eamirgh\RankForge\Crawlers\Transformers\ContentTransformer;
use Eamirgh\RankForge\Mcp\McpServer;
use Eamirgh\RankForge\RankForgeManager;
use Eamirgh\RankForge\RankForgeServiceProvider;
use Eamirgh\RankForge\Schema\Concerns\HasJsonLd;
use Eamirgh\RankForge\Schema\Graph;
use Eamirgh\RankForge\Schema\Types\BreadcrumbList;
use Eamirgh\RankForge\Schema\Types\LocalBusiness;
use Eamirgh\RankForge\Schema\Types\Offer;
use Eamirgh\RankForge\Schema\Types\Product;
use Eamirgh\RankForge\Schema\Types\WebSite;
use Eamirgh\RankForge\Sitemap\SitemapManager;
use Eamirgh\RankForge\Sitemap\SitemapUrl;
use Eamirgh\RankForge\Support\Sanitizer;
use Illuminate\Database\Eloquent\Model;
use Orchestra\Testbench\Concerns\CreatesApplication;

class BenchmarkModel extends Model
{
    use HasJsonLd;

    protected $guarded = [];

    public function getSeoTitle(): string
    {
        return $this->custom_title ?? $this->title;
    }

    public function getSeoImage(): string
    {
        return 'https://example.com/images/featured.jpg';
    }
}

class BenchmarkRunner
{
    use CreatesApplication;

    public function __construct()
    {
        $app = $this->createApplication();
        $app->register(RankForgeServiceProvider::class);
    }

    protected function getPackageProviders($app)
    {
        return [RankForgeServiceProvider::class];
    }

    public function run(): void
    {
        echo "===============================================================\n";
        echo " RankForge Comprehensive Benchmark Suite\n";
        echo " PHP Version : " . PHP_VERSION . "\n";
        echo " Date        : " . date('Y-m-d H:i:s') . "\n";
        echo "===============================================================\n\n";

        $this->benchmarkMetaTagsRendering(5000);
        $this->benchmarkJsonLdComplexGraph(5000);
        $this->benchmarkBreadcrumbUrlParsing(10000);
        $this->benchmarkSitemapGeneration(50000);
        $this->benchmarkCanonicalUrlFiltering(10000);
        $this->benchmarkRobotsTxtGeneration(5000);
        $this->benchmarkLlmsTxtCompilation(2000);
        $this->benchmarkContentTransformer(1000);
        $this->benchmarkModelIntegrationFallback(5000);
        $this->benchmarkHeadlessSerialization(5000);
        $this->benchmarkMcpServerJsonRpc(5000);

        echo "===============================================================\n";
        echo " All 11 benchmarks completed successfully.\n";
        echo "===============================================================\n";
    }

    // 1. Meta Tags Engine
    protected function benchmarkMetaTagsRendering(int $iterations): void
    {
        $config = [
            'site_name' => 'Acme Enterprise',
            'title' => ['separator' => '|', 'template' => '{title} {separator} {site_name}', 'max_length' => 60],
            'description' => ['max_length' => 160],
            'canonical' => ['enabled' => true, 'strip_query_params' => ['utm_source', 'fbclid']],
            'open_graph' => ['enabled' => true],
            'twitter' => ['enabled' => true],
        ];

        $startMemory = memory_get_usage(true);
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $manager = new RankForgeManager($config);
            $manager->title("Product Launch #{$i}")
                ->description("Explore the cutting-edge features in version {$i} with enterprise scalability.")
                ->canonical("https://example.com/products/{$i}?utm_source=twitter&fbclid=xyz")
                ->keywords(['laravel', 'seo', 'benchmark', 'enterprise'])
                ->ogImage("https://example.com/images/{$i}.jpg", 1200, 630, "Banner {$i}")
                ->twitterCard('summary_large_image')
                ->twitterSite('@acme')
                ->articlePublishedTime('2025-01-01T00:00:00Z');

            $html = $manager->renderHead();
        }

        $duration = microtime(true) - $startTime;
        $peakMemory = (memory_get_peak_usage(true) - $startMemory) / 1024 / 1024;
        $opsSec = round($iterations / $duration);

        printf("1. Meta Tags Engine (renderHead)\n");
        printf("   Iterations  : %d\n", $iterations);
        printf("   Total Time  : %.4f s\n", $duration);
        printf("   Throughput  : %s ops/sec\n", number_format($opsSec));
        printf("   Avg Latency : %.4f ms/op\n", ($duration / $iterations) * 1000);
        printf("   Memory Peak : %.2f MB\n\n", $peakMemory);
    }

    // 2. JSON-LD Complex @graph
    protected function benchmarkJsonLdComplexGraph(int $iterations): void
    {
        $startMemory = memory_get_usage(true);
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $graph = Graph::make()
                ->add(
                    WebSite::make()
                        ->name("Site {$i}")
                        ->url("https://example.com/{$i}")
                        ->searchAction("https://example.com/search?q={q}")
                )
                ->add(
                    LocalBusiness::make()
                        ->name("HQ {$i}")
                        ->telephone("+1-800-555-{$i}")
                        ->geo(37.7749, -122.4194)
                )
                ->add(
                    Product::make()
                        ->name("Gadget {$i}")
                        ->sku("SKU-{$i}")
                        ->offers(
                            Offer::make()
                                ->price(99.99)
                                ->priceCurrency('USD')
                                ->availability('https://schema.org/InStock')
                        )
                );

            $json = json_encode($graph->toArray(), JSON_UNESCAPED_SLASHES);
        }

        $duration = microtime(true) - $startTime;
        $peakMemory = (memory_get_peak_usage(true) - $startMemory) / 1024 / 1024;
        $opsSec = round($iterations / $duration);

        printf("2. JSON-LD Complex @graph Construction & Serialization\n");
        printf("   Iterations  : %d\n", $iterations);
        printf("   Total Time  : %.4f s\n", $duration);
        printf("   Throughput  : %s ops/sec\n", number_format($opsSec));
        printf("   Avg Latency : %.4f ms/op\n", ($duration / $iterations) * 1000);
        printf("   Memory Peak : %.2f MB\n\n", $peakMemory);
    }

    // 3. Breadcrumb URL Parsing
    protected function benchmarkBreadcrumbUrlParsing(int $iterations): void
    {
        $startMemory = memory_get_usage(true);
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $breadcrumbs = BreadcrumbList::make()->fromUrl("https://example.com/catalog/electronics/smartphones/device-{$i}");
            $array = $breadcrumbs->toArray();
        }

        $duration = microtime(true) - $startTime;
        $peakMemory = (memory_get_peak_usage(true) - $startMemory) / 1024 / 1024;
        $opsSec = round($iterations / $duration);

        printf("3. BreadcrumbList Auto-Generation from URL Segments\n");
        printf("   Iterations  : %d\n", $iterations);
        printf("   Total Time  : %.4f s\n", $duration);
        printf("   Throughput  : %s ops/sec\n", number_format($opsSec));
        printf("   Avg Latency : %.4f ms/op\n", ($duration / $iterations) * 1000);
        printf("   Memory Peak : %.2f MB\n\n", $peakMemory);
    }

    // 4. High-Volume XML Sitemap
    protected function benchmarkSitemapGeneration(int $urlCount): void
    {
        $startMemory = memory_get_usage(true);
        $startTime = microtime(true);

        $manager = new SitemapManager(['max_urls' => 50000]);
        $manager->register('large_catalog', function () use ($urlCount) {
            for ($i = 1; $i <= $urlCount; $i++) {
                yield SitemapUrl::make("https://example.com/items/{$i}")
                    ->lastmod('2025-01-01')
                    ->changefreq('daily')
                    ->priority(0.8)
                    ->image("https://example.com/img/{$i}.jpg", "Item {$i}");
            }
        });

        $xml = $manager->renderSection('large_catalog', 1);

        $duration = microtime(true) - $startTime;
        $memoryUsed = (memory_get_usage(true) - $startMemory) / 1024 / 1024;
        $peakMemory = (memory_get_peak_usage(true) - $startMemory) / 1024 / 1024;

        printf("4. High-Volume XML Sitemap Generation (Streaming Generator)\n");
        printf("   URL Count   : %s URLs (with Image Extension)\n", number_format($urlCount));
        printf("   XML Size    : %.2f MB\n", strlen($xml) / 1024 / 1024);
        printf("   Total Time  : %.4f s\n", $duration);
        printf("   Throughput  : %s URLs/sec\n", number_format(round($urlCount / $duration)));
        printf("   Memory Delta: %.2f MB\n", $memoryUsed);
        printf("   Peak Memory : %.2f MB\n\n", $peakMemory);
    }

    // 5. Canonical URL Filtering
    protected function benchmarkCanonicalUrlFiltering(int $iterations): void
    {
        $stripParams = ['utm_*', 'fbclid', 'gclid', 'ref', 'mc_eid'];
        $whitelistParams = [];

        $startMemory = memory_get_usage(true);
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $url = "https://example.com/products/item-{$i}?utm_source=twitter&utm_medium=cpc&utm_campaign=summer&fbclid=987654&page=2&sort=price";
            $cleanUrl = Sanitizer::filterQueryParams($url, $stripParams, $whitelistParams);
        }

        $duration = microtime(true) - $startTime;
        $peakMemory = (memory_get_peak_usage(true) - $startMemory) / 1024 / 1024;
        $opsSec = round($iterations / $duration);

        printf("5. Canonical URL & Query Parameter Filtering (Wildcards & Whitelist)\n");
        printf("   Iterations  : %d\n", $iterations);
        printf("   Total Time  : %.4f s\n", $duration);
        printf("   Throughput  : %s ops/sec\n", number_format($opsSec));
        printf("   Avg Latency : %.4f ms/op\n", ($duration / $iterations) * 1000);
        printf("   Memory Peak : %.2f MB\n\n", $peakMemory);
    }

    // 6. Robots.txt Generation
    protected function benchmarkRobotsTxtGeneration(int $iterations): void
    {
        $config = [
            'robots_txt' => [
                'enabled' => true,
                'dynamic' => true,
                'production' => [
                    'allow' => ['/'],
                    'disallow' => ['/admin', '/api/internal', '/telescope', '/horizon'],
                    'user_agents' => [
                        '*' => ['allow' => ['/'], 'disallow' => ['/admin']],
                        'Googlebot' => ['allow' => ['/'], 'disallow' => ['/admin']],
                        'Bingbot' => ['allow' => ['/'], 'disallow' => ['/admin']],
                    ],
                    'ai_crawlers' => [
                        'GPTBot' => ['allow' => ['/']],
                        'ClaudeBot' => ['allow' => ['/']],
                        'PerplexityBot' => ['allow' => ['/']],
                        'CCBot' => ['disallow' => ['/']],
                        'Google-Extended' => ['allow' => ['/']],
                    ],
                ],
                'sitemaps' => ['https://example.com/sitemap.xml'],
                'crawl_delay' => 2,
                'host' => 'example.com',
            ],
        ];

        $manager = new RobotsTxtManager($config);

        $startMemory = memory_get_usage(true);
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $txt = $manager->render();
        }

        $duration = microtime(true) - $startTime;
        $peakMemory = (memory_get_peak_usage(true) - $startMemory) / 1024 / 1024;
        $opsSec = round($iterations / $duration);

        printf("6. Robots.txt Dynamic Crawler Rule Engine (Multi-Agent & AI Bots)\n");
        printf("   Iterations  : %d\n", $iterations);
        printf("   Total Time  : %.4f s\n", $duration);
        printf("   Throughput  : %s ops/sec\n", number_format($opsSec));
        printf("   Avg Latency : %.4f ms/op\n", ($duration / $iterations) * 1000);
        printf("   Memory Peak : %.2f MB\n\n", $peakMemory);
    }

    // 7. LLMs.txt Compilation
    protected function benchmarkLlmsTxtCompilation(int $iterations): void
    {
        $manager = new LlmsTxtManager([
            'llms_txt' => [
                'title' => 'Acme Platform API',
                'description' => 'Comprehensive enterprise developer documentation.',
            ],
        ]);

        $manager->addSection('Core Endpoints', 'Primary REST APIs', [
            ['title' => 'Authentication', 'url' => 'https://example.com/docs/auth', 'description' => 'OAuth2 & tokens'],
            ['title' => 'Users API', 'url' => 'https://example.com/docs/users', 'description' => 'CRUD operations'],
            ['title' => 'Webhooks', 'url' => 'https://example.com/docs/webhooks', 'description' => 'Event triggers'],
        ]);

        $manager->addDocument('Authentication Guide', "# Auth\n\nUse Bearer tokens for all authenticated API requests.");
        $manager->addDocument('Rate Limits', "# Limits\n\nStandard tier is 60 req/min. Enterprise tier is 10,000 req/min.");

        $startMemory = memory_get_usage(true);
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $standard = $manager->render();
            $full = $manager->renderFull();
        }

        $duration = microtime(true) - $startTime;
        $peakMemory = (memory_get_peak_usage(true) - $startMemory) / 1024 / 1024;
        $opsSec = round($iterations / $duration);

        printf("7. LLMs.txt & LLMs-Full.txt Compilation (GEO Engine)\n");
        printf("   Iterations  : %d\n", $iterations);
        printf("   Total Time  : %.4f s\n", $duration);
        printf("   Throughput  : %s ops/sec\n", number_format($opsSec));
        printf("   Avg Latency : %.4f ms/op\n", ($duration / $iterations) * 1000);
        printf("   Memory Peak : %.2f MB\n\n", $peakMemory);
    }

    // 8. Content Transformer
    protected function benchmarkContentTransformer(int $iterations): void
    {
        $html = '<!DOCTYPE html><html><head><title>Test Page</title></head><body>'
            . '<nav><a href="/">Home</a><a href="/about">About</a></nav>'
            . '<header><h1>Documentation Title</h1><p class="subtitle">Platform Guide</p></header>'
            . '<main><article>'
            . '<h2>Overview</h2><p>This is a <strong>comprehensive</strong> guide explaining <a href="https://example.com">features</a>.</p>'
            . '<ul><li>Item 1</li><li>Item 2</li><li>Item 3</li></ul>'
            . '<pre><code>composer require eamirgh/rankforge</code></pre>'
            . '</article></main>'
            . '<footer><p>&copy; 2025 Acme Inc. All rights reserved.</p></footer>'
            . '</body></html>';

        $startMemory = memory_get_usage(true);
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $markdown = ContentTransformer::toMarkdown($html);
            $plainText = ContentTransformer::toPlainText($html);
        }

        $duration = microtime(true) - $startTime;
        $peakMemory = (memory_get_peak_usage(true) - $startMemory) / 1024 / 1024;
        $opsSec = round($iterations / $duration);

        printf("8. ContentTransformer (HTML -> Markdown & PlainText for LLMs.txt)\n");
        printf("   Iterations  : %d\n", $iterations);
        printf("   Total Time  : %.4f s\n", $duration);
        printf("   Throughput  : %s ops/sec\n", number_format($opsSec));
        printf("   Avg Latency : %.4f ms/op\n", ($duration / $iterations) * 1000);
        printf("   Memory Peak : %.2f MB\n\n", $peakMemory);
    }

    // 9. Model Integration Fallback
    protected function benchmarkModelIntegrationFallback(int $iterations): void
    {
        $config = [
            'site_name' => 'Acme Enterprise',
            'title' => ['separator' => '|', 'template' => '{title} {separator} {site_name}'],
            'description' => ['default' => 'Default fallback description'],
        ];

        $model = new BenchmarkModel([
            'title' => 'Model Original Title',
            'custom_title' => 'Model Custom SEO Title',
            'description' => 'Model description excerpt',
        ]);

        $startMemory = memory_get_usage(true);
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $manager = new RankForgeManager($config);
            $manager->forModel($model);
            $head = $manager->renderHead();
        }

        $duration = microtime(true) - $startTime;
        $peakMemory = (memory_get_peak_usage(true) - $startMemory) / 1024 / 1024;
        $opsSec = round($iterations / $duration);

        printf("9. Eloquent Model Integration (forModel with Cascading Fallbacks)\n");
        printf("   Iterations  : %d\n", $iterations);
        printf("   Total Time  : %.4f s\n", $duration);
        printf("   Throughput  : %s ops/sec\n", number_format($opsSec));
        printf("   Avg Latency : %.4f ms/op\n", ($duration / $iterations) * 1000);
        printf("   Memory Peak : %.2f MB\n\n", $peakMemory);
    }

    // 10. Headless Serialization
    protected function benchmarkHeadlessSerialization(int $iterations): void
    {
        $config = [
            'site_name' => 'Acme Enterprise',
            'title' => ['separator' => '|', 'template' => '{title} {separator} {site_name}'],
            'description' => ['default' => 'Default desc'],
            'open_graph' => ['enabled' => true],
            'twitter' => ['enabled' => true],
        ];

        $startMemory = memory_get_usage(true);
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $manager = new RankForgeManager($config);
            $manager->title("Page {$i}")
                ->description("Description {$i}")
                ->canonical("https://example.com/{$i}")
                ->ogImage("https://example.com/{$i}.jpg")
                ->jsonLd(WebSite::make()->name('Acme')->url('https://example.com'));

            $array = $manager->toArray();
            $json = $manager->toJson();
        }

        $duration = microtime(true) - $startTime;
        $peakMemory = (memory_get_peak_usage(true) - $startMemory) / 1024 / 1024;
        $opsSec = round($iterations / $duration);

        printf("10. Headless & Inertia.js Serialization (toArray & toJson)\n");
        printf("    Iterations  : %d\n", $iterations);
        printf("    Total Time  : %.4f s\n", $duration);
        printf("    Throughput  : %s ops/sec\n", number_format($opsSec));
        printf("    Avg Latency : %.4f ms/op\n", ($duration / $iterations) * 1000);
        printf("    Memory Peak : %.2f MB\n\n", $peakMemory);
    }

    // 11. MCP Server JSON-RPC
    protected function benchmarkMcpServerJsonRpc(int $iterations): void
    {
        $server = new McpServer();
        $request = [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => [
                'name' => 'inspect_html_meta',
                'arguments' => [
                    'html' => '<!DOCTYPE html><html><head><title>Test</title><meta name="description" content="Desc"><link rel="canonical" href="https://example.com"></head><body></body></html>',
                ],
            ],
        ];

        $startMemory = memory_get_usage(true);
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $response = $server->handleRequest($request);
        }

        $duration = microtime(true) - $startTime;
        $peakMemory = (memory_get_peak_usage(true) - $startMemory) / 1024 / 1024;
        $opsSec = round($iterations / $duration);

        printf("11. Model Context Protocol (MCP) JSON-RPC Request Handling\n");
        printf("    Iterations  : %d\n", $iterations);
        printf("    Total Time  : %.4f s\n", $duration);
        printf("    Throughput  : %s ops/sec\n", number_format($opsSec));
        printf("    Avg Latency : %.4f ms/op\n", ($duration / $iterations) * 1000);
        printf("    Memory Peak : %.2f MB\n\n", $peakMemory);
    }
}

$runner = new BenchmarkRunner();
$runner->run();
