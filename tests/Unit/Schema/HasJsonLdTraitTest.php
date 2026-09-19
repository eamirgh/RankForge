<?php

namespace Eamirgh\RankForge\Tests\Unit\Schema;

use Eamirgh\RankForge\Schema\Concerns\HasJsonLd;
use Eamirgh\RankForge\Schema\Types\Article;
use Eamirgh\RankForge\Tests\TestCase;

class HasJsonLdTraitTest extends TestCase
{
    public function test_it_converts_object_using_has_json_ld_trait_to_article_schema(): void
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

    public function test_it_handles_missing_optional_properties(): void
    {
        $dummy = new class {
            use HasJsonLd;
        };

        $schema = $dummy->toJsonLd();
        $this->assertInstanceOf(Article::class, $schema);
        $this->assertEquals('Article', $schema->getType());

        $array = $schema->toArray();
        $this->assertEquals('https://schema.org', $array['@context']);
        $this->assertEquals('Article', $array['@type']);
        $this->assertArrayNotHasKey('headline', $array);
        $this->assertArrayNotHasKey('description', $array);
    }
}
