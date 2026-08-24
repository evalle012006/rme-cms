<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * AuSuburbsSeeder
 *
 * Loads the Australian suburbs / postcodes dataset (~15,269 rows) into the
 * `suburbs` table from database/data/au-suburbs.json.
 *
 * The seeder is idempotent — existing rows (matched by name + postcode +
 * state) are skipped so it is safe to re-run on any environment.
 *
 * Usage:
 *   php artisan db:seed --class=AuSuburbsSeeder
 */
class AuSuburbsSeeder extends Seeder
{
    private const CHUNK_SIZE = 1000;

    public function run()
    {
        $path = database_path('data/au-suburbs.json');

        if (!file_exists($path)) {
            $this->command->error("AuSuburbsSeeder: data file not found at {$path}");
            return;
        }

        $records = json_decode(file_get_contents($path), true);
        if (!is_array($records) || empty($records)) {
            $this->command->error('AuSuburbsSeeder: dataset is empty or invalid.');
            return;
        }

        $this->command->info('AuSuburbsSeeder: loading ' . count($records) . ' suburbs...');

        // Build a set of existing keys so we can skip duplicates cheaply.
        $existing = DB::table('suburbs')
            ->select('name', 'postcode', 'state')
            ->get()
            ->map(function ($r) {
                return $this->key($r->name, $r->postcode, $r->state);
            })
            ->flip();

        $toInsert = [];
        $skipped  = 0;

        foreach ($records as $r) {
            $key = $this->key($r['name'] ?? '', $r['postcode'] ?? '', $r['state'] ?? '');
            if ($existing->has($key)) {
                $skipped++;
                continue;
            }
            $existing->put($key, true);
            $toInsert[] = [
                'name'      => $r['name'] ?? '',
                'postcode'  => (string) ($r['postcode'] ?? ''),
                'state'     => $r['state'] ?? '',
                'latitude'  => (string) ($r['latitude'] ?? ''),
                'longitude' => (string) ($r['longitude'] ?? ''),
            ];
        }

        if (empty($toInsert)) {
            $this->command->info("AuSuburbsSeeder: nothing to insert (skipped {$skipped} existing).");
            return;
        }

        DB::transaction(function () use ($toInsert) {
            foreach (array_chunk($toInsert, self::CHUNK_SIZE) as $chunk) {
                DB::table('suburbs')->insert($chunk);
            }
        });

        $inserted = count($toInsert);
        $this->command->info("AuSuburbsSeeder: inserted {$inserted}, skipped {$skipped}.");
    }

    private function key(string $name, string $postcode, string $state): string
    {
        return strtolower(trim($name)) . '|' . trim($postcode) . '|' . strtoupper(trim($state));
    }
}
