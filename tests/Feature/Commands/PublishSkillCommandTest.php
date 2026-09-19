<?php

namespace Eamirgh\RankForge\Tests\Feature\Commands;

use Eamirgh\RankForge\Tests\TestCase;
use Illuminate\Support\Facades\File;

class PublishSkillCommandTest extends TestCase
{
    protected string $testSkillPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testSkillPath = base_path('.agents/skills/rankforge/SKILL.md');
    }

    protected function tearDown(): void
    {
        if (File::exists(dirname($this->testSkillPath))) {
            File::deleteDirectory(base_path('.agents'));
        }
        parent::tearDown();
    }

    public function test_it_publishes_skill_file(): void
    {
        $this->artisan('rankforge:skill')
            ->expectsOutputToContain('published successfully')
            ->assertSuccessful();

        $this->assertTrue(File::exists($this->testSkillPath));
        $content = File::get($this->testSkillPath);
        $this->assertStringContainsString('Skill: rankforge', $content);
    }

    public function test_it_publishes_skill_file_with_custom_path(): void
    {
        $customPath = base_path('custom-skills/SKILL.md');

        $this->artisan('rankforge:skill', ['--path' => $customPath])
            ->assertSuccessful();

        $this->assertTrue(File::exists($customPath));
        File::deleteDirectory(base_path('custom-skills'));
    }
}
