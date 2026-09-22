<?php

namespace App\Console\Commands;

use App\Models\Review;
use App\Support\ReviewLocalityExceptionReport;
use App\Support\ReviewLocalityMatcher;
use App\Support\StateResolver;
use Illuminate\Console\Command;

/**
 * Attach existing reviews to a locality record.
 *
 * Run once after `localities:import`. Reviews that cannot be resolved to a
 * single locality are left alone and written to a CSV exception report for the
 * client to resolve in the admin portal.
 *
 * The legacy state, suburb and post_code columns are not touched; dropping them
 * is a follow-up gated on client sign-off.
 */
class BackfillReviewLocalities extends Command
{
    protected $signature = 'reviews:backfill-localities
                            {--dry-run : Print counts without writing}';

    protected $description = 'Resolve reviews.locality_id from the stored state, suburb and postcode';

    const CHUNK_SIZE = 200;

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');
        $this->info($dryRun ? 'Dry run — no rows will be updated.' : 'Backfilling review localities.');

        $matched = 0;
        $matchedOn = [];
        $exceptions = [];

        Review::withTrashed()
            ->whereNull('locality_id')
            ->orderBy('id')
            ->chunkById(self::CHUNK_SIZE, function ($reviews) use ($dryRun, &$matched, &$matchedOn, &$exceptions) {
                foreach ($reviews as $review) {
                    $result = ReviewLocalityMatcher::match(
                        $review->suburb,
                        $review->post_code,
                        $review->state
                    );

                    if (!$result['locality']) {
                        $exceptions[] = [
                            $review->id,
                            $review->title,
                            StateResolver::name($review->state) ?: $review->state,
                            $review->suburb,
                            (string) $review->post_code,
                            (string) $review->created_at,
                            $result['reason'],
                        ];
                        continue;
                    }

                    $matched++;
                    $key = $result['matched_on'];
                    $matchedOn[$key] = ($matchedOn[$key] ?? 0) + 1;

                    if (!$dryRun) {
                        // Update without touching timestamps or firing model
                        // events — this is a data repair, not an edit.
                        Review::withTrashed()
                            ->where('id', $review->id)
                            ->update(['locality_id' => $result['locality']->id]);
                    }
                }
            });

        $this->table(
            ['Matched', 'Unresolved'],
            [[$matched, count($exceptions)]]
        );

        if (!empty($matchedOn)) {
            $this->line('Matched on:');
            foreach ($matchedOn as $strategy => $count) {
                $this->line("  {$strategy}: {$count}");
            }
        }

        if (empty($exceptions)) {
            $this->info('Every review resolved to a locality.');
            return 0;
        }

        $this->warn('Unresolved reviews (first 20):');
        $this->table(
            ['id', 'title', 'state', 'suburb', 'postcode', 'submitted', 'reason'],
            array_slice($exceptions, 0, 20)
        );

        if ($dryRun) {
            $this->line('Dry run — exception report not written.');
            return 0;
        }

        $url = ReviewLocalityExceptionReport::store($exceptions);
        $this->info('Exception report: ' . $url);

        return 0;
    }
}
