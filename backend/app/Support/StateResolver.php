<?php

namespace App\Support;

use App\Models\ReviewStates;

class StateResolver
{
    /** @var \Illuminate\Support\Collection|null */
    private static $rows;

    /**
     * @return \Illuminate\Support\Collection
     */
    public static function all()
    {
        if (self::$rows === null) {
            self::$rows = ReviewStates::all();
        }

        return self::$rows;
    }

    /**
     * Resolve a stored state value (full name, abbreviation, or id)
     * to a review_states row.
     *
     * @param mixed $state
     * @return ReviewStates|null
     */
    public static function resolve($state)
    {
        if ($state === null || $state === '' || $state === 0 || $state === '0') {
            return null;
        }

        $trimmed = is_string($state) ? trim($state) : $state;

        return self::all()->first(function ($row) use ($trimmed) {
            return (string) $row->id === (string) $trimmed
                || strcasecmp((string) $row->name, (string) $trimmed) === 0
                || strcasecmp((string) $row->abbreviation, (string) $trimmed) === 0;
        });
    }

    /**
     * @param mixed $state
     * @return int|null
     */
    public static function id($state)
    {
        $row = self::resolve($state);
        return $row ? (int) $row->id : null;
    }

    /**
     * Canonical full name from review_states.
     *
     * @param mixed $state
     * @return string|null
     */
    public static function name($state)
    {
        $row = self::resolve($state);
        return $row ? $row->name : null;
    }

    /**
     * Values that may be stored on properties.state for this row.
     *
     * @return string[]
     */
    public static function propertyStateValues(ReviewStates $state)
    {
        return array_values(array_unique(array_filter([
            $state->name,
            $state->abbreviation,
        ])));
    }
}
