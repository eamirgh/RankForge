<?php

namespace Eamirgh\RankForge\Tests\Unit\Schema;

use Eamirgh\RankForge\Schema\Types\LocalBusiness;
use Eamirgh\RankForge\Tests\TestCase;

class LocalBusinessSchemaTest extends TestCase
{
    public function test_it_generates_local_business_schema(): void
    {
        $biz = LocalBusiness::make()
            ->name('Acme Cafe')
            ->telephone('+1-555-1234')
            ->priceRange('$$')
            ->address([
                'streetAddress' => '123 Main St',
                'addressLocality' => 'Austin',
                'addressRegion' => 'TX',
                'postalCode' => '78701',
            ])
            ->geo(30.2672, -97.7431)
            ->openingHours(['Mo-Fr 08:00-18:00', 'Sa 09:00-15:00']);

        $bizArray = $biz->toArray();

        $this->assertEquals('https://schema.org', $bizArray['@context']);
        $this->assertEquals('LocalBusiness', $bizArray['@type']);
        $this->assertEquals('Acme Cafe', $bizArray['name']);
        $this->assertEquals('+1-555-1234', $bizArray['telephone']);
        $this->assertEquals('$$', $bizArray['priceRange']);
        $this->assertEquals('PostalAddress', $bizArray['address']['@type']);
        $this->assertEquals('123 Main St', $bizArray['address']['streetAddress']);
        $this->assertEquals('GeoCoordinates', $bizArray['geo']['@type']);
        $this->assertEquals(30.2672, $bizArray['geo']['latitude']);
        $this->assertEquals(-97.7431, $bizArray['geo']['longitude']);
        $this->assertCount(2, $bizArray['openingHours']);
    }

    public function test_it_supports_string_address_and_single_opening_hours(): void
    {
        $biz = LocalBusiness::make()
            ->name('Simple Cafe')
            ->address('123 Main St, Austin, TX')
            ->openingHours('Mo-Su 09:00-17:00');

        $bizArray = $biz->toArray();

        $this->assertEquals('123 Main St, Austin, TX', $bizArray['address']);
        $this->assertEquals('Mo-Su 09:00-17:00', $bizArray['openingHours']);
    }
}
