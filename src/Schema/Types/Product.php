<?php

namespace Eamirgh\RankForge\Schema\Types;

class Product extends AbstractType
{
    public function __construct()
    {
        parent::__construct('Product');
    }

    public function name(string $name): static
    {
        return $this->setProperty('name', $name);
    }

    public function description(string $description): static
    {
        return $this->setProperty('description', $description);
    }

    /**
     * @param  string|string[]  $image
     */
    public function image(string|array $image): static
    {
        return $this->setProperty('image', $image);
    }

    public function sku(string $sku): static
    {
        return $this->setProperty('sku', $sku);
    }

    public function mpn(string $mpn): static
    {
        return $this->setProperty('mpn', $mpn);
    }

    /**
     * @param  string|array<string, mixed>|Organization  $brand
     */
    public function brand(string|array|Organization $brand): static
    {
        if (is_string($brand)) {
            $brand = [
                '@type' => 'Brand',
                'name' => $brand,
            ];
        }

        return $this->setProperty('brand', $brand);
    }

    /**
     * @param  Offer|Offer[]|array<mixed>  $offers
     */
    public function offers(Offer|array $offers): static
    {
        return $this->setProperty('offers', $offers);
    }

    public function aggregateRating(float $ratingValue, int $reviewCount, float $bestRating = 5, float $worstRating = 1): static
    {
        return $this->setProperty('aggregateRating', [
            '@type' => 'AggregateRating',
            'ratingValue' => $ratingValue,
            'reviewCount' => $reviewCount,
            'bestRating' => $bestRating,
            'worstRating' => $worstRating,
        ]);
    }
}
