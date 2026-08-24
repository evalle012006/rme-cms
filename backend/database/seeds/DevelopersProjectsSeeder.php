<?php

use App\Models\CompanyDetails;
use App\Models\Properties;
use App\Models\PropertyCategory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * DevelopersProjectsSeeder
 *
 * Imports the master list of developers/brands and their projects/estates
 * from database/data/developers-projects.json into:
 *   - company_details             (one row per developer/brand)
 *   - properties                  (one row per project)
 *   - property_available_categories (link table for project ↔ category)
 *
 * The seeder is idempotent:
 *   - Developers are matched by (case-insensitive) company_name.
 *   - Projects are matched by (developer_name, project_name).
 * Existing rows are updated in place rather than duplicated, so the seeder
 * can be re-run safely on any environment.
 *
 * Requires PropertyCategorySeeder to have been run first (for the base
 * category rows: Homesite, Home & Land, Apartments, Townhomes, Homebuilder).
 *
 * Usage:
 *   php artisan db:seed --class=DevelopersProjectsSeeder
 */
class DevelopersProjectsSeeder extends Seeder
{
    /**
     * Map free-text category tokens (as found in the source spreadsheet)
     * to the slugs used in the property_categories table.
     */
    private const CATEGORY_TOKEN_MAP = [
        'land'          => 'homesite',
        'home & land'   => 'homeland',
        'home&land'     => 'homeland',
        'home and land' => 'homeland',
        'homeland'      => 'homeland',
        'apartments'    => 'apartments',
        'apartment'     => 'apartments',
        'aparments'     => 'apartments', // typo tolerated
        'townhomes'     => 'town-homes',
        'townhome'      => 'town-homes',
        'towhomes'      => 'town-homes', // typo tolerated
        'homebuilder'   => 'homebuilder',
        'home'          => 'homebuilder',
        'homes'         => 'homebuilder',
    ];

    public function run()
    {
        $path = database_path('data/developers-projects.json');

        if (!file_exists($path)) {
            $this->command->error("DevelopersProjectsSeeder: data file not found at {$path}");
            return;
        }

        $data = json_decode(file_get_contents($path), true);
        if (!is_array($data)) {
            $this->command->error('DevelopersProjectsSeeder: dataset is invalid.');
            return;
        }

        $brands   = $data['brands']   ?? [];
        $projects = $data['projects'] ?? [];

        $categoryIdBySlug = PropertyCategory::pluck('id', 'slug')->all();
        if (empty($categoryIdBySlug)) {
            $this->command->warn('DevelopersProjectsSeeder: no property_categories found. Run PropertyCategorySeeder first.');
        }

        DB::transaction(function () use ($brands, $projects, $categoryIdBySlug) {
            $companyIdByName = $this->seedBrands($brands);
            $this->seedProjects($projects, $companyIdByName, $categoryIdBySlug);
        });

        $this->command->info('DevelopersProjectsSeeder: done.');
    }

    /**
     * @return array<string,int>  lowercased company name => company id
     */
    private function seedBrands(array $brands): array
    {
        $created = 0;
        $updated = 0;
        $map     = [];

        // Preload existing companies (case-insensitive lookup).
        foreach (CompanyDetails::withTrashed()->get(['id', 'company_name']) as $c) {
            $map[strtolower(trim($c->company_name))] = $c->id;
        }

        foreach ($brands as $brand) {
            $name = trim($brand['company_name'] ?? '');
            if ($name === '') {
                continue;
            }

            $key      = strtolower($name);
            $existing = $map[$key] ?? null;

            $attributes = [
                'company_name' => $name,
                'website'      => $brand['website']    ?? null,
                'brand_type'   => $brand['categories'] ?? null,
                'is_from'      => 'seeder',
                'status'       => 1,
            ];

            if ($existing) {
                $updateData = array_filter([
                    'website'    => $attributes['website'],
                    'brand_type' => $attributes['brand_type'],
                ], function ($v) {
                    return !is_null($v) && $v !== '';
                });
                if (!empty($updateData)) {
                    CompanyDetails::where('id', $existing)->update($updateData);
                }
                $updated++;
            } else {
                $attributes['slug'] = Str::slug($name) . '-' . Str::lower(Str::random(6));
                $company = CompanyDetails::create($attributes);
                $map[$key] = $company->id;
                $created++;
            }
        }

        $this->command->info("  brands: created {$created}, updated {$updated}.");
        return $map;
    }

    /**
     * @param  array<string,int> $companyIdByName
     * @param  array<string,int> $categoryIdBySlug
     */
    private function seedProjects(array $projects, array $companyIdByName, array $categoryIdBySlug): void
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $now     = Carbon::now();

        foreach ($projects as $p) {
            $projectName = trim($p['project_name'] ?? '');
            $brandName   = trim($p['brand']        ?? '');

            if ($projectName === '' || $brandName === '') {
                $skipped++;
                continue;
            }

            $developerId = $companyIdByName[strtolower($brandName)] ?? null;
            if (!$developerId) {
                // Fall back to creating a bare brand entry if it wasn't in the brands sheet.
                $company = CompanyDetails::create([
                    'company_name' => $brandName,
                    'slug'         => Str::slug($brandName) . '-' . Str::lower(Str::random(6)),
                    'is_from'      => 'seeder',
                    'status'       => 1,
                ]);
                $developerId = $company->id;
                $companyIdByName[strtolower($brandName)] = $developerId;
            }

            $stateCode = $this->normalizeStateCode($p['state'] ?? '');

            $data = [
                'developer_name'     => $developerId,
                'brand'              => $brandName,
                'project_name'       => $projectName,
                'address'            => $p['address']  ?? null,
                'country'            => 'Australia',
                'state'              => $stateCode,
                'city'               => $p['suburb']   ?? null,
                'suburb'             => $p['suburb']   ?? null,
                'post_code'          => (string) ($p['postcode'] ?? ''),
                'website_url'        => $p['website']  ?? null,
                'is_from'            => 'seeder',
                'status'             => 1,
            ];

            $existing = Properties::withTrashed()
                ->where('developer_name', $developerId)
                ->where('project_name', $projectName)
                ->first();

            if ($existing) {
                $existing->fill($data)->save();
                $property = $existing;
                $updated++;
            } else {
                $data['slug'] = Str::slug($projectName) . '-' . Str::lower(Str::random(6));
                $data['created_at'] = $now;
                $data['updated_at'] = $now;
                $property = Properties::create($data);
                $created++;
            }

            $this->syncCategories($property->id, $p['category'] ?? '', $categoryIdBySlug);
        }

        $this->command->info("  projects: created {$created}, updated {$updated}, skipped {$skipped}.");
    }

    private function syncCategories(int $propertyId, string $categoryText, array $categoryIdBySlug): void
    {
        if (empty($categoryIdBySlug)) {
            return;
        }

        $slugs = $this->resolveCategorySlugs($categoryText);
        if (empty($slugs)) {
            return;
        }

        $rows = [];
        foreach ($slugs as $slug) {
            $catId = $categoryIdBySlug[$slug] ?? null;
            if (!$catId) {
                continue;
            }
            $exists = DB::table('property_available_categories')
                ->where('property_id', $propertyId)
                ->where('property_category_id', $catId)
                ->exists();
            if (!$exists) {
                $rows[] = [
                    'property_id'          => $propertyId,
                    'property_category_id' => $catId,
                ];
            }
        }

        if (!empty($rows)) {
            DB::table('property_available_categories')->insert($rows);
        }
    }

    private function resolveCategorySlugs(string $text): array
    {
        if ($text === '') {
            return [];
        }

        $normalized = strtolower(str_replace(['&', '/', '\\'], [' & ', ',', ','], $text));
        $tokens     = preg_split('/,/', $normalized) ?: [];

        $slugs = [];
        foreach ($tokens as $raw) {
            $tok = trim($raw);
            if ($tok === '') {
                continue;
            }
            // Normalise whitespace.
            $tok = preg_replace('/\s+/', ' ', $tok);

            // Try full-token match first, then progressively strip words.
            $slug = self::CATEGORY_TOKEN_MAP[$tok] ?? null;

            if (!$slug) {
                // Try common variants: e.g. "home & land" produced from "home&land".
                $tok2 = str_replace(' & ', '&', $tok);
                $slug = self::CATEGORY_TOKEN_MAP[$tok2] ?? null;
            }

            if ($slug) {
                $slugs[$slug] = true;
            }
        }

        return array_keys($slugs);
    }

    private function normalizeStateCode(string $s): string
    {
        $s = strtoupper(trim(str_replace("\xC2\xA0", ' ', $s)));
        $valid = ['NSW', 'VIC', 'QLD', 'WA', 'SA', 'TAS', 'NT', 'ACT'];
        return in_array($s, $valid, true) ? $s : $s;
    }
}
