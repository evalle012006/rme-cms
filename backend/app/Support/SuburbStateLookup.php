<?php

namespace App\Support;

use App\Models\Suburbs;
use App\Models\ReviewStates;

class SuburbStateLookup
{
    /**
     * Find the canonical review_states row for a suburb name + optional postcode.
     *
     * @param string|null $suburb
     * @param string|null $postcode
     * @return array{status: string, state: ReviewStates|null, suburb_state: string|null}
     */
    public static function resolve($suburb, $postcode = null)
    {
        $suburb = is_string($suburb) ? trim($suburb) : '';
        $postcode = is_string($postcode) ? trim($postcode) : '';

        if ($suburb === '') {
            return ['status' => 'missing', 'state' => null, 'suburb_state' => null];
        }

        $query = Suburbs::query()->where('name', $suburb);
        if ($postcode !== '') {
            $withPostcode = (clone $query)->where('postcode', $postcode)->pluck('state')->unique()->filter();
            if ($withPostcode->count() === 1) {
                return self::pack('ok', $withPostcode->first());
            }
            if ($withPostcode->count() > 1) {
                return self::pack('ambiguous', null);
            }
        }

        $byName = $query->pluck('state')->unique()->filter();
        if ($byName->count() === 1) {
            return self::pack('ok', $byName->first());
        }
        if ($byName->count() > 1) {
            return self::pack('ambiguous', null);
        }

        return self::pack('missing', null);
    }

    /**
     * Full state name from suburbs + review_states, or null.
     *
     * @param string|null $suburb
     * @param string|null $postcode
     * @return string|null
     */
    public static function canonicalName($suburb, $postcode = null)
    {
        $result = self::resolve($suburb, $postcode);
        return $result['state'] ? $result['state']->name : null;
    }

    /**
     * review_states.id from suburbs + review_states, or null.
     *
     * @param string|null $suburb
     * @param string|null $postcode
     * @return int|null
     */
    public static function canonicalId($suburb, $postcode = null)
    {
        $result = self::resolve($suburb, $postcode);
        return $result['state'] ? (int) $result['state']->id : null;
    }

    /**
     * @param string $status
     * @param string|null $suburbState
     * @return array{status: string, state: ReviewStates|null, suburb_state: string|null}
     */
    private static function pack($status, $suburbState)
    {
        return [
            'status' => $status,
            'state' => $suburbState ? StateResolver::resolve($suburbState) : null,
            'suburb_state' => $suburbState,
        ];
    }
}
