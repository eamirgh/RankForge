<?php

namespace RankForge\Schema\Types;

class WebSite extends AbstractType
{
    public function __construct()
    {
        parent::__construct('WebSite');
    }

    public function name(string $name): static
    {
        return $this->setProperty('name', $name);
    }

    public function url(string $url): static
    {
        return $this->setProperty('url', $url);
    }

    public function searchAction(string $targetUrl, string $queryInput = 'required name=search_term_string'): static
    {
        return $this->setProperty('potentialAction', [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => $targetUrl,
            ],
            'query-input' => $queryInput,
        ]);
    }
}
