<?php

namespace App\Console\Commands;

use App\Models\Properties;
use App\Models\Review;
use App\Support\StateResolver;
use Illuminate\Console\Command;

class SyncReviewLocationFromProperty extends Command
{
    protected $signature = 'reviews:sync-location-from-property
                            {--dry-run : Print counts without writing}';

    protected $description = 'Normalize property state names, then copy property location onto reviews';

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');
        $this->info($dryRun ? 'Dry run — no rows will be updated.' : 'Applying location sync.');

        $propertyUpdated = $this->normalizePropertyStates($dryRun);
        $reviewUpdated = $this->syncReviewsFromProperties($dryRun);

        $this->table(
            ['Step', 'Rows updated'],
            [
                ['Properties.state abbreviation → full name', $propertyUpdated],
                ['Reviews copied from property location', $reviewUpdated],
            ]
        );

        return 0;
    }

    private function normalizePropertyStates($dryRun)
    {
        $updated = 0;
        Properties::query()
            ->whereNotNull('state')
            ->where('state', '!=', '')
            ->orderBy('id')
            ->chunkById(200, function ($properties) use ($dryRun, &$updated) {
                foreach ($properties as $property) {
                    $resolved = StateResolver::resolve($property->state);
                    if (!$resolved || $property->state === $resolved->name) {
                        continue;
                    }

                    $updated++;
                    if (!$dryRun) {
                        $property->state = $resolved->name;
                        $property->save();
                    }
                }
            });

        return $updated;
    }

    private function syncReviewsFromProperties($dryRun)
    {
        $updated = 0;
        Review::query()
            ->whereNotNull('property_id')
            ->with('property:id,state,suburb,post_code')
            ->orderBy('id')
            ->chunkById(200, function ($reviews) use ($dryRun, &$updated) {
                foreach ($reviews as $review) {
                    $property = $review->property;
                    if (!$property) {
                        continue;
                    }

                    $stateId = StateResolver::id($property->state);
                    $suburb = $property->suburb ?: '';
                    $postCode = $property->post_code ?: '';

                    if (
                        (int) $review->state === (int) $stateId
                        && (string) $review->suburb === (string) $suburb
                        && (string) $review->post_code === (string) $postCode
                    ) {
                        continue;
                    }

                    if ($stateId === null && $suburb === '' && $postCode === '') {
                        continue;
                    }

                    $updated++;
                    if (!$dryRun) {
                        $review->state = $stateId ?: $review->state;
                        $review->suburb = $suburb !== '' ? $suburb : $review->suburb;
                        $review->post_code = $postCode !== '' ? $postCode : $review->post_code;
                        $review->save();
                    }
                }
            });

        return $updated;
    }
}
