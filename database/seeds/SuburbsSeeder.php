<?php

use App\Models\Suburbs;
use Illuminate\Database\Seeder;

class SuburbsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = json_decode(file_get_contents(public_path('/json-data/suburbs.json')), true);
        foreach (array_chunk($data, 1000) as $chunkArray) {
            Suburbs::insert($chunkArray);
        }
    }
}
