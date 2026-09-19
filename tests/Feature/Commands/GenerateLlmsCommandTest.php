<?php

namespace Eamirgh\RankForge\Tests\Feature\Commands;

use Eamirgh\RankForge\Tests\TestCase;

class GenerateLlmsCommandTest extends TestCase
{
    public function test_it_generates_llms_txt_files(): void
    {
        $this->artisan('rankforge:llms:generate')
            ->expectsOutputToContain('Generating llms.txt and llms-full.txt...')
            ->expectsOutputToContain('LLMs crawler files generated successfully.')
            ->assertSuccessful();
    }
}
