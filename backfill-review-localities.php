#!/usr/bin/env php
<?php

/**
 * One-off backfill: set reviews.locality_id from the stored suburb, postcode
 * and state. Unmatched rows are left alone and listed in a CSV next to this
 * file. Does not change suburb, post_code, state, or updated_at.
 *
 * Preview:
 *   php backfill-review-localities.php --dry-run
 *
 * Apply:
 *   php backfill-review-localities.php
 *
 * Docker (this file is not mounted in the PHP container by default):
 *   docker compose run --rm --no-deps \
 *     -v "$(pwd)/backfill-review-localities.php:/var/www/backend/backfill-review-localities.php" \
 *     php php backfill-review-localities.php --dry-run
 */

use App\Models\Review;
use App\Support\ReviewLocalityMatcher;
use App\Support\StateResolver;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

const CHUNK_SIZE = 200;
const REPORT_FILENAME = 'review-locality-backfill-unmatched.csv';

$dryRun = in_array('--dry-run', $argv, true);
$wantHelp = in_array('--help', $argv, true) || in_array('-h', $argv, true);

if ($wantHelp) {
    fwrite(STDOUT, <<<'HELP'
Backfill reviews.locality_id from suburb + postcode + state.

Usage:
  php backfill-review-localities.php [--dry-run]

  --dry-run   Print counts and write the unmatched CSV. Do not update rows.
  --help      Show this message.

A locality is written only when exactly one suburbs row matches. Ambiguous
or unknown suburbs stay locality_id null and appear in:
  review-locality-backfill-unmatched.csv

HELP
    );
    exit(0);
}

$backend = resolveBackendPath(__DIR__);
if ($backend === null) {
    fwrite(STDERR, "Could not find backend/vendor/autoload.php from " . __DIR__ . PHP_EOL);
    exit(1);
}

require $backend . '/vendor/autoload.php';

$app = require $backend . '/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

fwrite(STDOUT, $dryRun
    ? "Dry run — no rows will be updated." . PHP_EOL
    : "Backfilling review localities." . PHP_EOL
);

$matched = 0;
$matchedOn = [];
$exceptions = [];

Review::withTrashed()
    ->whereNull('locality_id')
    ->orderBy('id')
    ->chunkById(CHUNK_SIZE, function ($reviews) use ($dryRun, &$matched, &$matchedOn, &$exceptions) {
        foreach ($reviews as $review) {
            $result = ReviewLocalityMatcher::match(
                $review->suburb,
                $review->post_code,
                $review->state
            );

            if (!$result['locality']) {
                $exceptions[] = [
                    'review_id' => $review->id,
                    'title' => $review->title,
                    'state' => StateResolver::name($review->state) ?: (string) $review->state,
                    'suburb' => (string) $review->suburb,
                    'post_code' => (string) $review->post_code,
                    'property_id' => $review->property_id,
                    'created_at' => (string) $review->created_at,
                    'reason' => $result['reason'],
                ];
                continue;
            }

            $matched++;
            $key = $result['matched_on'];
            $matchedOn[$key] = ($matchedOn[$key] ?? 0) + 1;

            if (!$dryRun) {
                DB::table('reviews')
                    ->where('id', $review->id)
                    ->update(['locality_id' => $result['locality']->id]);
            }
        }
    });

fwrite(STDOUT, PHP_EOL);
fwrite(STDOUT, sprintf("Matched:    %d%s", $matched, PHP_EOL));
fwrite(STDOUT, sprintf("Unresolved: %d%s", count($exceptions), PHP_EOL));

if (!empty($matchedOn)) {
    fwrite(STDOUT, PHP_EOL . "Matched on:" . PHP_EOL);
    foreach ($matchedOn as $strategy => $count) {
        fwrite(STDOUT, sprintf("  %s: %d%s", $strategy, $count, PHP_EOL));
    }
}

if (empty($exceptions)) {
    fwrite(STDOUT, PHP_EOL . "Every review resolved to a locality." . PHP_EOL);
    $kernel->terminate(new Symfony\Component\Console\Input\ArgvInput, 0);
    exit(0);
}

fwrite(STDOUT, PHP_EOL . "Unresolved reviews (first 20):" . PHP_EOL);
$preview = array_slice($exceptions, 0, 20);
fwrite(STDOUT, sprintf(
    "  %-8s  %-22s  %-12s  %-20s  %s%s",
    'id',
    'suburb',
    'postcode',
    'reason',
    'title',
    PHP_EOL
));
foreach ($preview as $row) {
    fwrite(STDOUT, sprintf(
        "  %-8s  %-22s  %-12s  %-20s  %s%s",
        $row['review_id'],
        truncate((string) $row['suburb'], 22),
        $row['post_code'],
        truncate((string) $row['reason'], 20),
        truncate((string) $row['title'], 40),
        PHP_EOL
    ));
}

$reportPath = __DIR__ . DIRECTORY_SEPARATOR . REPORT_FILENAME;
writeUnmatchedCsv($reportPath, $exceptions);
fwrite(STDOUT, PHP_EOL . "Unmatched report: " . $reportPath . PHP_EOL);

$kernel->terminate(new Symfony\Component\Console\Input\ArgvInput, 0);
exit(0);

/**
 * Repo-root run uses backend/ next to this file. Docker bind-mounts this
 * file into the PHP container's backend directory.
 *
 * @param string $scriptDir
 * @return string|null
 */
function resolveBackendPath($scriptDir)
{
    if (is_file($scriptDir . '/vendor/autoload.php') && is_file($scriptDir . '/bootstrap/app.php')) {
        return $scriptDir;
    }

    $nested = $scriptDir . '/backend';
    if (is_file($nested . '/vendor/autoload.php') && is_file($nested . '/bootstrap/app.php')) {
        return $nested;
    }

    return null;
}

/**
 * @param string $path
 * @param array<int, array<string, mixed>> $rows
 * @return void
 */
function writeUnmatchedCsv($path, array $rows)
{
    $handle = fopen($path, 'w');
    if ($handle === false) {
        fwrite(STDERR, "Could not write unmatched report to {$path}" . PHP_EOL);
        exit(1);
    }

    fputcsv($handle, [
        'review_id',
        'title',
        'state',
        'suburb',
        'post_code',
        'property_id',
        'created_at',
        'reason',
    ]);

    foreach ($rows as $row) {
        fputcsv($handle, [
            $row['review_id'],
            $row['title'],
            $row['state'],
            $row['suburb'],
            $row['post_code'],
            $row['property_id'],
            $row['created_at'],
            $row['reason'],
        ]);
    }

    fclose($handle);
}

/**
 * @param string $value
 * @param int $length
 * @return string
 */
function truncate($value, $length)
{
    if (mb_strlen($value) <= $length) {
        return $value;
    }

    return mb_substr($value, 0, max(0, $length - 1)) . '…';
}
