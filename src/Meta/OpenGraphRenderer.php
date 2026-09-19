<?php

namespace Eamirgh\RankForge\Meta;

use Eamirgh\RankForge\RankForgeManager;
use Eamirgh\RankForge\Support\Sanitizer;

class OpenGraphRenderer
{
    public function __construct(
        protected RankForgeManager $manager,
    ) {}

    public function render(): string
    {
        if (! $this->manager->configGet('open_graph.enabled', true)) {
            return '';
        }

        $og = $this->manager->getOpenGraph();
        $tags = [];

        // Standard OG properties
        $simpleProperties = [
            'title' => 'og:title',
            'description' => 'og:description',
            'type' => 'og:type',
            'url' => 'og:url',
            'site_name' => 'og:site_name',
            'locale' => 'og:locale',
        ];

        foreach ($simpleProperties as $key => $property) {
            if (isset($og[$key]) && $og[$key] !== '') {
                $tags[] = $this->tag($property, (string) $og[$key]);
            }
        }

        // Locale alternates
        if (isset($og['locale_alternate'])) {
            foreach ((array) $og['locale_alternate'] as $altLocale) {
                if ($altLocale !== '') {
                    $tags[] = $this->tag('og:locale:alternate', (string) $altLocale);
                }
            }
        }

        // Image (with dimensions, alt, secure URL, type)
        if (isset($og['image']) && $og['image'] !== '') {
            $tags[] = $this->tag('og:image', (string) $og['image']);

            if (isset($og['image_secure_url']) && $og['image_secure_url'] !== '') {
                $tags[] = $this->tag('og:image:secure_url', (string) $og['image_secure_url']);
            }

            if (isset($og['image_type']) && $og['image_type'] !== '') {
                $tags[] = $this->tag('og:image:type', (string) $og['image_type']);
            }

            if (isset($og['image_width']) && $og['image_width'] !== null) {
                $tags[] = $this->tag('og:image:width', (string) $og['image_width']);
            }

            if (isset($og['image_height']) && $og['image_height'] !== null) {
                $tags[] = $this->tag('og:image:height', (string) $og['image_height']);
            }

            if (isset($og['image_alt']) && $og['image_alt'] !== '') {
                $tags[] = $this->tag('og:image:alt', (string) $og['image_alt']);
            }
        }

        // Article metadata
        $articleProperties = [
            'article:published_time' => 'article:published_time',
            'article:modified_time' => 'article:modified_time',
            'article:section' => 'article:section',
        ];

        foreach ($articleProperties as $key => $prop) {
            if (isset($og[$key]) && $og[$key] !== '') {
                $tags[] = $this->tag($prop, (string) $og[$key]);
            }
        }

        if (isset($og['article:author'])) {
            foreach ((array) $og['article:author'] as $author) {
                if ($author !== '') {
                    $tags[] = $this->tag('article:author', (string) $author);
                }
            }
        }

        if (isset($og['article:tag'])) {
            foreach ((array) $og['article:tag'] as $tag) {
                if ($tag !== '') {
                    $tags[] = $this->tag('article:tag', (string) $tag);
                }
            }
        }

        return implode(PHP_EOL, $tags);
    }

    protected function tag(string $property, string $content): string
    {
        return '<meta property="'.Sanitizer::escapeAttribute($property).'" content="'.Sanitizer::escapeAttribute($content).'">';
    }
}
