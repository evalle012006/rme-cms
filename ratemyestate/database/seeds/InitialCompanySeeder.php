<?php

use Illuminate\Database\Seeder;

class InitialCompanySeeder extends Seeder
{
    public function run()
    {
        $companies = [
            ['company_name' => 'Mirvac', 'status' => 0, 'website' => 'https://www.mirvac.com/'],
            ['company_name' => 'Stocklands', 'status' => 0, 'website' => 'https://www.stockland.com.au/'],
            ['company_name' => 'Frasers Property', 'status' => 0, 'website' => 'https://www.frasersproperty.com.au/'],
            ['company_name' => 'Ausbuild', 'status' => 0, 'website' => 'https://www.ausbuild.com.au/'],
            ['company_name' => 'Metricon', 'status' => 0, 'website' => 'https://www.metricon.com.au/'],
            ['company_name' => 'Mosaic', 'status' => 0, 'website' => 'https://www.mosaicproperty.com.au/'],
            ['company_name' => 'Satterley', 'status' => 0, 'website' => 'https://satterley.com.au/'],
            ['company_name' => 'Peet', 'status' => 0, 'website' => 'http://WWW.PEET.COM.AU'],
            ['company_name' => 'Unison Projects', 'status' => 0, 'website' => 'http://www.unisonprojects.net.au/'],
            ['company_name' => 'Sekisui House', 'status' => 0, 'website' => 'https://www.sekisuihouse.com.au/'],
            ['company_name' => 'Sunland', 'status' => 0, 'website' => 'https://www.sunlandgroup.com.au/'],
            ['company_name' => 'Avid Property Group', 'status' => 0, 'website' => 'https://www.avid.com.au/'],
            ['company_name' => 'AV Jennings', 'status' => 0, 'website' => 'https://www.avjennings.com.au/'],
            ['company_name' => 'Stylemaster', 'status' => 0, 'website' => 'https://stylemasterhomes.com.au'],
            ['company_name' => 'Hallmark Homes', 'status' => 0, 'website' => 'https://hallmarkhomes.com.au/'],
            ['company_name' => 'Mcarthy Homes', 'status' => 0, 'website' => 'https://www.mccarthyhomes.com.au'],
            ['company_name' => 'Coral Homes', 'status' => 0, 'website' => 'https://coralhomes.com.au'],
            ['company_name' => 'Privium Group', 'status' => 0, 'website' => 'https://privium.com.au/'],
            ['company_name' => 'Plantation Homes', 'status' => 0, 'website' => 'https://www.plantationhomes.com.au'],
            ['company_name' => 'Condev Constructions', 'status' => 0, 'website' => 'https://condevconstruction.com.au'],
            ['company_name' => 'Tamawood', 'status' => 0, 'website' => 'https://www.tamawood.com.au'],
            ['company_name' => 'Clarendon', 'status' => 0, 'website' => 'https://www.clarendon.com.au'],
            ['company_name' => 'Brighton Homes', 'status' => 0, 'website' => 'https://www.brightonhomes.net.au'],
            ['company_name' => 'Simonds Homes', 'status' => 0, 'website' => 'https://www.simonds.com.au'],
            ['company_name' => 'Burbank', 'status' => 0, 'website' => 'https://www.burbank.com.au'],
        ];

        foreach ($companies as $company) {
            \App\Models\CompanyDetails::create($company);
        }
    }
}
