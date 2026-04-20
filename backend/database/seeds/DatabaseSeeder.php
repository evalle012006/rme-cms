<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(PropertyCategorySeeder::class);
        $this->call(BlogCategorySeeder::class);
        $this->call(BuyerTypeSeeder::class);
        $this->call(TagsSeeder::class);
        $this->call(SuburbsSeeder::class);
        $this->call(CmsPageSeeder::class);

    }
}
