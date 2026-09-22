<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Reviews whose stored location could not be resolved to a single locality.
 *
 * The client resolves these by hand in the admin portal; the backfill command
 * never guesses between candidates.
 */
class ReviewLocalityExceptionsExport implements FromArray, WithHeadings
{
    /** @var array */
    private $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function headings(): array
    {
        return [
            'Review ID',
            'Title',
            'State',
            'Suburb',
            'Postcode',
            'Submitted At',
            'Reason',
        ];
    }

    public function array(): array
    {
        return $this->rows;
    }
}
