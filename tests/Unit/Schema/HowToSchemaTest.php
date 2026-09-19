<?php

namespace Eamirgh\RankForge\Tests\Unit\Schema;

use Eamirgh\RankForge\Schema\Types\HowTo;
use Eamirgh\RankForge\Tests\TestCase;

class HowToSchemaTest extends TestCase
{
    public function test_it_generates_how_to_schema(): void
    {
        $howTo = HowTo::make()
            ->name('How to Install RankForge')
            ->description('Step by step guide')
            ->totalTime('PT5M')
            ->supply(['Composer', 'PHP 8.2+'])
            ->tool(['Terminal'])
            ->addStep('Require Package', 'Run composer require eamirgh/rankforge', 'https://example.com/step1');

        $array = $howTo->toArray();

        $this->assertEquals('https://schema.org', $array['@context']);
        $this->assertEquals('HowTo', $array['@type']);
        $this->assertEquals('How to Install RankForge', $array['name']);
        $this->assertEquals('Step by step guide', $array['description']);
        $this->assertEquals('PT5M', $array['totalTime']);
        $this->assertEquals('HowToSupply', $array['supply'][0]['@type']);
        $this->assertEquals('Composer', $array['supply'][0]['name']);
        $this->assertEquals('HowToTool', $array['tool'][0]['@type']);
        $this->assertEquals('Terminal', $array['tool'][0]['name']);
        $this->assertEquals('HowToStep', $array['step'][0]['@type']);
        $this->assertEquals('Require Package', $array['step'][0]['name']);
        $this->assertEquals('Run composer require eamirgh/rankforge', $array['step'][0]['text']);
        $this->assertEquals('https://example.com/step1', $array['step'][0]['url']);
    }

    public function test_it_supports_steps_with_images(): void
    {
        $howTo = HowTo::make()
            ->name('Setup with image')
            ->addStep('Step 1', 'Do something', null, 'https://example.com/step1.png');

        $array = $howTo->toArray();
        $this->assertEquals('https://example.com/step1.png', $array['step'][0]['image']);
    }
}
