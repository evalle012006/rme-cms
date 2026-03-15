<?php

use App\Models\CompanyDetails;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user1 = User::create(['name' => 'admin',
            'firstname' => 'admin',
            'lastname' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin123'),
            'status' => 1
        ]);
        $user1->assignRole('admin');


        $user2 = User::create(['name' => 'reviewer',
            'firstname' => 'reviewer',
            'lastname' => 'reviewer',
            'email' => 'reviewer@gmail.com',
            'password' => bcrypt('reviewer123'),
            'status' => 1
        ]);
        $user2->assignRole('reviewer');

        $companyInsert['company_name'] = "Mirvac";
        $companyInsert['slug'] = "mirvac";
        $companyInsert['contact_number'] = "1234567890";
        $companyInsert['website'] = "http://google.com";
        $companyInsert['bio'] = 'hello there, this is test company';
        $companyInsert['location'] = 'Sydney';
        $companyInsert['logo'] = 'company-logo/2020/02/12/eytva6kzceqrLhEZqxYUyTCIJN6uvVZ1e0MgZqxj.png';
        $company = CompanyDetails::create($companyInsert);

        $companyInsert2['company_name'] = "Stocklands";
        $companyInsert2['slug'] = "stocklands";
        $companyInsert2['contact_number'] = "1234567890";
        $companyInsert2['website'] = "http://google.com";
        $companyInsert2['bio'] = 'hello there, this is test company';
        $companyInsert2['location'] = 'Sydney';
        $companyInsert2['logo'] = 'company-logo/2020/02/12/eytva6kzceqrLhEZqxYUyTCIJN6uvVZ1e0MgZqxj.png';
        CompanyDetails::create($companyInsert2);

        $companyIns3['company_name'] = 'Undefined Company';
        $companyIns3['slug'] = 'undefine';
        CompanyDetails::create($companyIns3);

        $user3 = User::create(['name' => 'builder',
            'firstname' => 'builder',
            'lastname' => 'builder',
            'email' => 'builder@gmail.com',
            'password' => bcrypt('builder123'),
            'status' => 1,
            'brand_type' => 2,
            'company_id' => $company->id
        ]);

        $user3->assignRole('builder');

    }
}
