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

    public function test_it_scans_directory_for_markdown_files(): void
    {
        $tempDir = sys_get_temp_dir().'/llms-scan-test-'.uniqid();
        mkdir($tempDir.'/nested', 0755, true);

        // File 1: Has heading and paragraph
        file_put_contents(
            $tempDir.'/intro.md',
            "# Introduction to RankForge\n\nThis is the introductory guide for RankForge.\n\nMore info here."
        );

        // File 2: Has no heading (uses filename), has frontmatter
        file_put_contents(
            $tempDir.'/nested/configuration.markdown',
            "---\ntitle: Meta Config\n---\n\nConfigure your application settings easily.\n\nDetails below."
        );

        // File 3: Non-markdown file (should be ignored)
        file_put_contents($tempDir.'/nested/ignored.txt', 'Ignored content');

        $manager = new LlmsTxtManager();
        $manager->addDirectory($tempDir, 'https://example.com/docs', 'Documentation');

        $rendered = $manager->render();
        $this->assertStringContainsString('## Documentation', $rendered);
        $this->assertStringContainsString('- [Introduction to RankForge](https://example.com/docs/intro.md): This is the introductory guide for RankForge.', $rendered);
        $this->assertStringContainsString('- [configuration](https://example.com/docs/nested/configuration.markdown): Configure your application settings easily.', $rendered);
        $this->assertStringNotContainsString('ignored.txt', $rendered);

        $full = $manager->renderFull();
        $this->assertStringContainsString('## Introduction to RankForge', $full);
        $this->assertStringContainsString('Source: https://example.com/docs/intro.md', $full);
        $this->assertStringContainsString('This is the introductory guide for RankForge.', $full);
        $this->assertStringContainsString('## configuration', $full);
        $this->assertStringContainsString('Source: https://example.com/docs/nested/configuration.markdown', $full);

        // Cleanup
        @unlink($tempDir.'/intro.md');
        @unlink($tempDir.'/nested/configuration.markdown');
        @unlink($tempDir.'/nested/ignored.txt');
        @rmdir($tempDir.'/nested');
        @rmdir($tempDir);
    }
}
