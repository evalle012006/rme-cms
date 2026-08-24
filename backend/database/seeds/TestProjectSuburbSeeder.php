<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * TestProjectSuburbSeeder
 *
 * Adds two test entries ("Test-1" and "Test-2") for the two autocomplete
 * fields on review-step-1:
 *   - Project name  -> `properties` table   (PropertyListingController@fetchProperty)
 *   - Suburb        -> `suburbs` table      (HomeController@fetchSuburbs)
 *
 * Projects are created with status = 1 (so they appear in the dropdown) and are
 * linked to a property category so they show up when a category is selected.
 * Suburbs are given a dedicated postcode ("9999") because fetchSuburbs requires
 * a postcode before returning results.
 *
 * Usage:
 *   php artisan db:seed --class=TestProjectSuburbSeeder
 *
 * Safe to re-run: existing entries are skipped.
 */
class TestProjectSuburbSeeder extends Seeder
{
    const TEST_POSTCODE = '9999';

    public function run()
    {
        DB::transaction(function () {
            $this->seedTestProjects();
            $this->seedTestSuburbs();
        });

        $this->command->info('TestProjectSuburbSeeder: done.');
    }

    private function seedTestProjects(): void
    {
        // developer_name is a NOT NULL integer FK to company_details.id.
        $companyId = DB::table('company_details')->min('id') ?? 0;

        // Attach to the first available category so the project shows up when a
        // category is selected on the review form (optional but realistic).
        $categoryId = DB::table('property_categories')->min('id');

        foreach (['Test-1', 'Test-2'] as $name) {
            if (DB::table('properties')->where('project_name', $name)->exists()) {
                $this->command->info("  - Project '{$name}' already exists, skipping.");
                continue;
            }

            $now = Carbon::now();
            $propertyId = DB::table('properties')->insertGetId([
                'project_name'   => $name,
                'developer_name' => $companyId,
                'status'         => 1,
                'country'        => 'Australia',
                'state'          => 'New South Wales',
                'city'           => 'Sydney',
                'suburb'         => $name,
                'post_code'      => self::TEST_POSTCODE,
                'address'        => $name . ' Test Address, Sydney NSW ' . self::TEST_POSTCODE,
                'slug'           => Str::slug($name) . '-' . uniqid(),
                'is_from'        => 'admin',
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);

            if ($categoryId) {
                DB::table('property_available_categories')->insert([
                    'property_id'          => $propertyId,
                    'property_category_id' => $categoryId,
                ]);
            }

            $this->command->info("  ✓ Project '{$name}' created (id {$propertyId}).");
        }
    }

    private function seedTestSuburbs(): void
    {
        foreach (['Test-1', 'Test-2'] as $name) {
            $exists = DB::table('suburbs')
                ->where('name', $name)
                ->where('postcode', self::TEST_POSTCODE)
                ->exists();

            if ($exists) {
                $this->command->info("  - Suburb '{$name}' already exists, skipping.");
                continue;
            }

            DB::table('suburbs')->insert([
                'name'      => $name,
                'postcode'  => self::TEST_POSTCODE,
                'state'     => 'NSW',
                'latitude'  => '-33.8688',
                'longitude' => '151.2093',
            ]);

            $this->command->info("  ✓ Suburb '{$name}' created (postcode " . self::TEST_POSTCODE . ").");
        }
    }
}
