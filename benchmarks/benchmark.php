<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Orchestra\Testbench\Concerns\CreatesApplication;
use Eamirgh\RankForge\RankForgeManager;
use Eamirgh\RankForge\RankForgeServiceProvider;
use Eamirgh\RankForge\Schema\Types\Article;
use Eamirgh\RankForge\Schema\Types\BreadcrumbList;
use Eamirgh\RankForge\Schema\Types\LocalBusiness;
use Eamirgh\RankForge\Schema\Types\Offer;
use Eamirgh\RankForge\Schema\Types\Product;
use Eamirgh\RankForge\Schema\Types\WebSite;
use Eamirgh\RankForge\Schema\Graph;
use Eamirgh\RankForge\Sitemap\SitemapUrl;
use Eamirgh\RankForge\Sitemap\SitemapManager;
use Eamirgh\RankForge\Crawlers\Transformers\ContentTransformer;

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
        echo " RankForge Local Benchmark Suite\n";
        echo " PHP Version : " . PHP_VERSION . "\n";
        echo " Date        : " . date('Y-m-d H:i:s') . "\n";
        echo "===============================================================\n\n";

        $this->benchmarkMetaTagsRendering(5000);
        $this->benchmarkJsonLdComplexGraph(5000);
        $this->benchmarkSitemapGeneration(50000);
        $this->benchmarkContentTransformer(1000);
        $this->benchmarkHeadlessSerialization(5000);

        echo "===============================================================\n";
        echo " All benchmarks completed successfully.\n";
        echo "===============================================================\n";
    }

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
                ->articlePublishedTime('2026-01-01T00:00:00Z');

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

    protected function benchmarkSitemapGeneration(int $urlCount): void
    {
        $startMemory = memory_get_usage(true);
        $startTime = microtime(true);

        $manager = new SitemapManager(['max_urls' => 50000]);
        $manager->register('large_catalog', function () use ($urlCount) {
            for ($i = 1; $i <= $urlCount; $i++) {
                yield SitemapUrl::make("https://example.com/items/{$i}")
                    ->lastmod('2026-01-01')
                    ->changefreq('daily')
                    ->priority(0.8)
                    ->image("https://example.com/img/{$i}.jpg", "Item {$i}");
            }
        });

        $xml = $manager->renderSection('large_catalog', 1);

        $duration = microtime(true) - $startTime;
        $memoryUsed = (memory_get_usage(true) - $startMemory) / 1024 / 1024;
        $peakMemory = (memory_get_peak_usage(true) - $startMemory) / 1024 / 1024;

        printf("3. High-Volume XML Sitemap Generation (Streaming Generator)\n");
        printf("   URL Count   : %s URLs (with Image Extension)\n", number_format($urlCount));
        printf("   XML Size    : %.2f MB\n", strlen($xml) / 1024 / 1024);
        printf("   Total Time  : %.4f s\n", $duration);
        printf("   Throughput  : %s URLs/sec\n", number_format(round($urlCount / $duration)));
        printf("   Memory Delta: %.2f MB\n", $memoryUsed);
        printf("   Peak Memory : %.2f MB\n\n", $peakMemory);
    }

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
            . '<footer><p>&copy; 2026 Acme Inc. All rights reserved.</p></footer>'
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

        printf("4. ContentTransformer (HTML -> Markdown & PlainText for LLMs.txt)\n");
        printf("   Iterations  : %d\n", $iterations);
        printf("   Total Time  : %.4f s\n", $duration);
        printf("   Throughput  : %s ops/sec\n", number_format($opsSec));
        printf("   Avg Latency : %.4f ms/op\n", ($duration / $iterations) * 1000);
        printf("   Memory Peak : %.2f MB\n\n", $peakMemory);
    }

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

        printf("5. Headless & Inertia.js Serialization (toArray & toJson)\n");
        printf("   Iterations  : %d\n", $iterations);
        printf("   Total Time  : %.4f s\n", $duration);
        printf("   Throughput  : %s ops/sec\n", number_format($opsSec));
        printf("   Avg Latency : %.4f ms/op\n", ($duration / $iterations) * 1000);
        printf("   Memory Peak : %.2f MB\n\n", $peakMemory);
    }
}

$runner = new BenchmarkRunner();
$runner->run();
