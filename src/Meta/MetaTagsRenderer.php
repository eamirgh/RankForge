<?php

namespace RankForge\Meta;

use RankForge\RankForgeManager;
use RankForge\Support\Sanitizer;

class MetaTagsRenderer
{
    public function __construct(
        protected RankForgeManager $manager,
    ) {}

    public function render(): string
    {
        $tags = [];

        // Title tag
        $title = $this->manager->getRenderedTitle();
        if ($title !== '') {
            $tags[] = '<title>'.Sanitizer::escapeAttribute($title).'</title>';
        }

        // Meta description
        $description = $this->manager->getDescription();
        if ($description !== '') {
            $tags[] = '<meta name="description" content="'.Sanitizer::escapeAttribute($description).'">';
        }

        // Meta keywords
        $keywords = $this->manager->getKeywordsString();
        if ($keywords !== '') {
            $tags[] = '<meta name="keywords" content="'.Sanitizer::escapeAttribute($keywords).'">';
        }

        // Robots
        $robots = $this->manager->getRobots();
        if ($robots !== '') {
            $tags[] = '<meta name="robots" content="'.Sanitizer::escapeAttribute($robots).'">';
        }

        // Custom robots tags (e.g. googlebot, bingbot)
        foreach ($this->manager->getCustomRobots() as $name => $content) {
            if ($content !== '') {
                $tags[] = '<meta name="'.Sanitizer::escapeAttribute($name).'" content="'.Sanitizer::escapeAttribute($content).'">';
            }
        }

        // Canonical URL
        $canonical = $this->manager->getCanonicalUrl();
        if ($canonical !== null) {
            $tags[] = '<link rel="canonical" href="'.Sanitizer::escapeAttribute($canonical).'">';
        }

        // Hreflang
        if ($this->manager->isHreflangEnabled()) {
            $entries = $this->manager->getHreflangEntries();
            foreach ($entries as $locale => $url) {
                $tags[] = '<link rel="alternate" hreflang="'.Sanitizer::escapeAttribute($locale).'" href="'.Sanitizer::escapeAttribute($url).'">';
            }
        }

        return implode(PHP_EOL, $tags);
    }
}
