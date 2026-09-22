<?php

namespace App\Support;

use App\Models\Suburbs;

/**
 * Resolves a review's free-text location to a locality record.
 *
 * Used by `reviews:backfill-localities` to populate reviews.locality_id and by
 * the admin exception report to list the rows that need a human decision. Both
 * must agree, which is why the matching lives here rather than in either caller.
 *
 * A locality is only returned when the evidence points at exactly one row. An
 * ambiguous match is reported, never guessed — picking the first of several
 * would silently attach reviews to the wrong place.
 *
 * Retired localities are deliberately in scope: a historic review should keep
 * resolving to the place it was written about, even once G-NAF drops it.
 */
class ReviewLocalityMatcher
{
    const REASON_NO_SUBURB = 'no suburb recorded';
    const REASON_AMBIGUOUS = 'suburb matches more than one locality';
    const REASON_UNMATCHED = 'suburb not found in the locality table';

    /**
     * @param string|null $suburb
     * @param string|null $postcode
     * @param mixed $state review_states id, name or abbreviation
     * @return array{locality: Suburbs|null, reason: string|null, matched_on: string|null}
     */
    public static function match($suburb, $postcode, $state)
    {
        $normalised = Suburbs::normalise($suburb);
        if ($normalised === '') {
            return self::failure(self::REASON_NO_SUBURB);
        }

        $postcode = trim((string) $postcode);
        $stateRow = StateResolver::resolve($state);
        $stateCode = $stateRow ? $stateRow->abbreviation : null;

        $attempts = [];

        // Most reviews have a trustworthy state, and a suburb name is unique
        // within a state unless it spans several postcodes.
        if ($stateCode) {
            $attempts['name and state'] = function () use ($normalised, $stateCode) {
                return Suburbs::where('name_normalised', $normalised)
                    ->where('state_code', $stateCode);
            };
        }

        // Postcode alone pins the state, so this recovers rows whose state is
        // missing or wrong.
        if ($postcode !== '') {
            $attempts['name and postcode'] = function () use ($normalised, $postcode) {
                return Suburbs::where('name_normalised', $normalised)
                    ->where('postcode', $postcode);
            };

            // Last resort for truncated or suffixed names ("Ascot Vale Nth").
            $attempts['postcode and name prefix'] = function () use ($normalised, $postcode) {
                return Suburbs::where('postcode', $postcode)
                    ->where('name_normalised', 'LIKE', self::escapeLike($normalised) . '%');
            };
        }

        $sawCandidates = false;

        foreach ($attempts as $matchedOn => $build) {
            $rows = $build()->take(2)->get();

            if ($rows->count() === 1) {
                return [
                    'locality' => $rows->first(),
                    'reason' => null,
                    'matched_on' => $matchedOn,
                ];
            }

            if ($rows->count() > 1) {
                $sawCandidates = true;
            }
        }

        return self::failure($sawCandidates ? self::REASON_AMBIGUOUS : self::REASON_UNMATCHED);
    }

    /**
     * @return array{locality: null, reason: string, matched_on: null}
     */
    private static function failure($reason)
    {
        return ['locality' => null, 'reason' => $reason, 'matched_on' => null];
    }

    /**
     * @param string $value
     * @return string
     */
    private static function escapeLike($value)
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $value);
    }
}
