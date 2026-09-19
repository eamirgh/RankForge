<?php

namespace Eamirgh\RankForge\Meta;

use Eamirgh\RankForge\RankForgeManager;
use Eamirgh\RankForge\Support\Sanitizer;

class TwitterCardRenderer
{
    public function __construct(
        protected RankForgeManager $manager,
    ) {}

    public function render(): string
    {
        if (! $this->manager->configGet('twitter.enabled', true)) {
            return '';
        }

        $twitter = $this->manager->getTwitter();
        $tags = [];

        $properties = [
            'card' => 'twitter:card',
            'site' => 'twitter:site',
            'creator' => 'twitter:creator',
            'title' => 'twitter:title',
            'description' => 'twitter:description',
            'image' => 'twitter:image',
            'image:alt' => 'twitter:image:alt',
        ];

        foreach ($properties as $key => $name) {
            if (isset($twitter[$key]) && $twitter[$key] !== '') {
                $tags[] = $this->tag($name, (string) $twitter[$key]);
            }
        }

        return implode(PHP_EOL, $tags);
    }

    protected function tag(string $name, string $content): string
    {
        return '<meta name="'.Sanitizer::escapeAttribute($name).'" content="'.Sanitizer::escapeAttribute($content).'">';
    }
}
