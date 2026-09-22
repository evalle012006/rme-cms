<?php

namespace App\Http\Controllers;

use App\Models\ReviewStates;
use App\Models\Suburbs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Read-only locality lookups backing the State / Suburb / Postcode component on
 * the public review form and in the admin portal.
 *
 * Everything is served from the local `suburbs` table. There is deliberately no
 * outbound call on any of these paths, so a third-party outage can never block a
 * review submission.
 */
class LocalityController extends BaseController
{
    const MAX_SEARCH_RESULTS = 25;
    const MIN_SEARCH_LENGTH = 2;
    const CACHE_TTL_MINUTES = 60 * 24;
    const CACHE_HEADER = 'public, max-age=86400';

    /**
     * GET /api/localities/states
     */
    public function states()
    {
        $states = Cache::remember(
            'localities.states.' . $this->cacheToken(),
            self::CACHE_TTL_MINUTES,
            function () {
                return ReviewStates::orderBy('name')
                    ->get(['id', 'name', 'abbreviation'])
                    ->map(function ($state) {
                        return [
                            'id' => (int) $state->id,
                            'code' => $state->abbreviation,
                            'name' => $state->name,
                        ];
                    })
                    ->values();
            }
        );

        return $this->cacheable($this->formatSuccessResponse('success', $states));
    }

    /**
     * GET /api/localities?state=QLD
     *
     * The full alphabetical list for one state. Cached because the suburb
     * dropdown fetches it on every state change.
     */
    public function index(Request $request)
    {
        $stateCode = $this->normaliseStateCode($request->query('state'));

        if ($stateCode === null) {
            return $this->formatFailureResponse('A valid state code is required.', 422);
        }

        $localities = Cache::remember(
            'localities.state.' . $stateCode . '.' . $this->cacheToken(),
            self::CACHE_TTL_MINUTES,
            function () use ($stateCode) {
                $rows = Suburbs::active()
                    ->inState($stateCode)
                    ->orderBy('name_normalised')
                    ->orderBy('postcode')
                    ->get(['id', 'name', 'name_normalised', 'postcode', 'state', 'state_code', 'latitude', 'longitude']);

                return $this->present($rows, true);
            }
        );

        return $this->cacheable($this->formatSuccessResponse('success', $localities));
    }

    /**
     * GET /api/localities/search?state=QLD&q=asc
     *
     * Prefix matches are ordered ahead of contains matches. `state` is optional;
     * without it the label carries the state code so duplicate names across
     * states stay distinguishable.
     */
    public function search(Request $request)
    {
        $term = Suburbs::normalise($request->query('q'));

        if (mb_strlen($term) < self::MIN_SEARCH_LENGTH) {
            return $this->cacheable($this->formatSuccessResponse('success', []));
        }

        $stateFilter = $request->query('state');
        $stateCode = $this->normaliseStateCode($stateFilter);

        if ($stateFilter !== null && $stateFilter !== '' && $stateCode === null) {
            return $this->formatFailureResponse('A valid state code is required.', 422);
        }

        $query = Suburbs::active()->search($term);
        if ($stateCode !== null) {
            $query->inState($stateCode);
        }

        $rows = $query
            ->take(self::MAX_SEARCH_RESULTS)
            ->get(['id', 'name', 'name_normalised', 'postcode', 'state', 'state_code', 'latitude', 'longitude']);

        return $this->cacheable(
            $this->formatSuccessResponse('success', $this->present($rows, $stateCode !== null))
        );
    }

    /**
     * GET /api/localities/by-postcode?postcode=4007
     *
     * Every locality sharing a postcode. Never reduced to one row — the caller
     * must not auto-select when several are valid.
     */
    public function byPostcode(Request $request)
    {
        $postcode = trim((string) $request->query('postcode'));

        if (!preg_match('/^\d{4}$/', $postcode)) {
            return $this->formatFailureResponse('A four digit postcode is required.', 422);
        }

        $rows = Suburbs::active()
            ->where('postcode', $postcode)
            ->orderBy('name_normalised')
            ->get(['id', 'name', 'name_normalised', 'postcode', 'state', 'state_code', 'latitude', 'longitude']);

        return $this->cacheable(
            $this->formatSuccessResponse('success', $this->present($rows, false))
        );
    }

    /**
     * Build the response rows, including a label the clients can render as-is.
     *
     * @param \Illuminate\Support\Collection $rows
     * @param bool $scopedToOneState whether the caller already filtered by state
     * @return \Illuminate\Support\Collection
     */
    private function present($rows, $scopedToOneState)
    {
        // A name that repeats inside a state is only distinguishable by postcode.
        $nameCounts = $rows->groupBy(function ($row) {
            return $row->state_code . '|' . $row->name_normalised;
        })->map(function ($group) {
            return $group->count();
        });

        return $rows->map(function ($row) use ($nameCounts, $scopedToOneState) {
            $suburb = Suburbs::displayName($row->name);
            $countKey = $row->state_code . '|' . $row->name_normalised;

            $label = $suburb;
            if ($nameCounts->get($countKey, 0) > 1) {
                $label .= ' (' . $row->postcode . ')';
            }
            if (!$scopedToOneState) {
                $label .= ', ' . $row->state_code;
            }

            return [
                'id' => (int) $row->id,
                'suburb' => $suburb,
                'postcode' => (string) $row->postcode,
                'state_code' => $row->state_code,
                'state_name' => $row->state,
                'label' => $label,
                'latitude' => $row->latitude,
                'longitude' => $row->longitude,
            ];
        })->values();
    }

    /**
     * Valid state code, or null when absent or unknown.
     *
     * @param mixed $state
     * @return string|null
     */
    private function normaliseStateCode($state)
    {
        $code = strtoupper(trim((string) $state));
        if ($code === '') {
            return null;
        }

        $known = Cache::remember('localities.state_codes', self::CACHE_TTL_MINUTES, function () {
            return ReviewStates::pluck('abbreviation')
                ->map(function ($abbreviation) {
                    return strtoupper(trim($abbreviation));
                })
                ->all();
        });

        return in_array($code, $known, true) ? $code : null;
    }

    /**
     * Changes whenever the import writes, so cached lists fall out naturally
     * after a quarterly refresh without the command having to flush anything.
     *
     * @return string
     */
    private function cacheToken()
    {
        $row = DB::table('suburbs')
            ->selectRaw('COUNT(*) as row_count, MAX(updated_at) as last_change')
            ->first();

        return ($row ? $row->row_count : 0) . '-' . ($row && $row->last_change ? $row->last_change : 'initial');
    }

    /**
     * @param \Illuminate\Http\JsonResponse $response
     * @return \Illuminate\Http\JsonResponse
     */
    private function cacheable($response)
    {
        return $response->header('Cache-Control', self::CACHE_HEADER);
    }
}
