<?php

namespace RankForge\Schema\Types;

class LocalBusiness extends Organization
{
    public function __construct(string $type = 'LocalBusiness')
    {
        parent::__construct($type);
    }

    public function telephone(string $phone): static
    {
        return $this->setProperty('telephone', $phone);
    }

    public function priceRange(string $priceRange): static
    {
        return $this->setProperty('priceRange', $priceRange);
    }

    /**
     * @param  array<string, mixed>|string  $address
     */
    public function address(array|string $address): static
    {
        if (is_array($address) && ! isset($address['@type'])) {
            $address = array_merge(['@type' => 'PostalAddress'], $address);
        }

        return $this->setProperty('address', $address);
    }

    public function geo(float $latitude, float $longitude): static
    {
        return $this->setProperty('geo', [
            '@type' => 'GeoCoordinates',
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }

    /**
     * @param  string[]|string  $hours
     */
    public function openingHours(array|string $hours): static
    {
        return $this->setProperty('openingHours', is_array($hours) ? array_values($hours) : $hours);
    }
}
