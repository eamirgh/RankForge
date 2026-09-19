<?php

namespace Eamirgh\RankForge\Commands;

use Illuminate\Console\Command;

class GenerateRobotsCommand extends Command
{
    protected $signature = 'rankforge:robots:generate {--path= : The output path for robots.txt (defaults to public_path(\'robots.txt\'))}';

    protected $aliases = ['rankforge:robots'];

    protected $description = 'Generate physical robots.txt file to public directory';

    public function handle(): int
    {
        $path = $this->option('path') !== null ? (string) $this->option('path') : null;

        $this->info('Generating robots.txt file to disk...');

        $success = app('rankforge.robots')->writeToDisk($path);

        if (! $success) {
            $this->error('Failed to generate robots.txt file.');

            return self::FAILURE;
        }

        $target = $path ?? (function_exists('public_path') ? public_path('robots.txt') : 'public/robots.txt');
        $this->info("Robots.txt successfully generated at [{$target}].");

        return self::SUCCESS;
    }
}
