<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesLocalitySchema;
use Tests\TestCase;

/**
 * `localities:import` is the quarterly refresh path, so it has to be safe to run
 * repeatedly against live data: no duplicates, no deletions, and no selectable
 * PO Boxes.
 */
class ImportLocalitiesTest extends TestCase
{
    use CreatesLocalitySchema;

    /** @var string */
    private $extractPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createLocalitySchema();
        $this->extractPath = tempnam(sys_get_temp_dir(), 'localities') . '.json';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->extractPath)) {
            unlink($this->extractPath);
        }
        parent::tearDown();
    }

    /**
     * @param array[] $localities
     */
    private function writeExtract(array $localities, $version = 'test-v1')
    {
        file_put_contents($this->extractPath, json_encode([
            'version' => $version,
            'localities' => $localities,
        ]));
    }

    private function runImport(array $options = [])
    {
        return $this->artisan('localities:import', array_merge(
            ['--file' => $this->extractPath],
            $options
        ));
    }

    private function locality($name, $state, $postcode, $latitude = -27.4, $longitude = 153.0)
    {
        return [
            'locality' => $name,
            'state' => $state,
            'postcode' => $postcode,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];
    }

    public function testImportInsertsLocalitiesWithNormalisedAndDisplayForms()
    {
        $this->writeExtract([$this->locality('Mckinnon', 'VIC', '3204')]);

        $this->runImport()->assertExitCode(0);

        $row = DB::table('suburbs')->first();
        // Upstream casing is naive; the import stores the corrected display form.
        $this->assertSame('McKinnon', $row->name);
        $this->assertSame('mckinnon', $row->name_normalised);
        $this->assertSame('VIC', $row->state_code);
        $this->assertSame('Victoria', $row->state);
        $this->assertSame('test-v1', $row->source_version);
    }

    public function testExternalTerritoriesAreExcludedBecauseTheyHaveNoStateRow()
    {
        $this->writeExtract([
            $this->locality('Ascot', 'QLD', '4007'),
            $this->locality('Christmas Island', 'OT', '6798'),
        ]);

        $this->runImport();

        $this->assertSame(1, DB::table('suburbs')->count());
        $this->assertSame('Ascot', DB::table('suburbs')->value('name'));
    }

    public function testNonPhysicalLocalitiesCanNeverBeSelected()
    {
        $this->writeExtract([
            $this->locality('Brisbane', 'QLD', '4000'),
            $this->locality('Brisbane PO Box', 'QLD', '4001'),
            $this->locality('Sydney Mail Centre', 'NSW', '2012'),
            $this->locality('Melbourne Large Volume Receiver', 'VIC', '3001'),
        ]);

        $this->runImport();

        $this->assertSame(['Brisbane'], DB::table('suburbs')->pluck('name')->all());
    }

    public function testLeadingZeroPostcodeIsStoredAsAString()
    {
        $this->writeExtract([$this->locality('Darwin City', 'NT', '0800')]);

        $this->runImport();

        $this->assertSame('0800', DB::table('suburbs')->value('postcode'));
    }

    public function testDuplicateRecordsInTheExtractAreCollapsed()
    {
        $this->writeExtract([
            $this->locality('Ascot', 'QLD', '4007'),
            $this->locality("ASCOT", 'QLD', '4007'),
        ]);

        $this->runImport();

        $this->assertSame(1, DB::table('suburbs')->count());
    }

    public function testRerunningTheImportDoesNotDuplicateRows()
    {
        $this->writeExtract([$this->locality('Ascot', 'QLD', '4007')]);

        $this->runImport();
        $this->runImport();

        $this->assertSame(1, DB::table('suburbs')->count());
    }

    public function testLocalitiesMissingFromAReleaseAreRetiredNotDeleted()
    {
        $this->writeExtract([
            $this->locality('Ascot', 'QLD', '4007'),
            $this->locality('Gone Place', 'QLD', '4999'),
        ]);
        $this->runImport();

        $this->writeExtract([$this->locality('Ascot', 'QLD', '4007')], 'test-v2');
        $this->runImport();

        $retired = DB::table('suburbs')->where('name', 'Gone Place')->first();
        $this->assertNotNull($retired, 'a retired locality must keep its row so old reviews resolve');
        $this->assertEquals(0, $retired->is_active);
    }

    public function testALocalityThatReturnsInALaterReleaseIsReactivated()
    {
        $this->writeExtract([$this->locality('Ascot', 'QLD', '4007')]);
        $this->runImport();

        $this->writeExtract([$this->locality('Somewhere Else', 'QLD', '4010')], 'test-v2');
        $this->runImport();

        $this->writeExtract([$this->locality('Ascot', 'QLD', '4007')], 'test-v3');
        $this->runImport();

        $this->assertEquals(1, DB::table('suburbs')->where('name', 'Ascot')->value('is_active'));
    }

    public function testDryRunWritesNothing()
    {
        $this->writeExtract([$this->locality('Ascot', 'QLD', '4007')]);

        $this->runImport(['--dry-run' => true]);

        $this->assertSame(0, DB::table('suburbs')->count());
    }

    public function testABareUpstreamArrayIsAccepted()
    {
        file_put_contents(
            $this->extractPath,
            json_encode([$this->locality('Ascot', 'QLD', '4007')])
        );

        $this->runImport(['--source-version' => 'upstream-direct'])->assertExitCode(0);

        $this->assertSame('upstream-direct', DB::table('suburbs')->value('source_version'));
    }
}
