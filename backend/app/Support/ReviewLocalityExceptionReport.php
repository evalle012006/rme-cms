<?php

namespace App\Support;

use App\Exports\ReviewLocalityExceptionsExport;
use App\Models\Review;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Builds and stores the unresolved-locality CSV.
 *
 * Shared by `reviews:backfill-localities` and the admin endpoint so the console
 * and the portal always describe the same set of problem rows.
 */
class ReviewLocalityExceptionReport
{
    const PATH = 'csv-exports/review-locality-exceptions.csv';
    const CHUNK_SIZE = 200;

    /**
     * Every review that still has no locality, with the reason it did not match.
     *
     * @return array[]
     */
    public static function rows()
    {
        $rows = [];

        Review::withTrashed()
            ->whereNull('locality_id')
            ->orderBy('id')
            ->chunkById(self::CHUNK_SIZE, function ($reviews) use (&$rows) {
                foreach ($reviews as $review) {
                    $result = ReviewLocalityMatcher::match(
                        $review->suburb,
                        $review->post_code,
                        $review->state
                    );

                    // A row that now matches is not an exception — the next
                    // backfill run will pick it up.
                    if ($result['locality']) {
                        continue;
                    }

                    $rows[] = [
                        $review->id,
                        $review->title,
                        StateResolver::name($review->state) ?: $review->state,
                        $review->suburb,
                        (string) $review->post_code,
                        (string) $review->created_at,
                        $result['reason'],
                    ];
                }
            });

        return $rows;
    }

    /**
     * Write the CSV and return its public URL.
     *
     * Mirrors DashboardController::downloadCsv so exports land on the same disk
     * in every environment.
     *
     * @param array[] $rows
     * @return string
     */
    public static function store(array $rows)
    {
        $export = new ReviewLocalityExceptionsExport($rows);

        if (config('filesystems.default') !== 's3') {
            Excel::store($export, self::PATH, 'public', \Maatwebsite\Excel\Excel::CSV);
            return Storage::disk('public')->url(self::PATH);
        }

        Excel::store($export, self::PATH, 's3', \Maatwebsite\Excel\Excel::CSV, [
            'visibility' => 'public',
        ]);

        return Storage::disk('s3')->url(self::PATH);
    }
}
