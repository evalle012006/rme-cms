<?php

namespace App\Console\Commands;

use App\Models\Properties;
use App\Models\Review;
use App\Support\SuburbStateLookup;
use Illuminate\Console\Command;

class SyncStateFromSuburb extends Command
{
    protected $signature = 'locations:sync-state-from-suburb
                            {--dry-run : Print counts without writing}';

    protected $description = 'Set properties.state and reviews.state from the suburbs table for each suburb';

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');
        $this->info($dryRun ? 'Dry run — no rows will be updated.' : 'Applying suburb-to-state corrections.');

        $propertyStats = $this->correctProperties($dryRun);
        $reviewStats = $this->correctReviews($dryRun);

        $this->table(
            ['Table', 'Updated', 'Skipped (no suburb)', 'Unmatched suburb', 'Ambiguous suburb'],
            [
                [
                    'properties',
                    $propertyStats['updated'],
                    $propertyStats['missing_suburb'],
                    $propertyStats['unmatched'],
                    $propertyStats['ambiguous'],
                ],
                [
                    'reviews',
                    $reviewStats['updated'],
                    $reviewStats['missing_suburb'],
                    $reviewStats['unmatched'],
                    $reviewStats['ambiguous'],
                ],
            ]
        );

        $skipped = array_merge($propertyStats['skipped_rows'], $reviewStats['skipped_rows']);
        if (!empty($skipped)) {
            $this->warn('Skipped rows (first 30):');
            $this->table(['table', 'id', 'suburb', 'postcode', 'reason'], array_slice($skipped, 0, 30));
        }

        return 0;
    }

    private function emptyStats()
    {
        return [
            'updated' => 0,
            'missing_suburb' => 0,
            'unmatched' => 0,
            'ambiguous' => 0,
            'skipped_rows' => [],
        ];
    }

    private function correctProperties($dryRun)
    {
        $stats = $this->emptyStats();

        Properties::query()
            ->orderBy('id')
            ->chunkById(200, function ($properties) use ($dryRun, &$stats) {
                foreach ($properties as $property) {
                    $this->applyRow(
                        $stats,
                        $dryRun,
                        'properties',
                        $property->id,
                        $property->suburb,
                        $property->post_code,
                        function ($resolved) use ($property) {
                            return $property->state !== $resolved->name;
                        },
                        function ($resolved) use ($property) {
                            $property->state = $resolved->name;
                            $property->save();
                        }
                    );
                }
            });

        return $stats;
    }

    private function correctReviews($dryRun)
    {
        $stats = $this->emptyStats();

        Review::query()
            ->orderBy('id')
            ->chunkById(200, function ($reviews) use ($dryRun, &$stats) {
                foreach ($reviews as $review) {
                    $this->applyRow(
                        $stats,
                        $dryRun,
                        'reviews',
                        $review->id,
                        $review->suburb,
                        $review->post_code,
                        function ($resolved) use ($review) {
                            return (int) $review->state !== (int) $resolved->id;
                        },
                        function ($resolved) use ($review) {
                            $review->state = $resolved->id;
                            $review->save();
                        }
                    );
                }
            });

        return $stats;
    }

    private function applyRow(array &$stats, $dryRun, $table, $id, $suburb, $postcode, $needsUpdate, $persist)
    {
        $result = SuburbStateLookup::resolve($suburb, $postcode);

        if (!$suburb) {
            $stats['missing_suburb']++;
            return;
        }

        if ($result['status'] === 'ambiguous') {
            $stats['ambiguous']++;
            $stats['skipped_rows'][] = [$table, $id, $suburb, $postcode, 'suburb exists in more than one state'];
            return;
        }

        if ($result['status'] === 'missing' || !$result['state']) {
            $stats['unmatched']++;
            $stats['skipped_rows'][] = [$table, $id, $suburb, $postcode, 'suburb not in suburbs table'];
            return;
        }

        if (!$needsUpdate($result['state'])) {
            return;
        }

        $stats['updated']++;
        if (!$dryRun) {
            $persist($result['state']);
        }
    }
}
