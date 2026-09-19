<?php

namespace Eamirgh\RankForge\Commands;

use Eamirgh\RankForge\Mcp\McpServer;
use Illuminate\Console\Command;

class McpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rankforge:mcp';

    /**
     * The console command aliases.
     *
     * @var array<int, string>
     */
    protected $aliases = ['rankforge:mcp:serve'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the RankForge Model Context Protocol (MCP) server over stdio';

    /**
     * Execute the console command.
     */
    public function handle(McpServer $server): int
    {
        $server->listen();

        return self::SUCCESS;
    }
}
