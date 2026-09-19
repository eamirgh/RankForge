<?php

namespace Eamirgh\RankForge\Tests\Unit\Meta;

use Eamirgh\RankForge\RankForgeManager;
use Eamirgh\RankForge\Schema\Types\Article;
use Eamirgh\RankForge\Tests\TestCase;

class ModelMetaFallbackTest extends TestCase
{
    public function test_it_extracts_title_description_image_and_canonical_from_model(): void
    {
        $model = new class {
            public string $title = 'Model Title';
            public string $description = 'Model Description';
            public string $featured_image = 'https://example.com/featured.jpg';
            public string $url = 'https://example.com/articles/model';
        };

        $manager = new RankForgeManager([
            'site_name' => 'My Site',
        ]);

        $manager->forModel($model);

        $this->assertStringContainsString('Model Title', $manager->getRenderedTitle());
        $this->assertEquals('Model Description', $manager->getDescription());
        $this->assertEquals('https://example.com/articles/model', $manager->getCanonicalUrl());

        $og = $manager->getOpenGraph();
        $this->assertEquals('https://example.com/featured.jpg', $og['image']);
    }

    public function test_it_prioritizes_specific_getter_methods_over_properties(): void
    {
        $model = new class {
            public string $title = 'Property Title';
            public string $description = 'Property Description';

            public function getSeoTitle(): string
            {
                return 'SEO Specific Title';
            }

            public function getSeoDescription(): string
            {
                return 'SEO Specific Description';
            }
        };

        $manager = new RankForgeManager([
            'site_name' => 'My Site',
        ]);

        $manager->forModel($model);

        $this->assertStringContainsString('SEO Specific Title', $manager->getRenderedTitle());
        $this->assertEquals('SEO Specific Description', $manager->getDescription());
    }

    public function test_it_cascades_images_from_config_to_model_to_view_specific(): void
    {
        $model = new class {
            public string $featured_image = 'https://example.com/model-image.jpg';
        };

        $manager = new RankForgeManager([
            'open_graph' => ['image' => 'https://example.com/default-config.jpg'],
        ]);

        // Initial config image
        $this->assertEquals('https://example.com/default-config.jpg', $manager->getOpenGraph()['image']);

        // Model image overrides config default
        $manager->forModel($model);
        $this->assertEquals('https://example.com/model-image.jpg', $manager->getOpenGraph()['image']);

        // View-specific image overrides model image
        $manager->ogImage('https://example.com/view-specific.jpg');
        $this->assertEquals('https://example.com/view-specific.jpg', $manager->getOpenGraph()['image']);
    }

    public function test_it_ignores_model_when_has_seo_returns_false(): void
    {
        $model = new class {
            public string $title = 'Ignored Title';

            public function hasSeo(): bool
            {
                return false;
            }
        };

        $manager = new RankForgeManager();
        $manager->forModel($model);

        $this->assertNull($manager->getTitle());
    }

    public function test_it_attaches_json_ld_from_model_to_schema_manager(): void
    {
        $model = new class {
            public string $title = 'Model Title';
            public string $description = 'Model Description';

            public function toJsonLd(): Article
            {
                return (new Article())
                    ->headline($this->title)
                    ->description($this->description);
            }
        };

        $manager = new RankForgeManager();
        $manager->forModel($model);

        $this->assertCount(1, $manager->getJsonLdSchemas());
    }
}
