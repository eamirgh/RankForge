<?php

namespace Eamirgh\RankForge\Tests\Unit\Meta;

use Eamirgh\RankForge\RankForgeManager;
use Eamirgh\RankForge\Tests\TestCase;

class HreflangTest extends TestCase
{
    public function test_it_sets_x_default_and_individual_hreflang_entries(): void
    {
        $manager = new RankForgeManager();

        $manager->xDefault('https://example.com')
            ->hreflang('en', 'https://example.com/en');

        $entries = $manager->getHreflangEntries();
        $this->assertEquals('https://example.com', $entries['x-default']);
        $this->assertEquals('https://example.com/en', $entries['en']);
    }

    public function test_it_merges_multiple_hreflangs(): void
    {
        $manager = new RankForgeManager();

        $manager->hreflangs([
            'de' => 'https://example.com/de',
            'fr' => 'https://example.com/fr',
        ]);

        $entries = $manager->getHreflangEntries();
        $this->assertEquals('https://example.com/de', $entries['de']);
        $this->assertEquals('https://example.com/fr', $entries['fr']);
    }

    public function test_it_merges_with_config_locales(): void
    {
        $manager = new RankForgeManager([
            'hreflang' => [
                'x_default' => 'https://example.com/default',
                'locales' => [
                    'es' => 'https://example.com/es',
                    'it' => 'https://example.com/it',
                ],
            ],
        ]);

        $manager->hreflang('pt', 'https://example.com/pt');

        $entries = $manager->getHreflangEntries();
        $this->assertEquals('https://example.com/default', $entries['x-default']);
        $this->assertEquals('https://example.com/es', $entries['es']);
        $this->assertEquals('https://example.com/it', $entries['it']);
        $this->assertEquals('https://example.com/pt', $entries['pt']);
    }

    public function test_it_renders_alternate_hreflang_link_tags(): void
    {
        $manager = new RankForgeManager();

        $manager->xDefault('https://example.com')
            ->hreflang('en', 'https://example.com/en')
            ->hreflangs([
                'de' => 'https://example.com/de',
                'fr' => 'https://example.com/fr',
            ]);

        $html = $manager->renderHead();
        $this->assertStringContainsString('<link rel="alternate" hreflang="x-default" href="https://example.com">', $html);
        $this->assertStringContainsString('<link rel="alternate" hreflang="en" href="https://example.com/en">', $html);
        $this->assertStringContainsString('<link rel="alternate" hreflang="de" href="https://example.com/de">', $html);
        $this->assertStringContainsString('<link rel="alternate" hreflang="fr" href="https://example.com/fr">', $html);
    }

    public function test_it_checks_if_hreflang_is_enabled(): void
    {
        $managerWithoutHreflang = new RankForgeManager();
        $this->assertFalse($managerWithoutHreflang->isHreflangEnabled());

        $managerWithEntries = new RankForgeManager();
        $managerWithEntries->hreflang('en', 'https://example.com/en');
        $this->assertTrue($managerWithEntries->isHreflangEnabled());

        $managerConfigEnabled = new RankForgeManager([
            'hreflang' => ['enabled' => true],
        ]);
        $this->assertTrue($managerConfigEnabled->isHreflangEnabled());
    }
}
