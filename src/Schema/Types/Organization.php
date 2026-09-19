<?php

namespace RankForge\Schema\Types;

class Organization extends AbstractType
{
    public function __construct(string $type = 'Organization')
    {
        parent::__construct($type);
    }

    public function name(string $name): static
    {
        return $this->setProperty('name', $name);
    }

    public function url(string $url): static
    {
        return $this->setProperty('url', $url);
    }

    public function logo(string $logoUrl): static
    {
        return $this->setProperty('logo', $logoUrl);
    }

    /**
     * @param  string[]  $socialUrls
     */
    public function sameAs(array $socialUrls): static
    {
        return $this->setProperty('sameAs', array_values($socialUrls));
    }

    public function contactPoint(string $telephone, string $contactType = 'customer support', ?string $areaServed = null): static
    {
        $point = [
            '@type' => 'ContactPoint',
            'telephone' => $telephone,
            'contactType' => $contactType,
        ];

        if ($areaServed !== null) {
            $point['areaServed'] = $areaServed;
        }

        $current = $this->getProperty('contactPoint');

        if ($current === null) {
            $this->setProperty('contactPoint', $point);
        } elseif (is_array($current) && isset($current['@type'])) {
            $this->setProperty('contactPoint', [$current, $point]);
        } elseif (is_array($current)) {
            $current[] = $point;
            $this->setProperty('contactPoint', $current);
        }

        return $this;
    }
}
