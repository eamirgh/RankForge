<?php

namespace Eamirgh\RankForge\Tests\Unit\Schema;

use Eamirgh\RankForge\Schema\Types\Organization;
use Eamirgh\RankForge\Tests\TestCase;

class OrganizationSchemaTest extends TestCase
{
    public function test_it_generates_organization_schema(): void
    {
        $org = Organization::make()
            ->name('Acme Corp')
            ->url('https://acme.test')
            ->logo('https://acme.test/logo.png')
            ->sameAs(['https://twitter.com/acme', 'https://facebook.com/acme'])
            ->contactPoint('+1-800-555-0199', 'technical support', 'US');

        $orgArray = $org->toArray();

        $this->assertEquals('https://schema.org', $orgArray['@context']);
        $this->assertEquals('Organization', $orgArray['@type']);
        $this->assertEquals('Acme Corp', $orgArray['name']);
        $this->assertEquals('https://acme.test', $orgArray['url']);
        $this->assertEquals('https://acme.test/logo.png', $orgArray['logo']);
        $this->assertEquals(['https://twitter.com/acme', 'https://facebook.com/acme'], $orgArray['sameAs']);
        $this->assertEquals('ContactPoint', $orgArray['contactPoint']['@type']);
        $this->assertEquals('+1-800-555-0199', $orgArray['contactPoint']['telephone']);
        $this->assertEquals('technical support', $orgArray['contactPoint']['contactType']);
        $this->assertEquals('US', $orgArray['contactPoint']['areaServed']);
    }

    public function test_it_supports_multiple_contact_points(): void
    {
        $org = Organization::make()
            ->name('Acme Corp')
            ->contactPoint('+1-800-111-1111', 'sales')
            ->contactPoint('+1-800-222-2222', 'support');

        $orgArray = $org->toArray();

        $this->assertIsArray($orgArray['contactPoint']);
        $this->assertCount(2, $orgArray['contactPoint']);
        $this->assertEquals('+1-800-111-1111', $orgArray['contactPoint'][0]['telephone']);
        $this->assertEquals('+1-800-222-2222', $orgArray['contactPoint'][1]['telephone']);
    }
}
