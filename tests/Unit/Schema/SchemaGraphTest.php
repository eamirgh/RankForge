<?php

namespace Eamirgh\RankForge\Tests\Unit\Schema;

use Eamirgh\RankForge\RankForgeManager;
use Eamirgh\RankForge\Schema\Graph;
use Eamirgh\RankForge\Schema\Types\Organization;
use Eamirgh\RankForge\Schema\Types\WebSite;
use Eamirgh\RankForge\Tests\TestCase;

class SchemaGraphTest extends TestCase
{
    public function test_it_creates_and_manages_schema_graph(): void
    {
        $graph = Graph::make();
        $this->assertTrue($graph->isEmpty());

        $graph->add(WebSite::make()->name('Graph Site')->url('https://example.com'));
        $graph->add(Organization::make()->name('Graph Org')->url('https://example.com'));

        $this->assertFalse($graph->isEmpty());
        $this->assertCount(2, $graph->getSchemas());

        $graphArray = $graph->toArray();
        $this->assertEquals('https://schema.org', $graphArray['@context']);
        $this->assertCount(2, $graphArray['@graph']);
        $this->assertEquals('WebSite', $graphArray['@graph'][0]['@type']);
        $this->assertEquals('Organization', $graphArray['@graph'][1]['@type']);
    }

    public function test_it_renders_graph_via_rank_forge_manager(): void
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
    }

    public function test_it_prevents_closing_script_tag_injection(): void
    {
        $manager = new RankForgeManager([
            'json_ld' => ['enabled' => true],
        ]);

        $site = WebSite::make()->name('Malicious </script><script>alert(1)</script>');
        $manager->jsonLd($site);

        $html = $manager->renderHead();
        $this->assertStringNotContainsString('</script><script>', $html);
        $this->assertStringContainsString('<\/script', $html);
    }
}
