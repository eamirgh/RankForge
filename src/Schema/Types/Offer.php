<?php

namespace RankForge\Schema\Types;

use DateTimeInterface;

class Offer extends AbstractType
{
    public function __construct()
    {
        parent::__construct('Offer');
    }

    public function price(float|string $price): static
    {
        return $this->setProperty('price', $price);
    }

    public function priceCurrency(string $currency = 'USD'): static
    {
        return $this->setProperty('priceCurrency', $currency);
    }

    public function availability(string $availability): static
    {
        return $this->setProperty('availability', $availability);
    }

    public function priceValidUntil(string|DateTimeInterface $date): static
    {
        return $this->setProperty('priceValidUntil', $date);
    }

    public function url(string $url): static
    {
        return $this->setProperty('url', $url);
    }
}
