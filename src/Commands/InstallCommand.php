<?php

namespace RankForge\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'rankforge:install';

    protected $description = 'Install and publish RankForge configurations and views';

    public function handle(): int
    {
        $this->info('Publishing RankForge configuration...');
        $this->call('vendor:publish', [
            '--tag' => 'rankforge-config',
        ]);

        $this->info('Publishing RankForge views...');
        $this->call('vendor:publish', [
            '--tag' => 'rankforge-views',
        ]);

        $this->info('RankForge installed successfully.');

        return self::SUCCESS;
    }
}
