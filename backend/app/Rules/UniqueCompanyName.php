<?php

namespace App\Rules;

use App\Models\CompanyDetails;
use Illuminate\Contracts\Validation\Rule;

/**
 * Case-insensitive, trimmed uniqueness for company_details.company_name.
 * Soft-deleted rows are ignored (SoftDeletes on CompanyDetails).
 */
class UniqueCompanyName implements Rule
{
    /** @var int|null */
    private $ignoreId;

    public function __construct($ignoreId = null)
    {
        $this->ignoreId = $ignoreId !== null ? (int) $ignoreId : null;
    }

    /**
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $normalized = strtolower(trim((string) $value));
        if ($normalized === '') {
            return false;
        }

        $query = CompanyDetails::query()
            ->whereRaw('LOWER(TRIM(company_name)) = ?', [$normalized]);

        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }

        return !$query->exists();
    }

    /**
     * @return string
     */
    public function message()
    {
        return 'A company with this name already exists. Please select it from the list instead of creating a duplicate.';
    }
}
