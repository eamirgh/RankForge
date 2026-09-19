<?php

namespace Eamirgh\RankForge\Commands;

use Illuminate\Console\Command;
use Eamirgh\RankForge\Crawlers\LlmsTxtManager;

class GenerateLlmsCommand extends Command
{
    protected $signature = 'rankforge:llms:generate';

    protected $aliases = ['rankforge:llms'];

    protected $description = 'Generate the llms.txt and llms-full.txt files';

    public function handle(LlmsTxtManager $llmsTxtManager): int
    {
        $this->info('Generating llms.txt and llms-full.txt...');

        $files = $llmsTxtManager->writeToDisk();

        foreach ($files as $name => $path) {
            $this->line(" - Generated {$name}: {$path}");
        }

        $this->info('LLMs crawler files generated successfully.');

        return self::SUCCESS;
    }
}
