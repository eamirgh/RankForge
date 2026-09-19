<?php

namespace Eamirgh\RankForge\Tests\Unit\Schema;

use Eamirgh\RankForge\Schema\Types\WebSite;
use Eamirgh\RankForge\Tests\TestCase;

class WebSiteSchemaTest extends TestCase
{
    public function test_it_generates_website_schema_with_search_action(): void
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
        $this->assertEquals('required name=search_term_string', $array['potentialAction']['query-input']);
    }

    public function test_it_generates_basic_website_schema_without_search_action(): void
    {
        $site = WebSite::make()
            ->name('Simple Site')
            ->url('https://example.com');

        $array = $site->toArray();

        $this->assertEquals('WebSite', $array['@type']);
        $this->assertEquals('Simple Site', $array['name']);
        $this->assertEquals('https://example.com', $array['url']);
        $this->assertArrayNotHasKey('potentialAction', $array);
    }

    public function test_it_converts_to_script_tag(): void
    {
        $site = WebSite::make()->name('Script Site')->url('https://example.com');
        $script = $site->toScript();

        $this->assertStringStartsWith('<script type="application/ld+json">', $script);
        $this->assertStringEndsWith('</script>', $script);
        $this->assertStringContainsString('"@type":"WebSite"', $script);
    }
}
