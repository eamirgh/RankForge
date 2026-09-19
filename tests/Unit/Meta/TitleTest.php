<?php

namespace Eamirgh\RankForge\Tests\Unit\Meta;

use Eamirgh\RankForge\RankForgeManager;
use Eamirgh\RankForge\Tests\TestCase;

class TitleTest extends TestCase
{
    public function test_it_sets_and_retrieves_title(): void
    {
        $manager = new RankForgeManager();
        $manager->title('My Custom Title');

        $this->assertEquals('My Custom Title', $manager->getTitle());
    }

    public function test_it_renders_title_with_configured_template_and_separator(): void
    {
        $manager = new RankForgeManager([
            'site_name' => 'Acme Corp',
            'title' => [
                'template' => '{title} {separator} {site_name}',
                'separator' => '-',
                'max_length' => 60,
            ],
        ]);

        $manager->title('About Us');

        $this->assertEquals('About Us - Acme Corp', $manager->getRenderedTitle());
    }

    public function test_it_falls_back_to_site_name_when_title_is_empty(): void
    {
        $manager = new RankForgeManager([
            'site_name' => 'Acme Corp',
            'title' => [
                'default' => '',
            ],
        ]);

        $this->assertEquals('Acme Corp', $manager->getRenderedTitle());
    }

    public function test_it_returns_empty_string_when_title_and_site_name_are_empty(): void
    {
        $manager = new RankForgeManager([
            'site_name' => '',
            'title' => [
                'default' => '',
            ],
        ]);

        $this->assertEquals('', $manager->getRenderedTitle());
    }

    public function test_it_truncates_title_to_maximum_length(): void
    {
        $manager = new RankForgeManager([
            'site_name' => 'Acme Corp',
            'title' => [
                'template' => '{title}',
                'max_length' => 20,
            ],
        ]);

        $manager->title('This is a very long title that should be truncated');

        // Word-boundary truncation: "This is a very long" is 19 chars <= 20 chars
        $rendered = $manager->getRenderedTitle();
        $this->assertLessThanOrEqual(20, mb_strlen($rendered));
        $this->assertEquals('This is a very long', $rendered);
    }

    public function test_it_falls_back_to_default_title_from_config(): void
    {
        $manager = new RankForgeManager([
            'site_name' => 'Acme Corp',
            'title' => [
                'default' => 'Welcome to Acme',
                'template' => '{title} | {site_name}',
            ],
        ]);

        $this->assertEquals('Welcome to Acme | Acme Corp', $manager->getRenderedTitle());
    }

    public function test_it_escapes_html_in_rendered_title_tag(): void
    {
        $manager = new RankForgeManager([
            'site_name' => 'Acme & Co',
            'title' => [
                'template' => '{title} | {site_name}',
            ],
        ]);

        $manager->title('News <script>alert(1)</script>');

        $html = $manager->renderHead();
        $this->assertStringContainsString('<title>News alert(1) | Acme &amp; Co</title>', $html);
    }

    public function test_it_detects_title_truncation_warning(): void
    {
        $manager = new RankForgeManager([
            'title' => [
                'max_length' => 60,
            ],
        ]);

        $shortTitle = 'Short Title';
        $manager->title($shortTitle);
        $this->assertFalse($manager->hasTitleWarning());
        $this->assertNull($manager->getTitleWarning());

        $longTitle = str_repeat('A', 61);
        $manager->title($longTitle);
        $this->assertTrue($manager->hasTitleWarning());
        $this->assertNotNull($manager->getTitleWarning());
        $this->assertStringContainsString('60', $manager->getTitleWarning());
        $this->assertStringContainsString('61', $manager->getTitleWarning());
    }
}
