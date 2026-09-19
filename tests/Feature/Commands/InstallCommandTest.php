<?php

namespace Eamirgh\RankForge\Tests\Feature\Commands;

use Eamirgh\RankForge\Tests\TestCase;

class InstallCommandTest extends TestCase
{
    public function test_it_executes_install_command_successfully(): void
    {
        $this->artisan('rankforge:install')
            ->expectsOutputToContain('Publishing RankForge configuration...')
            ->expectsOutputToContain('RankForge installed successfully.')
            ->assertSuccessful();
    }
}
