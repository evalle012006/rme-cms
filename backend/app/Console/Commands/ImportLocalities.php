<?php

namespace App\Console\Commands;

use App\Models\ReviewStates;
use App\Models\Suburbs;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Seed and refresh the locality reference table from a G-NAF derived extract.
 *
 * Run quarterly, after Geoscape publishes a new G-NAF release. Rows absent from
 * the release are marked inactive rather than deleted, so reviews that reference
 * a retired locality keep resolving.
 */
class ImportLocalities extends Command
{
    protected $signature = 'localities:import
                            {--file= : Path to the extract (defaults to database/data/localities-gnaf.json)}
                            {--source-version= : Release identifier stored on each row (defaults to the file version). Not --version, which Artisan reserves.}
                            {--dry-run : Print counts without writing}';

    protected $description = 'Import or refresh localities from a G-NAF derived extract';

    const DEFAULT_FILE = 'data/localities-gnaf.json';
    const CHUNK_SIZE = 500;

    /**
     * Non-physical postcodes must never be selectable as a locality. The
     * upstream extract is built from physical addresses so these should not
     * appear, but guard anyway in case a different source is passed via --file.
     */
    const NON_PHYSICAL_PATTERN = '/\b(po box|p\.o\. box|post office box|private bag|locked bag|mail centre|mail service|large volume receiver|lvr|community mail)\b/i';

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');
        $path = $this->option('file') ?: database_path(self::DEFAULT_FILE);

        if (!file_exists($path)) {
            $this->error("Extract not found at {$path}");
            return 1;
        }

        $payload = json_decode(file_get_contents($path), true);
        if (!is_array($payload)) {
            $this->error('Extract is not valid JSON.');
            return 1;
        }

        // Accept either the committed envelope or a bare upstream array, so a
        // freshly downloaded file can be passed straight to --file.
        $records = isset($payload['localities']) ? $payload['localities'] : $payload;
        if (!is_array($records) || empty($records)) {
            $this->error('Extract contains no localities.');
            return 1;
        }

        $version = $this->option('source-version')
            ?: (isset($payload['version']) ? $payload['version'] : basename($path) . '@' . date('Y-m-d', filemtime($path)));

        $stateNames = $this->stateNamesByCode();
        if ($stateNames->isEmpty()) {
            $this->error('review_states is empty. Seed it before importing localities.');
            return 1;
        }

        $this->info($dryRun ? 'Dry run — no rows will be written.' : 'Importing localities.');
        $this->line('Extract: ' . $path);
        $this->line('Version: ' . $version);

        $prepared = $this->prepareRecords($records, $stateNames);
        $stats = $this->upsert($prepared['rows'], $version, $dryRun);
        $stats['retired'] = $this->retireAbsent(array_keys($prepared['rows']), $dryRun);

        $this->table(
            ['Added', 'Updated', 'Unchanged', 'Reactivated', 'Retired', 'Skipped'],
            [[
                $stats['added'],
                $stats['updated'],
                $stats['unchanged'],
                $stats['reactivated'],
                $stats['retired'],
                count($prepared['skipped']),
            ]]
        );

        if (!empty($prepared['skipped'])) {
            $this->warn('Skipped records (first 30):');
            $this->table(['locality', 'state', 'postcode', 'reason'], array_slice($prepared['skipped'], 0, 30));
        }

        return 0;
    }

    /**
     * @return \Illuminate\Support\Collection state code => full state name
     */
    private function stateNamesByCode()
    {
        return ReviewStates::all()->mapWithKeys(function ($row) {
            return [strtoupper(trim($row->abbreviation)) => $row->name];
        });
    }

    /**
     * Normalise, filter and deduplicate the extract.
     *
     * @return array{rows: array, skipped: array}
     */
    private function prepareRecords(array $records, $stateNames)
    {
        $rows = [];
        $skipped = [];

        foreach ($records as $record) {
            $name = trim((string) $this->field($record, ['locality', 'name', 'suburb']));
            $stateCode = strtoupper(trim((string) $this->field($record, ['state', 'state_code'])));
            // Cast to string before trimming so a numeric postcode in a
            // third-party file cannot arrive here as an integer.
            $postcode = trim((string) $this->field($record, ['postcode', 'post_code']));

            if ($name === '' || $postcode === '') {
                $skipped[] = [$name, $stateCode, $postcode, 'missing locality or postcode'];
                continue;
            }

            // Only the eight states and territories have a review_states row.
            // External territories (OT) are intentionally out of scope.
            if (!$stateNames->has($stateCode)) {
                $skipped[] = [$name, $stateCode, $postcode, 'state not one of the eight states and territories'];
                continue;
            }

            if (!preg_match('/^\d{4}$/', $postcode)) {
                $skipped[] = [$name, $stateCode, $postcode, 'postcode is not four digits'];
                continue;
            }

            if (preg_match(self::NON_PHYSICAL_PATTERN, $name)) {
                $skipped[] = [$name, $stateCode, $postcode, 'non-physical locality'];
                continue;
            }

            $normalised = Suburbs::normalise($name);
            if ($normalised === '') {
                $skipped[] = [$name, $stateCode, $postcode, 'locality name normalises to empty'];
                continue;
            }

            $key = $this->compositeKey($normalised, $postcode, $stateCode);
            if (isset($rows[$key])) {
                $skipped[] = [$name, $stateCode, $postcode, 'duplicate of an earlier record'];
                continue;
            }

            $latitude = $this->field($record, ['latitude', 'lat']);
            $longitude = $this->field($record, ['longitude', 'long', 'lng']);

            $rows[$key] = [
                // Upstream casing is naive ("Mckinnon", "O'connor"), so store
                // the display form and keep the matching form beside it.
                'name' => Suburbs::displayName($name),
                'name_normalised' => $normalised,
                'state' => $stateNames->get($stateCode),
                'state_code' => $stateCode,
                'postcode' => $postcode,
                'latitude' => $latitude === null || $latitude === '' ? null : (string) $latitude,
                'longitude' => $longitude === null || $longitude === '' ? null : (string) $longitude,
            ];
        }

        return ['rows' => $rows, 'skipped' => $skipped];
    }

    /**
     * Insert new localities, update changed ones, and reactivate any that came
     * back in this release.
     *
     * @return array{added: int, updated: int, unchanged: int, reactivated: int}
     */
    private function upsert(array $rows, $version, $dryRun)
    {
        $existing = $this->existingByKey();

        $added = 0;
        $updated = 0;
        $unchanged = 0;
        $reactivated = 0;
        $inserts = [];

        foreach ($rows as $key => $row) {
            if (!isset($existing[$key])) {
                $added++;
                $inserts[] = $row + [
                    'is_active' => 1,
                    'source_version' => $version,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                continue;
            }

            $current = $existing[$key];
            $changes = $this->changedColumns($current, $row);

            if (!$current->is_active) {
                $changes['is_active'] = 1;
                $reactivated++;
            }

            if (empty($changes) && $current->source_version === $version) {
                $unchanged++;
                continue;
            }

            if (!empty($changes)) {
                $updated++;
            }

            $changes['source_version'] = $version;
            $changes['updated_at'] = now();

            if (!$dryRun) {
                DB::table('suburbs')->where('id', $current->id)->update($changes);
            }
        }

        if (!$dryRun && !empty($inserts)) {
            foreach (array_chunk($inserts, self::CHUNK_SIZE) as $chunk) {
                DB::table('suburbs')->insert($chunk);
            }
        }

        return [
            'added' => $added,
            'updated' => $updated,
            'unchanged' => $unchanged,
            'reactivated' => $reactivated,
        ];
    }

    /**
     * Mark localities that are no longer in the release inactive. Never delete —
     * existing reviews must continue to resolve.
     *
     * @param string[] $presentKeys
     * @return int
     */
    private function retireAbsent(array $presentKeys, $dryRun)
    {
        $present = array_flip($presentKeys);
        $retiring = [];

        DB::table('suburbs')
            ->where('is_active', true)
            ->orderBy('id')
            ->chunkById(self::CHUNK_SIZE, function ($rows) use ($present, &$retiring) {
                foreach ($rows as $row) {
                    $key = $this->compositeKey($row->name_normalised, $row->postcode, $row->state_code);
                    if (!isset($present[$key])) {
                        $retiring[] = $row->id;
                    }
                }
            });

        if (!$dryRun && !empty($retiring)) {
            foreach (array_chunk($retiring, self::CHUNK_SIZE) as $chunk) {
                DB::table('suburbs')->whereIn('id', $chunk)->update([
                    'is_active' => 0,
                    'updated_at' => now(),
                ]);
            }
        }

        return count($retiring);
    }

    /**
     * @return array keyed by composite key
     */
    private function existingByKey()
    {
        $existing = [];

        DB::table('suburbs')
            ->orderBy('id')
            ->chunkById(self::CHUNK_SIZE, function ($rows) use (&$existing) {
                foreach ($rows as $row) {
                    $key = $this->compositeKey($row->name_normalised, $row->postcode, $row->state_code);
                    $existing[$key] = $row;
                }
            });

        return $existing;
    }

    /**
     * @return array columns whose stored value differs from the extract
     */
    private function changedColumns($current, array $row)
    {
        $changes = [];

        foreach ($row as $column => $value) {
            $stored = isset($current->{$column}) ? $current->{$column} : null;

            if ($column === 'latitude' || $column === 'longitude') {
                // Stored as strings; compare numerically so "-37.8127" and
                // "-37.81270" do not count as a change every run.
                $same = ($stored === null && $value === null)
                    || ($stored !== null && $value !== null && abs((float) $stored - (float) $value) < 0.000001);
                if (!$same) {
                    $changes[$column] = $value;
                }
                continue;
            }

            if ((string) $stored !== (string) $value) {
                $changes[$column] = $value;
            }
        }

        return $changes;
    }

    /**
     * @return string
     */
    private function compositeKey($normalised, $postcode, $stateCode)
    {
        return strtolower(trim((string) $normalised))
            . '|' . trim((string) $postcode)
            . '|' . strtoupper(trim((string) $stateCode));
    }

    /**
     * First present value among several possible key spellings.
     *
     * @param array $record
     * @param string[] $keys
     * @return mixed
     */
    private function field(array $record, array $keys)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $record) && $record[$key] !== null) {
                return $record[$key];
            }
        }

        return null;
    }
}
