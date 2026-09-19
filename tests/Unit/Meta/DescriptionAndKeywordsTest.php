<?php

namespace Eamirgh\RankForge\Tests\Unit\Meta;

use Eamirgh\RankForge\RankForgeManager;
use Eamirgh\RankForge\Tests\TestCase;

class DescriptionAndKeywordsTest extends TestCase
{
    public function test_it_sets_and_retrieves_description(): void
    {
        $manager = new RankForgeManager();
        $manager->description('A concise description of this page.');

        $this->assertEquals('A concise description of this page.', $manager->getDescription());
    }

    public function test_it_falls_back_to_config_default_description(): void
    {
        $manager = new RankForgeManager([
            'description' => [
                'default' => 'Default fallback description.',
                'max_length' => 160,
            ],
        ]);

        $this->assertEquals('Default fallback description.', $manager->getDescription());
    }

    public function test_it_truncates_description_to_maximum_length_at_word_boundary(): void
    {
        $manager = new RankForgeManager([
            'description' => [
                'max_length' => 30,
            ],
        ]);

        $manager->description('The quick brown fox jumps over the lazy dog');

        $desc = $manager->getDescription();
        $this->assertLessThanOrEqual(30, mb_strlen($desc));
        $this->assertEquals('The quick brown fox jumps', $desc);
    }

    public function test_it_normalizes_whitespace_and_strips_tags_in_description(): void
    {
        $manager = new RankForgeManager();
        $manager->description("   <p>Hello <strong>World</strong>!</p>\n\n  Welcome to   our site.  ");

        $this->assertEquals('Hello World! Welcome to our site.', $manager->getDescription());
    }

    public function test_it_sets_keywords_from_string_and_array(): void
    {
        $manager = new RankForgeManager();
        $manager->keywords('seo, laravel, search engine');
        $manager->keywords(['php', 'laravel']);

        $this->assertEquals(['seo', 'laravel', 'search engine', 'php'], $manager->getKeywords());
        $this->assertEquals('seo, laravel, search engine, php', $manager->getKeywordsString());
    }

    public function test_it_merges_and_deduplicates_keywords_with_config(): void
    {
        $manager = new RankForgeManager([
            'keywords' => ['default1', 'default2', 'common'],
        ]);

        $manager->keywords(['common', 'unique1']);

        $this->assertEquals(['default1', 'default2', 'common', 'common', 'unique1'], $manager->getKeywords());
        $this->assertEquals('default1, default2, common, unique1', $manager->getKeywordsString());
    }

    public function test_it_renders_description_and_keywords_meta_tags(): void
    {
        $manager = new RankForgeManager();
        $manager->description('Page description')
            ->keywords('keyword1, keyword2');

        $html = $manager->renderHead();
        $this->assertStringContainsString('<meta name="description" content="Page description">', $html);
        $this->assertStringContainsString('<meta name="keywords" content="keyword1, keyword2">', $html);
    }

    public function test_it_detects_description_warnings_for_short_and_long_descriptions(): void
    {
        $manager = new RankForgeManager([
            'description' => [
                'max_length' => 160,
            ],
        ]);

        // Valid length (e.g. 80 chars)
        $manager->description(str_repeat('a', 80));
        $this->assertFalse($manager->hasDescriptionWarning());
        $this->assertNull($manager->getDescriptionWarning());

        // Too short (< 50 chars)
        $manager->description('Too short');
        $this->assertFalse($manager->hasDescriptionWarning()); // hasDescriptionWarning is only for > max_length
        $this->assertNotNull($manager->getDescriptionWarning());
        $this->assertStringContainsString('50', $manager->getDescriptionWarning());

        // Too long (> 160 chars)
        $manager->description(str_repeat('b', 161));
        $this->assertTrue($manager->hasDescriptionWarning());
        $this->assertNotNull($manager->getDescriptionWarning());
        $this->assertStringContainsString('160', $manager->getDescriptionWarning());
    }
}
