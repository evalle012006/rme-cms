<?php

use Illuminate\Database\Seeder;

class TagsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $valueToTags = [
            [
                'name' => 'Not Good location',
                'type' => 'constructive',
                'is_active' => 1
            ],
            [
                'name' => 'Property',
                'type' => 'positive',
                'is_active' => 1
            ],
            [
                'name' => 'Amenities',
                'type' => 'positive',
                'is_active' => 1
            ],
            [
                'name' => 'Good Settlement Process',
                'type' => 'positive',
                'is_active' => 1
            ],
        ];
        \App\Models\Tags::insert($valueToTags);


        $ins = [
            [
                'name' => 'New South Wales',
                'abbreviation' => 'NSW'
            ],
            [
                'name' => 'Victoria',
                'abbreviation' => 'VIC'
            ],
            [
                'name' => 'Queensland',
                'abbreviation' => 'QLD'
            ],
            [
                'name' => 'Tasmania',
                'abbreviation' => 'TAS'
            ],
            [
                'name' => 'South Australia',
                'abbreviation' => 'SA'
            ],
            [
                'name' => 'Western Australia',
                'abbreviation' => 'WA'
            ],
            [
                'name' => 'Northern Territory',
                'abbreviation' => 'NT'
            ],
            [
                'name' => 'Australian Capital Territory',
                'abbreviation' => 'ACT'
            ]
        ];

        \App\Models\ReviewStates::insert($ins);
    }
}
