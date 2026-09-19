<?php

namespace Eamirgh\RankForge\Schema\Types;

class SoftwareApplication extends AbstractType
{
    public function __construct()
    {
        parent::__construct('SoftwareApplication');
    }

    public function name(string $name): static
    {
        return $this->setProperty('name', $name);
    }

    public function operatingSystem(string $os): static
    {
        return $this->setProperty('operatingSystem', $os);
    }

    public function applicationCategory(string $category): static
    {
        return $this->setProperty('applicationCategory', $category);
    }

    public function aggregateRating(float $ratingValue, int $ratingCount): static
    {
        return $this->setProperty('aggregateRating', [
            '@type' => 'AggregateRating',
            'ratingValue' => $ratingValue,
            'ratingCount' => $ratingCount,
        ]);
    }

    /**
     * @param  Offer|Offer[]|array<mixed>  $offers
     */
    public function offers(Offer|array $offers): static
    {
        return $this->setProperty('offers', $offers);
    }
}
