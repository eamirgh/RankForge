<?php

namespace Eamirgh\RankForge\Tests\Unit\Schema;

use Eamirgh\RankForge\Schema\Types\Offer;
use Eamirgh\RankForge\Schema\Types\Product;
use Eamirgh\RankForge\Tests\TestCase;

class ProductAndOfferSchemaTest extends TestCase
{
    public function test_it_generates_product_and_offer_schema(): void
    {
        $offer = Offer::make()
            ->price(29.99)
            ->priceCurrency('USD')
            ->availability('https://schema.org/InStock')
            ->url('https://example.com/buy/product-1');

        $product = Product::make()
            ->name('Acme Widget')
            ->description('High quality widget')
            ->image('https://example.com/widget.jpg')
            ->sku('WIDGET-123')
            ->mpn('MPN-999')
            ->brand('Acme')
            ->offers($offer)
            ->aggregateRating(4.8, 125, 5, 1);

        $array = $product->toArray();

        $this->assertEquals('https://schema.org', $array['@context']);
        $this->assertEquals('Product', $array['@type']);
        $this->assertEquals('Acme Widget', $array['name']);
        $this->assertEquals('High quality widget', $array['description']);
        $this->assertEquals('https://example.com/widget.jpg', $array['image']);
        $this->assertEquals('WIDGET-123', $array['sku']);
        $this->assertEquals('MPN-999', $array['mpn']);
        $this->assertEquals('Brand', $array['brand']['@type']);
        $this->assertEquals('Acme', $array['brand']['name']);
        $this->assertEquals('Offer', $array['offers']['@type']);
        $this->assertEquals(29.99, $array['offers']['price']);
        $this->assertEquals('USD', $array['offers']['priceCurrency']);
        $this->assertEquals('https://schema.org/InStock', $array['offers']['availability']);
        $this->assertEquals('AggregateRating', $array['aggregateRating']['@type']);
        $this->assertEquals(4.8, $array['aggregateRating']['ratingValue']);
        $this->assertEquals(125, $array['aggregateRating']['reviewCount']);
    }

    public function test_it_supports_offer_price_valid_until(): void
    {
        $offer = Offer::make()
            ->price(19.99)
            ->priceValidUntil('2026-12-31');

        $array = $offer->toArray();
        $this->assertEquals(19.99, $array['price']);
        $this->assertEquals('2026-12-31', $array['priceValidUntil']);
    }
}
