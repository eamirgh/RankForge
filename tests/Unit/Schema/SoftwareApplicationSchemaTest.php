<?php

namespace Eamirgh\RankForge\Tests\Unit\Schema;

use Eamirgh\RankForge\Schema\Types\Offer;
use Eamirgh\RankForge\Schema\Types\SoftwareApplication;
use Eamirgh\RankForge\Tests\TestCase;

class SoftwareApplicationSchemaTest extends TestCase
{
    public function test_it_generates_software_application_schema(): void
    {
        $app = SoftwareApplication::make()
            ->name('RankForge CLI')
            ->operatingSystem('Linux, macOS, Windows')
            ->applicationCategory('DeveloperApplication')
            ->aggregateRating(4.9, 50);

        $array = $app->toArray();

        $this->assertEquals('https://schema.org', $array['@context']);
        $this->assertEquals('SoftwareApplication', $array['@type']);
        $this->assertEquals('RankForge CLI', $array['name']);
        $this->assertEquals('Linux, macOS, Windows', $array['operatingSystem']);
        $this->assertEquals('DeveloperApplication', $array['applicationCategory']);
        $this->assertEquals('AggregateRating', $array['aggregateRating']['@type']);
        $this->assertEquals(4.9, $array['aggregateRating']['ratingValue']);
        $this->assertEquals(50, $array['aggregateRating']['ratingCount']);
    }

    public function test_it_supports_software_application_offers(): void
    {
        $offer = Offer::make()->price(0)->priceCurrency('USD');

        $app = SoftwareApplication::make()
            ->name('Free App')
            ->offers($offer);

        $array = $app->toArray();
        $this->assertEquals('Offer', $array['offers']['@type']);
        $this->assertEquals(0, $array['offers']['price']);
    }
}
