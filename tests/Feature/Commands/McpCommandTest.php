<?php

namespace Eamirgh\RankForge\Tests\Feature\Commands;

use Eamirgh\RankForge\Mcp\McpServer;
use Eamirgh\RankForge\Tests\TestCase;
use Mockery;

class McpCommandTest extends TestCase
{
    public function test_it_starts_mcp_server(): void
    {
        $serverMock = Mockery::mock(McpServer::class);
        $serverMock->shouldReceive('listen')->once();
        $this->app->instance(McpServer::class, $serverMock);

        $this->artisan('rankforge:mcp')
            ->assertSuccessful();
    }

    public function test_it_works_via_alias(): void
    {
        $serverMock = Mockery::mock(McpServer::class);
        $serverMock->shouldReceive('listen')->once();
        $this->app->instance(McpServer::class, $serverMock);

        $this->artisan('rankforge:mcp:serve')
            ->assertSuccessful();
    }
}
