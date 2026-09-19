<?php

namespace Eamirgh\RankForge\Tests\Unit\Crawlers;

use Eamirgh\RankForge\Crawlers\LlmsTxtManager;
use Eamirgh\RankForge\Facades\RankForge;
use Eamirgh\RankForge\Tests\TestCase;

class LlmsTxtManagerTest extends TestCase
{
    public function test_it_renders_llms_txt_with_sections_and_links(): void
    {
        $manager = new LlmsTxtManager([
            'site_name' => 'RankForge Docs',
            'llms_txt' => [
                'title' => 'RankForge',
                'description' => 'Enterprise SEO and GEO Engine for Laravel',
            ],
        ]);

        $manager->addSection('Core Links', 'Essential pages', [
            ['title' => 'Overview', 'url' => 'https://example.com/docs', 'description' => 'Introduction to features'],
            ['title' => 'Installation', 'url' => 'https://example.com/install'],
        ]);

        $manager->addSection('Optional', '', [
            ['title' => 'API Reference', 'url' => 'https://example.com/api', 'description' => 'Complete endpoints'],
        ]);

        $rendered = $manager->render();

        $this->assertStringContainsString('# RankForge', $rendered);
        $this->assertStringContainsString('> Enterprise SEO and GEO Engine for Laravel', $rendered);
        $this->assertStringContainsString('## Core Links', $rendered);
        $this->assertStringContainsString('Essential pages', $rendered);
        $this->assertStringContainsString('- [Overview](https://example.com/docs): Introduction to features', $rendered);
        $this->assertStringContainsString('- [Installation](https://example.com/install)', $rendered);
        $this->assertStringContainsString('## Optional', $rendered);
        $this->assertStringContainsString('- [API Reference](https://example.com/api): Complete endpoints', $rendered);
    }

    public function test_it_renders_full_llms_txt_and_writes_to_disk(): void
    {
        $tempDir = sys_get_temp_dir().'/llms-test-'.uniqid();
        mkdir($tempDir, 0755, true);

        $manager = new LlmsTxtManager([
            'llms_txt' => [
                'title' => 'My Project',
                'description' => 'Project Description',
                'strip_html' => true,
            ],
        ]);

        $manager->addDocument('Guide', '<h1>Guide</h1><p>Welcome to <strong>RankForge</strong>.</p>', 'https://example.com/guide');

        $full = $manager->renderFull();

        $this->assertStringContainsString('# My Project', $full);
        $this->assertStringContainsString('> Project Description', $full);
        $this->assertStringContainsString('## Guide', $full);
        $this->assertStringContainsString('Source: https://example.com/guide', $full);
        $this->assertStringContainsString('Welcome to **RankForge**.', $full);

        $written = $manager->writeToDisk($tempDir);

        $this->assertFileExists($written['llms.txt']);
        $this->assertFileExists($written['llms-full.txt']);

        @unlink($written['llms.txt']);
        @unlink($written['llms-full.txt']);
        @rmdir($tempDir);
    }

    public function test_it_accesses_llms_txt_via_facade(): void
    {
        $this->assertInstanceOf(LlmsTxtManager::class, RankForge::llmsTxt());
    }
}
