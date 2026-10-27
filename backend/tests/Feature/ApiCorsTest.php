<?php

namespace Tests\Feature;

use Tests\Feature\Concerns\CreatesLocalitySchema;
use Tests\TestCase;

/**
 * CORS on the public API must be the same on every response. The locality
 * payloads are cached in the browser for a day, and a copy stored for one
 * subdomain was being reused for the other.
 */
class ApiCorsTest extends TestCase
{
    use CreatesLocalitySchema;

    const CMS_ORIGIN = 'https://ratemyestatecms-react.castledigital.com.au';

    protected function setUp(): void
    {
        parent::setUp();
        $this->createLocalitySchema();
    }

    public function testCachedStatesResponseAllowsAnyOriginWithoutCredentials()
    {
        $response = $this->getJson('/api/localities/states', [
            'Origin' => self::CMS_ORIGIN,
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Access-Control-Allow-Origin', '*');
        $this->assertFalse($response->headers->has('Access-Control-Allow-Credentials'));
    }

    public function testSameOriginStatesResponseIsAlsoWildcardSoASharedCacheStaysValid()
    {
        $response = $this->getJson('/api/localities/states');

        $response->assertStatus(200);
        $response->assertHeader('Access-Control-Allow-Origin', '*');
        $this->assertFalse($response->headers->has('Access-Control-Allow-Credentials'));
    }

    public function testPreflightAllowsAnyOriginWithoutCredentials()
    {
        $response = $this->call(
            'OPTIONS',
            '/api/localities/states',
            [],
            [],
            [],
            $this->transformHeadersToServerVars([
                'Origin' => self::CMS_ORIGIN,
                'Access-Control-Request-Method' => 'GET',
                'Access-Control-Request-Headers' => 'authorization,content-type,accept',
            ])
        );

        $response->assertStatus(204);
        $response->assertHeader('Access-Control-Allow-Origin', '*');
        $response->assertHeader('Access-Control-Allow-Headers', 'AUTHORIZATION,CONTENT-TYPE,ACCEPT');
        $this->assertFalse($response->headers->has('Access-Control-Allow-Credentials'));
    }

    public function testApiErrorsStillAdvertiseTheWildcardOrigin()
    {
        $response = $this->getJson('/api/does-not-exist', [
            'Origin' => self::CMS_ORIGIN,
        ]);

        $response->assertStatus(404);
        $response->assertHeader('Access-Control-Allow-Origin', '*');
        $this->assertFalse($response->headers->has('Access-Control-Allow-Credentials'));
    }

    public function testRateLimitedApiResponseStillAdvertisesTheWildcardOrigin()
    {
        $response = null;

        for ($attempt = 0; $attempt < 61; $attempt++) {
            $response = $this->getJson('/api/localities/states', [
                'Origin' => self::CMS_ORIGIN,
            ]);
        }

        $response->assertStatus(429);
        $response->assertHeader('Access-Control-Allow-Origin', '*');
        $this->assertFalse($response->headers->has('Access-Control-Allow-Credentials'));
    }
}
