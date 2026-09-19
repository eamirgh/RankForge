<?php

namespace Eamirgh\RankForge\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishSkillCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rankforge:skill {--path= : The destination path for the skill file}';

    /**
     * The console command aliases.
     *
     * @var array<int, string>
     */
    protected $aliases = ['rankforge:publish:skill'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish the RankForge agent skill file (SKILL.md) to your application';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sourcePath = __DIR__.'/../../SKILL.md';

        if (! File::exists($sourcePath)) {
            $sourcePath = __DIR__.'/../../.agents/skills/rankforge/SKILL.md';
        }

        if (! File::exists($sourcePath)) {
            $this->error('Skill source file (SKILL.md) could not be located.');

            return self::FAILURE;
        }

        $destinationPath = $this->option('path')
            ? (string) $this->option('path')
            : base_path('.agents/skills/rankforge/SKILL.md');

        $destinationDir = dirname($destinationPath);

        if (! File::isDirectory($destinationDir)) {
            File::makeDirectory($destinationDir, 0755, true);
        }

        File::copy($sourcePath, $destinationPath);

        $this->info("RankForge agent skill published successfully to [{$destinationPath}].");

        return self::SUCCESS;
    }
}
