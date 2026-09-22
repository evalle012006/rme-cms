<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The canonical locality reference table, seeded from Geoscape G-NAF.
 *
 * `name` and `state` keep the original free-text form so existing callers
 * (SuburbStateLookup, HomeController::getMapData, the property forms) are
 * unaffected. `name_normalised` and `state_code` back the search and filter
 * paths added for the locality component.
 */
class Suburbs extends Model
{
    protected $fillable = [
        'name',
        'name_normalised',
        'state',
        'state_code',
        'postcode',
        'longitude',
        'latitude',
        'is_active',
        'source_version',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Matching form of a suburb name.
     *
     * Apostrophes are dropped so "oconnor" finds O'Connor; every other
     * separator collapses to a single space so "st kilda" and "bunya mountains"
     * both resolve.
     *
     * @param string|null $name
     * @return string
     */
    public static function normalise($name)
    {
        $value = mb_strtolower(trim((string) $name));
        $value = str_replace(["'", "\u{2019}", '`'], '', $value);
        $value = preg_replace('/[^a-z0-9]+/u', ' ', $value);

        return trim(preg_replace('/\s+/', ' ', $value));
    }

    /**
     * Display form. Source data is uppercase, so title case it while preserving
     * the names title casing alone gets wrong (McKinnon, O'Connor).
     *
     * @param string|null $name
     * @return string
     */
    public static function displayName($name)
    {
        $value = trim((string) $name);
        if ($value === '') {
            return '';
        }

        $words = preg_split('/(\s+|-)/u', mb_strtolower($value), -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($words as $index => $word) {
            if ($word === '' || preg_match('/^(\s+|-)$/u', $word)) {
                continue;
            }
            $words[$index] = self::capitaliseWord($word);
        }

        return implode('', $words);
    }

    /**
     * @param string $word
     * @return string
     */
    private static function capitaliseWord($word)
    {
        // O'Connor, D'Aguilar — capitalise either side of the apostrophe.
        if (strpos($word, "'") !== false) {
            return implode("'", array_map(function ($part) {
                return self::ucfirstWord($part);
            }, explode("'", $word)));
        }

        // McKinnon, McLaren. "Mac" is left alone — Macquarie is not MacQuarie.
        if (mb_strlen($word) > 2 && mb_substr($word, 0, 2) === 'mc') {
            return 'Mc' . self::ucfirstWord(mb_substr($word, 2));
        }

        return self::ucfirstWord($word);
    }

    /**
     * @param string $value
     * @return string
     */
    private static function ucfirstWord($value)
    {
        if ($value === '') {
            return $value;
        }

        return mb_strtoupper(mb_substr($value, 0, 1)) . mb_substr($value, 1);
    }

    public function getDisplayNameAttribute()
    {
        return self::displayName($this->name);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $stateCode
     */
    public function scopeInState($query, $stateCode)
    {
        $code = strtoupper(trim((string) $stateCode));
        if ($code === '') {
            return $query;
        }

        return $query->where('state_code', $code);
    }

    /**
     * Keyword search on the normalised name. Prefix matches are ordered ahead of
     * contains matches, as required by the locality component.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $term
     */
    public function scopeSearch($query, $term)
    {
        $normalised = self::normalise($term);
        if ($normalised === '') {
            return $query;
        }

        $escaped = self::escapeLike($normalised);

        return $query
            ->where('name_normalised', 'LIKE', '%' . $escaped . '%')
            ->orderByRaw('CASE WHEN name_normalised LIKE ? THEN 0 ELSE 1 END', [$escaped . '%'])
            ->orderBy('name_normalised')
            ->orderBy('postcode');
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
