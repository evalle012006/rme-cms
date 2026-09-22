<?php

namespace Tests\Unit;

use App\Models\Suburbs;
use PHPUnit\Framework\TestCase;

/**
 * Suburb name handling. `normalise` has twins in JavaScript — in
 * `public/js/locality-select.js` and `cms/src/components/atoms/LocalitySelect.tsx`
 * — so any change here must be mirrored in both or matching will disagree.
 */
class SuburbNameTest extends TestCase
{
    /**
     * @dataProvider normalisationCases
     */
    public function testNormalise($input, $expected)
    {
        $this->assertSame($expected, Suburbs::normalise($input));
    }

    public function normalisationCases()
    {
        return [
            'lowercases' => ['ASCOT', 'ascot'],
            'drops apostrophes so oconnor finds it' => ["O'Connor", 'oconnor'],
            'drops curly apostrophes too' => ["O\u{2019}Connor", 'oconnor'],
            'keeps word breaks' => ['Coffs Harbour', 'coffs harbour'],
            'hyphen becomes a space' => ['Bunya-Mountains', 'bunya mountains'],
            'collapses repeated whitespace' => ["  Ascot   Vale \n", 'ascot vale'],
            'strips trailing punctuation' => ['St Kilda.', 'st kilda'],
            'keeps digits' => ['Sydney 2000', 'sydney 2000'],
            'empty stays empty' => ['', ''],
            'null stays empty' => [null, ''],
        ];
    }

    /**
     * @dataProvider displayCases
     */
    public function testDisplayName($input, $expected)
    {
        $this->assertSame($expected, Suburbs::displayName($input));
    }

    public function displayCases()
    {
        return [
            'title cases uppercase source' => ['ASCOT VALE', 'Ascot Vale'],
            'fixes naive Mc casing' => ['Mckinnon', 'McKinnon'],
            'fixes Mc across a space' => ['Mclaren Vale', 'McLaren Vale'],
            'fixes apostrophe casing' => ["O'connor", "O'Connor"],
            'handles D apostrophe' => ["D'aguilar", "D'Aguilar"],
            'leaves Mac alone' => ['Macquarie Park', 'Macquarie Park'],
            'keeps hyphens' => ['bunya-mountains', 'Bunya-Mountains'],
            'short words are safe' => ['Mc', 'Mc'],
            'empty stays empty' => ['', ''],
        ];
    }
}
