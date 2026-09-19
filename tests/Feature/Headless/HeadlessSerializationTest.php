<?php

namespace Eamirgh\RankForge\Tests\Feature\Headless;

use Eamirgh\RankForge\RankForgeManager;
use Eamirgh\RankForge\Schema\Types\Article;
use Eamirgh\RankForge\Tests\TestCase;

class HeadlessSerializationTest extends TestCase
{
    public function test_it_serializes_all_meta_and_schema_data_to_array(): void
    {
        $manager = new RankForgeManager([
            'site_name' => 'Headless App',
            'open_graph' => ['enabled' => true],
            'twitter' => ['enabled' => true],
            'json_ld' => ['enabled' => true],
        ]);

        $manager->title('Headless Title')
            ->description('Headless Description')
            ->keywords(['headless', 'seo', 'inertia'])
            ->canonical('https://example.com/headless')
            ->customRobots('googlebot', 'index, follow')
            ->ogImage('https://example.com/og.jpg')
            ->twitterCard('summary_large_image')
            ->hreflang('en', 'https://example.com/en')
            ->jsonLd(Article::make()->headline('Article Headline'));

        $data = $manager->toArray();

        $this->assertIsArray($data);
        $this->assertStringContainsString('Headless Title', $data['title']);
        $this->assertEquals('Headless Description', $data['description']);
        $this->assertEquals('https://example.com/headless', $data['canonical']);
        $this->assertEquals(['headless', 'seo', 'inertia'], $data['keywords']);
        $this->assertEquals(['googlebot' => 'index, follow'], $data['custom_robots']);
        $this->assertEquals('https://example.com/og.jpg', $data['open_graph']['image']);
        $this->assertEquals('summary_large_image', $data['twitter']['card']);
        $this->assertEquals(['en' => 'https://example.com/en'], $data['hreflang']);
        $this->assertArrayHasKey('json_ld', $data);
        $this->assertEquals('Article', $data['json_ld'][0]['@type']);
    }

    public function test_it_serializes_to_json_and_implements_json_serializable(): void
    {
        $manager = new RankForgeManager([
            'site_name' => 'API App',
        ]);

        $manager->title('API Page')
            ->description('API Description');

        $json = $manager->toJson();
        $this->assertJson($json);

        $decoded = json_decode($json, true);
        $this->assertStringContainsString('API Page', $decoded['title']);

        // jsonSerialize() produces the same data
        $this->assertEquals($manager->toArray(), $manager->jsonSerialize());
        $this->assertEquals($json, json_encode($manager, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
