<?php

namespace RankForge\Tests\Unit;

use DateTimeImmutable;
use RankForge\RankForgeManager;
use RankForge\Schema\Concerns\HasJsonLd;
use RankForge\Schema\Graph;
use RankForge\Schema\Types\Article;
use RankForge\Schema\Types\BlogPosting;
use RankForge\Schema\Types\BreadcrumbList;
use RankForge\Schema\Types\FAQPage;
use RankForge\Schema\Types\HowTo;
use RankForge\Schema\Types\LocalBusiness;
use RankForge\Schema\Types\NewsArticle;
use RankForge\Schema\Types\Offer;
use RankForge\Schema\Types\Organization;
use RankForge\Schema\Types\Product;
use RankForge\Schema\Types\SoftwareApplication;
use RankForge\Schema\Types\WebSite;
use RankForge\Tests\TestCase;

class Phase3JsonLdTest extends TestCase
{
    public function test_website_schema_with_search_action(): void
    {
        $site = WebSite::make()
            ->name('My Site')
            ->url('https://example.com')
            ->searchAction('https://example.com/search?q={search_term_string}');

        $array = $site->toArray();

        $this->assertEquals('https://schema.org', $array['@context']);
        $this->assertEquals('WebSite', $array['@type']);
        $this->assertEquals('My Site', $array['name']);
        $this->assertEquals('https://example.com', $array['url']);
        $this->assertEquals('SearchAction', $array['potentialAction']['@type']);
        $this->assertEquals('EntryPoint', $array['potentialAction']['target']['@type']);
        $this->assertEquals('https://example.com/search?q={search_term_string}', $array['potentialAction']['target']['urlTemplate']);
    }

    public function test_organization_and_local_business_schema(): void
    {
        $org = Organization::make()
            ->name('Acme Corp')
            ->url('https://acme.test')
            ->logo('https://acme.test/logo.png')
            ->sameAs(['https://twitter.com/acme', 'https://facebook.com/acme'])
            ->contactPoint('+1-800-555-0199', 'technical support', 'US');

        $orgArray = $org->toArray();
        $this->assertEquals('Organization', $orgArray['@type']);
        $this->assertEquals('+1-800-555-0199', $orgArray['contactPoint']['telephone']);
        $this->assertEquals('US', $orgArray['contactPoint']['areaServed']);

        $biz = LocalBusiness::make()
            ->name('Acme Cafe')
            ->telephone('+1-555-1234')
            ->priceRange('$$')
            ->address([
                'streetAddress' => '123 Main St',
                'addressLocality' => 'Austin',
                'addressRegion' => 'TX',
                'postalCode' => '78701',
            ])
            ->geo(30.2672, -97.7431)
            ->openingHours(['Mo-Fr 08:00-18:00', 'Sa 09:00-15:00']);

        $bizArray = $biz->toArray();
        $this->assertEquals('LocalBusiness', $bizArray['@type']);
        $this->assertEquals('PostalAddress', $bizArray['address']['@type']);
        $this->assertEquals('GeoCoordinates', $bizArray['geo']['@type']);
        $this->assertEquals(30.2672, $bizArray['geo']['latitude']);
        $this->assertCount(2, $bizArray['openingHours']);
    }

    public function test_breadcrumb_list_schema(): void
    {
        $breadcrumbs = BreadcrumbList::make()
            ->add('Home', 'https://example.com', 1)
            ->add('Blog', 'https://example.com/blog', 2)
            ->add('Post', 'https://example.com/blog/my-post', 3);

        $array = $breadcrumbs->toArray();
        $this->assertEquals('BreadcrumbList', $array['@type']);
        $this->assertCount(3, $array['itemListElement']);
        $this->assertEquals('ListItem', $array['itemListElement'][0]['@type']);
        $this->assertEquals('Home', $array['itemListElement'][0]['name']);

        $fromUrl = BreadcrumbList::make()->fromUrl('https://example.com/category/tech-news');
        $fromUrlArray = $fromUrl->toArray();
        $this->assertCount(3, $fromUrlArray['itemListElement']);
        $this->assertEquals('Home', $fromUrlArray['itemListElement'][0]['name']);
        $this->assertEquals('Category', $fromUrlArray['itemListElement'][1]['name']);
        $this->assertEquals('Tech News', $fromUrlArray['itemListElement'][2]['name']);
    }

    public function test_article_blog_posting_and_news_article(): void
    {
        $date = new DateTimeImmutable('2025-02-01T10:00:00Z');

        $article = Article::make()
            ->headline('Breaking News')
            ->description('An insightful article')
            ->image(['https://example.com/art1.jpg'])
            ->datePublished($date)
            ->dateModified($date)
            ->author('John Doe')
            ->publisher('The Daily News', 'https://example.com/logo.png')
            ->mainEntityOfPage('https://example.com/news/1');

        $array = $article->toArray();
        $this->assertEquals('Article', $array['@type']);
        $this->assertEquals('Breaking News', $array['headline']);
        $this->assertEquals('Person', $array['author']['@type']);
        $this->assertEquals('John Doe', $array['author']['name']);
        $this->assertEquals('Organization', $array['publisher']['@type']);
        $this->assertEquals('https://example.com/logo.png', $array['publisher']['logo']['url']);
        $this->assertEquals('WebPage', $array['mainEntityOfPage']['@type']);
        $this->assertEquals('https://example.com/news/1', $array['mainEntityOfPage']['@id']);

        $blog = BlogPosting::make()->headline('Blog post');
        $this->assertEquals('BlogPosting', $blog->getType());

        $news = NewsArticle::make()->headline('News post');
        $this->assertEquals('NewsArticle', $news->getType());
    }

    public function test_product_and_offer(): void
    {
        $offer = Offer::make()
            ->price(29.99)
            ->priceCurrency('USD')
            ->availability('https://schema.org/InStock')
            ->url('https://example.com/buy/product-1');

        $product = Product::make()
            ->name('Acme Widget')
            ->description('High quality widget')
            ->image('https://example.com/widget.jpg')
            ->sku('WIDGET-123')
            ->mpn('MPN-999')
            ->brand('Acme')
            ->offers($offer)
            ->aggregateRating(4.8, 125, 5, 1);

        $array = $product->toArray();
        $this->assertEquals('Product', $array['@type']);
        $this->assertEquals('Brand', $array['brand']['@type']);
        $this->assertEquals('Acme', $array['brand']['name']);
        $this->assertEquals('Offer', $array['offers']['@type']);
        $this->assertEquals(29.99, $array['offers']['price']);
        $this->assertEquals('AggregateRating', $array['aggregateRating']['@type']);
        $this->assertEquals(4.8, $array['aggregateRating']['ratingValue']);
        $this->assertEquals(125, $array['aggregateRating']['reviewCount']);
    }

    public function test_faq_page_schema(): void
    {
        $faq = FAQPage::make()
            ->addQuestion('What is RankForge?', 'An SEO engine for Laravel.')
            ->questions([
                ['question' => 'Does it support PHP 8.2+?', 'answer' => 'Yes, fully.'],
            ]);

        $array = $faq->toArray();
        $this->assertEquals('FAQPage', $array['@type']);
        $this->assertCount(2, $array['mainEntity']);
        $this->assertEquals('Question', $array['mainEntity'][0]['@type']);
        $this->assertEquals('What is RankForge?', $array['mainEntity'][0]['name']);
        $this->assertEquals('Answer', $array['mainEntity'][0]['acceptedAnswer']['@type']);
        $this->assertEquals('An SEO engine for Laravel.', $array['mainEntity'][0]['acceptedAnswer']['text']);
    }

    public function test_how_to_schema(): void
    {
        $howTo = HowTo::make()
            ->name('How to Install RankForge')
            ->description('Step by step guide')
            ->totalTime('PT5M')
            ->supply(['Composer', 'PHP 8.2+'])
            ->tool(['Terminal'])
            ->addStep('Require Package', 'Run composer require rankforge/rankforge', 'https://example.com/step1');

        $array = $howTo->toArray();
        $this->assertEquals('HowTo', $array['@type']);
        $this->assertEquals('PT5M', $array['totalTime']);
        $this->assertEquals('HowToSupply', $array['supply'][0]['@type']);
        $this->assertEquals('HowToTool', $array['tool'][0]['@type']);
        $this->assertEquals('HowToStep', $array['step'][0]['@type']);
        $this->assertEquals('Require Package', $array['step'][0]['name']);
    }

    public function test_software_application_schema(): void
    {
        $app = SoftwareApplication::make()
            ->name('RankForge CLI')
            ->operatingSystem('Linux, macOS, Windows')
            ->applicationCategory('DeveloperApplication')
            ->aggregateRating(4.9, 50);

        $array = $app->toArray();
        $this->assertEquals('SoftwareApplication', $array['@type']);
        $this->assertEquals('DeveloperApplication', $array['applicationCategory']);
        $this->assertEquals(4.9, $array['aggregateRating']['ratingValue']);
    }

    public function test_graph_and_schema_manager_rendering(): void
    {
        $manager = new RankForgeManager([
            'json_ld' => [
                'enabled' => true,
                'pretty_print' => true,
            ],
        ]);

        $graph = Graph::make();
        $graph->add(WebSite::make()->name('Graph Site')->url('https://example.com'));
        $graph->add(Organization::make()->name('Graph Org')->url('https://example.com'));

        $manager->jsonLd($graph);

        $html = $manager->renderHead();
        $this->assertStringContainsString('<script type="application/ld+json">', $html);
        $this->assertStringContainsString('"@graph"', $html);
        $this->assertStringContainsString('"@type": "WebSite"', $html);
        $this->assertStringContainsString('"@type": "Organization"', $html);

        $graphArray = $graph->toArray();
        $this->assertEquals('https://schema.org', $graphArray['@context']);
        $this->assertCount(2, $graphArray['@graph']);
    }

    public function test_has_json_ld_trait(): void
    {
        $dummy = new class {
            use HasJsonLd;

            public string $title = 'Trait Post Title';
            public string $description = 'Trait Post Excerpt';
            public string $image = 'https://example.com/trait.jpg';
            public string $url = 'https://example.com/posts/trait';
        };

        $schema = $dummy->toJsonLd();
        $this->assertInstanceOf(Article::class, $schema);

        $array = $schema->toArray();
        $this->assertEquals('Trait Post Title', $array['headline']);
        $this->assertEquals('Trait Post Excerpt', $array['description']);
        $this->assertEquals('https://example.com/trait.jpg', $array['image']);
        $this->assertEquals('https://example.com/posts/trait', $array['mainEntityOfPage']['@id']);
    }
}
