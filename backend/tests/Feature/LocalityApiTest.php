<?php

namespace Tests\Feature;

use Tests\Feature\Concerns\CreatesLocalitySchema;
use Tests\TestCase;

/**
 * The four locality endpoints that both the public review form and the admin
 * portal read. Covers the cases that made free-text location unreliable:
 * duplicate suburb names, suburbs spanning several postcodes, and NT postcodes
 * that must stay strings.
 */
class LocalityApiTest extends TestCase
{
    use CreatesLocalitySchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createLocalitySchema();
    }

    public function testStatesReturnsTheEightStatesAndTerritories()
    {
        $response = $this->getJson('/api/localities/states');

        $response->assertStatus(200);
        $this->assertCount(8, $response->json('data'));
        $this->assertContains('QLD', array_column($response->json('data'), 'code'));
    }

    public function testStatesResponseIsCacheableForADay()
    {
        $response = $this->getJson('/api/localities/states');

        // Symfony re-serialises the directives alphabetically, so compare the
        // parsed set rather than the literal header we set.
        $directives = array_map('trim', explode(',', $response->headers->get('Cache-Control')));

        $this->assertContains('public', $directives);
        $this->assertContains('max-age=86400', $directives);

        // A publicly cached body must stay readable from the admin origin.
        // Same-origin callers do not send Origin, so these cannot be left to
        // the CORS middleware.
        $this->assertSame('*', $response->headers->get('Access-Control-Allow-Origin'));
        $vary = array_map('trim', explode(',', (string) $response->headers->get('Vary')));
        $this->assertContains('Origin', $vary);
    }

    public function testIndexReturnsActiveLocalitiesForOneStateOnly()
    {
        $this->createLocality(['name' => 'Ascot', 'postcode' => '4007', 'state_code' => 'QLD']);
        $this->createLocality(['name' => 'Ascot Vale', 'postcode' => '3032', 'state_code' => 'VIC']);
        $this->createLocality([
            'name' => 'Retired Place',
            'postcode' => '4000',
            'state_code' => 'QLD',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/localities?state=QLD');

        $response->assertStatus(200);
        $suburbs = array_column($response->json('data'), 'suburb');
        $this->assertSame(['Ascot'], $suburbs);
    }

    public function testIndexRejectsAnUnknownStateCode()
    {
        $this->getJson('/api/localities?state=ZZZ')->assertStatus(422);
        $this->getJson('/api/localities')->assertStatus(422);
    }

    public function testSuburbSpanningSeveralPostcodesReturnsEveryOptionLabelledByPostcode()
    {
        $this->createLocality(['name' => 'Melbourne', 'postcode' => '3000', 'state_code' => 'VIC']);
        $this->createLocality(['name' => 'Melbourne', 'postcode' => '3004', 'state_code' => 'VIC']);

        $response = $this->getJson('/api/localities?state=VIC');

        $labels = array_column($response->json('data'), 'label');
        sort($labels);
        $this->assertSame(['Melbourne (3000)', 'Melbourne (3004)'], $labels);
    }

    public function testDuplicateNameAcrossStatesIsLabelledWithTheStateCodeWhenNotScoped()
    {
        $this->createLocality(['name' => 'Richmond', 'postcode' => '3121', 'state_code' => 'VIC']);
        $this->createLocality(['name' => 'Richmond', 'postcode' => '2753', 'state_code' => 'NSW']);

        $response = $this->getJson('/api/localities/search?q=richmond');

        $labels = array_column($response->json('data'), 'label');
        sort($labels);
        $this->assertSame(['Richmond, NSW', 'Richmond, VIC'], $labels);
    }

    public function testSearchScopedToAStateOmitsTheStateCodeFromTheLabel()
    {
        $this->createLocality(['name' => 'Richmond', 'postcode' => '3121', 'state_code' => 'VIC']);
        $this->createLocality(['name' => 'Richmond', 'postcode' => '2753', 'state_code' => 'NSW']);

        $response = $this->getJson('/api/localities/search?q=richmond&state=VIC');

        $this->assertSame(['Richmond'], array_column($response->json('data'), 'label'));
    }

    public function testSearchOrdersPrefixMatchesAheadOfContainsMatches()
    {
        $this->createLocality(['name' => 'Mount Ascot', 'postcode' => '4001', 'state_code' => 'QLD']);
        $this->createLocality(['name' => 'Ascot', 'postcode' => '4007', 'state_code' => 'QLD']);

        $response = $this->getJson('/api/localities/search?q=ascot&state=QLD');

        $this->assertSame(
            ['Ascot', 'Mount Ascot'],
            array_column($response->json('data'), 'suburb')
        );
    }

    public function testSearchIgnoresApostrophesAndCasing()
    {
        $this->createLocality(['name' => "O'Connor", 'postcode' => '2602', 'state_code' => 'ACT']);

        $response = $this->getJson('/api/localities/search?q=OCONN&state=ACT');

        $this->assertSame("O'Connor", $response->json('data.0.suburb'));
    }

    public function testSearchBelowTheMinimumLengthReturnsNothingRatherThanEverything()
    {
        $this->createLocality(['name' => 'Ascot', 'postcode' => '4007', 'state_code' => 'QLD']);

        $response = $this->getJson('/api/localities/search?q=a&state=QLD');

        $response->assertStatus(200);
        $this->assertSame([], $response->json('data'));
    }

    public function testNorthernTerritoryPostcodeKeepsItsLeadingZero()
    {
        $this->createLocality(['name' => 'Darwin City', 'postcode' => '0800', 'state_code' => 'NT']);

        $response = $this->getJson('/api/localities/by-postcode?postcode=0800');

        $response->assertStatus(200);
        $this->assertSame('0800', $response->json('data.0.postcode'));
    }

    public function testByPostcodeReturnsEverySuburbSharingThePostcode()
    {
        $this->createLocality(['name' => 'Hamilton', 'postcode' => '4007', 'state_code' => 'QLD']);
        $this->createLocality(['name' => 'Ascot', 'postcode' => '4007', 'state_code' => 'QLD']);

        $response = $this->getJson('/api/localities/by-postcode?postcode=4007');

        $this->assertSame(
            ['Ascot', 'Hamilton'],
            array_column($response->json('data'), 'suburb')
        );
    }

    public function testByPostcodeRejectsAnythingThatIsNotFourDigits()
    {
        $this->getJson('/api/localities/by-postcode?postcode=40')->assertStatus(422);
        $this->getJson('/api/localities/by-postcode?postcode=abcd')->assertStatus(422);
    }
}
