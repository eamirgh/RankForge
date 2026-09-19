<?php

namespace Eamirgh\RankForge\Tests\Unit\Schema;

use Eamirgh\RankForge\Schema\Types\BreadcrumbList;
use Eamirgh\RankForge\Tests\TestCase;

class BreadcrumbListSchemaTest extends TestCase
{
    public function test_it_generates_breadcrumb_list_schema(): void
    {
        $breadcrumbs = BreadcrumbList::make()
            ->add('Home', 'https://example.com', 1)
            ->add('Blog', 'https://example.com/blog', 2)
            ->add('Post', 'https://example.com/blog/my-post', 3);

        $array = $breadcrumbs->toArray();

        $this->assertEquals('https://schema.org', $array['@context']);
        $this->assertEquals('BreadcrumbList', $array['@type']);
        $this->assertCount(3, $array['itemListElement']);
        $this->assertEquals('ListItem', $array['itemListElement'][0]['@type']);
        $this->assertEquals('Home', $array['itemListElement'][0]['name']);
        $this->assertEquals('https://example.com', $array['itemListElement'][0]['item']);
        $this->assertEquals(1, $array['itemListElement'][0]['position']);
    }

    public function test_it_generates_breadcrumbs_from_url(): void
    {
        $fromUrl = BreadcrumbList::make()->fromUrl('https://example.com/category/tech-news');
        $fromUrlArray = $fromUrl->toArray();

        $this->assertCount(3, $fromUrlArray['itemListElement']);
        $this->assertEquals('Home', $fromUrlArray['itemListElement'][0]['name']);
        $this->assertEquals('https://example.com', $fromUrlArray['itemListElement'][0]['item']);
        $this->assertEquals('Category', $fromUrlArray['itemListElement'][1]['name']);
        $this->assertEquals('https://example.com/category', $fromUrlArray['itemListElement'][1]['item']);
        $this->assertEquals('Tech News', $fromUrlArray['itemListElement'][2]['name']);
        $this->assertEquals('https://example.com/category/tech-news', $fromUrlArray['itemListElement'][2]['item']);
    }

    public function test_it_adds_items_from_associative_array(): void
    {
        $breadcrumbs = BreadcrumbList::make()->items([
            'Home' => 'https://example.com',
            'Products' => 'https://example.com/products',
        ]);

        $array = $breadcrumbs->toArray();

        $this->assertCount(2, $array['itemListElement']);
        $this->assertEquals('Home', $array['itemListElement'][0]['name']);
        $this->assertEquals('Products', $array['itemListElement'][1]['name']);
    }
}
