<?php

namespace Tests\Feature;

use App\Http\Controllers\ReviewController;
use App\Models\Review;
use App\Support\ReviewLocalityMatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tests\Feature\Concerns\CreatesLocalitySchema;
use Tests\TestCase;

/**
 * Attaching a review to a locality: the validation that blocks an unresolvable
 * submission, and the backfill matcher that must never guess between candidates.
 */
class ReviewLocalityTest extends TestCase
{
    use CreatesLocalitySchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createLocalitySchema();
    }

    /**
     * @param array $overrides
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validateReview(array $overrides)
    {
        return Validator::make(array_merge([
            'title' => 'A review',
            'user_id' => 1,
            'property_id' => 1,
            'category_id' => 1,
            'recommendation' => 1,
            'buyer_type' => 1,
            'status' => 1,
            'state' => 4,
            'suburb' => 'Ascot',
            'post_code' => '4007',
        ], $overrides), Review::$rules);
    }

    public function testSubmissionIsBlockedWithoutALocality()
    {
        $validator = $this->validateReview([]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('locality_id', $validator->errors()->toArray());
    }

    public function testSubmissionIsBlockedWhenTheLocalityDoesNotExist()
    {
        $validator = $this->validateReview(['locality_id' => 9999]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('locality_id', $validator->errors()->toArray());
    }

    public function testSubmissionPassesWithARealLocality()
    {
        $id = $this->createLocality([
            'name' => 'Ascot',
            'postcode' => '4007',
            'state_code' => 'QLD',
        ]);

        $validator = $this->validateReview(['locality_id' => $id]);

        $this->assertFalse($validator->fails(), json_encode($validator->errors()->toArray()));
    }

    /**
     * Call the controller's private property-location guard.
     *
     * @return array the review data after the guard has run
     */
    private function applyPropertyLocation(array $data, $propertyId)
    {
        $controller = new ReviewController();
        $method = new \ReflectionMethod($controller, 'applyPropertyLocationToReviewData');
        $method->setAccessible(true);

        return $method->invoke($controller, $data, $propertyId);
    }

    public function testPropertyLocationOverridesThePostedLocality()
    {
        $ascotId = $this->createLocality(['name' => 'Ascot', 'postcode' => '4007', 'state_code' => 'QLD']);
        $carltonId = $this->createLocality(['name' => 'Carlton', 'postcode' => '3053', 'state_code' => 'VIC']);
        $propertyId = DB::table('properties')->insertGetId([
            'name' => 'A tower in Ascot',
            'state' => 'Queensland',
            'suburb' => 'Ascot',
            'post_code' => '4007',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $data = $this->applyPropertyLocation(['locality_id' => $carltonId], $propertyId);

        $this->assertSame($ascotId, $data['locality_id']);
        $this->assertSame('Ascot', $data['suburb']);
        $this->assertSame('4007', $data['post_code']);
    }

    /**
     * `properties` holds free text, so a property can carry a suburb or postcode
     * that is not in the reference data. That must not discard the locality the
     * reviewer picked, or the review becomes unsubmittable.
     */
    public function testAnUnresolvablePropertyLocationFallsBackToThePostedLocality()
    {
        $ascotId = $this->createLocality(['name' => 'Ascot', 'postcode' => '4007', 'state_code' => 'QLD']);
        $propertyId = DB::table('properties')->insertGetId([
            'name' => 'A tower with a typo',
            'state' => 'Queensland',
            'suburb' => 'Asscot',
            'post_code' => '4007',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $data = $this->applyPropertyLocation(['locality_id' => $ascotId], $propertyId);

        $this->assertSame($ascotId, $data['locality_id']);
        $this->assertSame('Ascot', $data['suburb']);
    }

    public function testMatcherResolvesOnNameAndState()
    {
        $id = $this->createLocality([
            'name' => 'Ascot',
            'postcode' => '4007',
            'state_code' => 'QLD',
        ]);
        $queenslandId = DB::table('review_states')->where('abbreviation', 'QLD')->value('id');

        $result = ReviewLocalityMatcher::match('Ascot', null, $queenslandId);

        $this->assertNotNull($result['locality']);
        $this->assertEquals($id, $result['locality']->id);
        $this->assertSame('name and state', $result['matched_on']);
    }

    public function testMatcherFallsBackToPostcodeWhenTheStateIsMissing()
    {
        $id = $this->createLocality([
            'name' => 'Ascot',
            'postcode' => '4007',
            'state_code' => 'QLD',
        ]);

        $result = ReviewLocalityMatcher::match('Ascot', '4007', null);

        $this->assertEquals($id, $result['locality']->id);
        $this->assertSame('name and postcode', $result['matched_on']);
    }

    public function testMatcherResolvesATruncatedNameByPostcodePrefix()
    {
        $id = $this->createLocality([
            'name' => 'Ascot Vale',
            'postcode' => '3032',
            'state_code' => 'VIC',
        ]);

        $result = ReviewLocalityMatcher::match('Ascot', '3032', null);

        $this->assertEquals($id, $result['locality']->id);
        $this->assertSame('postcode and name prefix', $result['matched_on']);
    }

    public function testMatcherIgnoresPunctuationAndCasing()
    {
        $id = $this->createLocality([
            'name' => "O'Connor",
            'postcode' => '2602',
            'state_code' => 'ACT',
        ]);
        $actId = DB::table('review_states')->where('abbreviation', 'ACT')->value('id');

        $result = ReviewLocalityMatcher::match('OCONNOR', null, $actId);

        $this->assertEquals($id, $result['locality']->id);
    }

    public function testMatcherRefusesToGuessBetweenSeveralPostcodes()
    {
        $this->createLocality(['name' => 'Melbourne', 'postcode' => '3000', 'state_code' => 'VIC']);
        $this->createLocality(['name' => 'Melbourne', 'postcode' => '3004', 'state_code' => 'VIC']);
        $victoriaId = DB::table('review_states')->where('abbreviation', 'VIC')->value('id');

        $result = ReviewLocalityMatcher::match('Melbourne', null, $victoriaId);

        $this->assertNull($result['locality']);
        $this->assertSame(ReviewLocalityMatcher::REASON_AMBIGUOUS, $result['reason']);
    }

    public function testMatcherReportsAnUnknownSuburb()
    {
        $result = ReviewLocalityMatcher::match('Nowhere', '9999', null);

        $this->assertNull($result['locality']);
        $this->assertSame(ReviewLocalityMatcher::REASON_UNMATCHED, $result['reason']);
    }

    public function testMatcherReportsAMissingSuburb()
    {
        $result = ReviewLocalityMatcher::match('', '4007', null);

        $this->assertNull($result['locality']);
        $this->assertSame(ReviewLocalityMatcher::REASON_NO_SUBURB, $result['reason']);
    }

    public function testMatcherStillResolvesARetiredLocality()
    {
        $id = $this->createLocality([
            'name' => 'Gone Place',
            'postcode' => '4999',
            'state_code' => 'QLD',
            'is_active' => false,
        ]);
        $queenslandId = DB::table('review_states')->where('abbreviation', 'QLD')->value('id');

        $result = ReviewLocalityMatcher::match('Gone Place', null, $queenslandId);

        $this->assertEquals($id, $result['locality']->id);
    }

    public function testBackfillAttachesResolvableReviewsAndLeavesTheRestAlone()
    {
        $ascotId = $this->createLocality([
            'name' => 'Ascot',
            'postcode' => '4007',
            'state_code' => 'QLD',
        ]);
        $queenslandId = DB::table('review_states')->where('abbreviation', 'QLD')->value('id');

        DB::table('reviews')->insert([
            [
                'title' => 'Resolvable',
                'state' => $queenslandId,
                'suburb' => 'Ascot',
                'post_code' => '4007',
                'locality_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Unresolvable',
                'state' => $queenslandId,
                'suburb' => 'Nowhere At All',
                'post_code' => '9999',
                'locality_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->artisan('reviews:backfill-localities', ['--dry-run' => true]);
        $this->assertNull(DB::table('reviews')->where('title', 'Resolvable')->value('locality_id'));

        $this->artisan('reviews:backfill-localities');

        $this->assertEquals(
            $ascotId,
            DB::table('reviews')->where('title', 'Resolvable')->value('locality_id')
        );
        $this->assertNull(DB::table('reviews')->where('title', 'Unresolvable')->value('locality_id'));
    }
}
