<?php

use App\Models\OptionMaster;
use App\Models\SelectMaster;
use Illuminate\Database\Seeder;

class BuyerTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $buyer_type = SelectMaster::create([
            'name' => 'Buyer Type',
            'title' => 'Buyer Type'
        ]);

        $valueToOption = [
            [
                'select_id' => $buyer_type->id,
                'key_text' => 'First home buyer',
                'value_text' => 'First home buyer',
            ],
            [
                'select_id' => $buyer_type->id,
                'key_text' => 'Investor',
                'value_text' => 'Investor',
            ],
            [
                'select_id' => $buyer_type->id,
                'key_text' => 'Downsizer',
                'value_text' => 'Downsizer',
            ],
            [
                'select_id' => $buyer_type->id,
                'key_text' => 'Upgrader',
                'value_text' => 'Upgrader',
            ]
        ];
        OptionMaster::insert($valueToOption);
    }
}
