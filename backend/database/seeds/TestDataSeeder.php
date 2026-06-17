<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * TestDataSeeder
 *
 * Seeds realistic test data for every screen in the React CMS:
 *   - Users (reviewers + builders + admin)
 *   - Companies
 *   - Properties (approved + pending)
 *   - Reviews (approved + pending) with ratings and tags
 *   - Advertises (approved + pending)
 *   - Contact Us submissions
 *   - Dashboard chart data (spread across the current week + last 6 months)
 *
 * Usage:
 *   php artisan db:seed --class=TestDataSeeder
 *
 * Safe to re-run: skips if data already exists.
 * For a clean slate: php artisan migrate:fresh --seed
 */
class TestDataSeeder extends Seeder
{
    const ADMIN_USER_ID        = 1;
    const CATEGORY_HOMESITE    = 1;
    const CATEGORY_HOME        = 2;
    const CATEGORY_APARTMENT   = 3;
    const CATEGORY_TOWNHOME    = 4;
    const CATEGORY_HOMEBUILDER = 5;

    public function run()
    {
        if (DB::table('users')->where('email', 'reviewer1@test.com')->exists()) {
            $this->command->info('TestDataSeeder: data already present, skipping.');
            return;
        }

        DB::transaction(function () {
            $this->seedCompanies();
            $this->seedReviewers();
            $this->seedBuilders();
            $this->seedProperties();
            $this->seedReviews();
            $this->seedAdvertises();
            $this->seedContactUs();
        });

        $this->command->info('TestDataSeeder: done.');
    }

    // ── Companies ─────────────────────────────────────────────────────────────

    private function seedCompanies(): void
    {
        $companies = [
            [
                'company_name' => 'Meriton Group',
                'slug'         => 'meriton-group',
                'website'      => 'https://www.meriton.com.au',
                'bio'          => 'One of Australia\'s largest apartment developers.',
                'status'       => 1,
                'is_from'      => 'admin',
                'created_at'   => Carbon::now()->subMonths(6),
                'updated_at'   => Carbon::now()->subMonths(6),
            ],
            [
                'company_name' => 'Mirvac',
                'slug'         => 'mirvac',
                'website'      => 'https://www.mirvac.com',
                'bio'          => 'Integrated real estate group focused on urban communities.',
                'status'       => 1,
                'is_from'      => 'admin',
                'created_at'   => Carbon::now()->subMonths(5),
                'updated_at'   => Carbon::now()->subMonths(5),
            ],
            [
                'company_name' => 'Stockland',
                'slug'         => 'stockland',
                'website'      => 'https://www.stockland.com.au',
                'bio'          => 'Developing communities across residential and commercial.',
                'status'       => 1,
                'is_from'      => 'admin',
                'created_at'   => Carbon::now()->subMonths(4),
                'updated_at'   => Carbon::now()->subMonths(4),
            ],
            [
                'company_name' => 'Lendlease',
                'slug'         => 'lendlease',
                'website'      => 'https://www.lendlease.com',
                'bio'          => 'Global real estate and investment group.',
                'status'       => 0,
                'is_from'      => 'admin',
                'created_at'   => Carbon::now()->subMonths(1),
                'updated_at'   => Carbon::now()->subMonths(1),
            ],
        ];

        DB::table('company_details')->insert($companies);
        $this->command->info('  ✓ Companies seeded');
    }

    // ── Reviewers ─────────────────────────────────────────────────────────────

    private function seedReviewers(): void
    {
        $reviewerRoleId = DB::table('roles')->where('name', 'reviewer')->value('id');

        $reviewers = [
            ['firstname' => 'Alice',   'lastname' => 'Nguyen',   'display_name' => 'alice_n',   'email' => 'reviewer1@test.com',  'status' => 1, 'mobile' => '0411000001', 'location' => 'Sydney NSW',     'months_ago' => 5],
            ['firstname' => 'Bob',     'lastname' => 'Smith',    'display_name' => 'bob_smith',  'email' => 'reviewer2@test.com',  'status' => 1, 'mobile' => '0411000002', 'location' => 'Melbourne VIC',  'months_ago' => 4],
            ['firstname' => 'Carol',   'lastname' => 'Lee',      'display_name' => 'carol_lee',  'email' => 'reviewer3@test.com',  'status' => 1, 'mobile' => '0411000003', 'location' => 'Brisbane QLD',   'months_ago' => 3],
            ['firstname' => 'David',   'lastname' => 'Brown',    'display_name' => 'dave_b',     'email' => 'reviewer4@test.com',  'status' => 2, 'mobile' => '0411000004', 'location' => 'Perth WA',       'months_ago' => 2],
            ['firstname' => 'Eva',     'lastname' => 'Johnson',  'display_name' => 'eva_j',      'email' => 'reviewer5@test.com',  'status' => 1, 'mobile' => '0411000005', 'location' => 'Adelaide SA',    'months_ago' => 1],
            ['firstname' => 'Frank',   'lastname' => 'Williams', 'display_name' => 'frankw',     'email' => 'reviewer6@test.com',  'status' => 3, 'mobile' => '0411000006', 'location' => 'Hobart TAS',     'months_ago' => 1],
            ['firstname' => 'Grace',   'lastname' => 'Taylor',   'display_name' => 'grace_t',    'email' => 'reviewer7@test.com',  'status' => 1, 'mobile' => '0411000007', 'location' => 'Darwin NT',      'months_ago' => 0],
            ['firstname' => 'Henry',   'lastname' => 'Anderson', 'display_name' => 'henry_a',    'email' => 'reviewer8@test.com',  'status' => 1, 'mobile' => '0411000008', 'location' => 'Canberra ACT',   'months_ago' => 0],
            ['firstname' => 'Isla',    'lastname' => 'Martinez', 'display_name' => 'isla_m',     'email' => 'reviewer9@test.com',  'status' => 4, 'mobile' => '0411000009', 'location' => 'Gold Coast QLD', 'months_ago' => 0],
            ['firstname' => 'Jack',    'lastname' => 'Wilson',   'display_name' => 'jack_w',     'email' => 'reviewer10@test.com', 'status' => 1, 'mobile' => '0411000010', 'location' => 'Newcastle NSW',  'months_ago' => 0],
        ];

        foreach ($reviewers as $r) {
            $createdAt = Carbon::now()->subMonths($r['months_ago'])->subDays(rand(0, 20));
            $userId = DB::table('users')->insertGetId([
                'firstname'         => $r['firstname'],
                'lastname'          => $r['lastname'],
                'display_name'      => $r['display_name'],
                'email'             => $r['email'],
                'password'          => Hash::make('password'),
                'mobile'            => $r['mobile'],
                'location'          => $r['location'],
                'status'            => $r['status'],
                'email_verified_at' => $createdAt,
                'created_at'        => $createdAt,
                'updated_at'        => $createdAt,
            ]);
            if ($reviewerRoleId) {
                DB::table('model_has_roles')->insert([
                    'role_id'    => $reviewerRoleId,
                    'model_type' => 'App\\Models\\User',
                    'model_id'   => $userId,
                ]);
            }
        }

        $this->command->info('  ✓ Reviewers seeded');
    }

    // ── Builders ──────────────────────────────────────────────────────────────

    private function seedBuilders(): void
    {
        $builderRoleId = DB::table('roles')->where('name', 'builder')->value('id');

        $meritonId   = DB::table('company_details')->where('slug', 'meriton-group')->value('id');
        $mirvacId    = DB::table('company_details')->where('slug', 'mirvac')->value('id');
        $stocklandId = DB::table('company_details')->where('slug', 'stockland')->value('id');
        $lendleaseId = DB::table('company_details')->where('slug', 'lendlease')->value('id');

        $builders = [
            ['firstname' => 'Meriton',   'lastname' => 'Admin',   'email' => 'builder1@test.com', 'company_id' => $meritonId,   'brand_type' => 1, 'status' => 1, 'verified' => true,  'months_ago' => 5],
            ['firstname' => 'Mirvac',    'lastname' => 'Admin',   'email' => 'builder2@test.com', 'company_id' => $mirvacId,    'brand_type' => 2, 'status' => 1, 'verified' => true,  'months_ago' => 4],
            ['firstname' => 'Stockland', 'lastname' => 'Admin',   'email' => 'builder3@test.com', 'company_id' => $stocklandId, 'brand_type' => 3, 'status' => 1, 'verified' => true,  'months_ago' => 3],
            ['firstname' => 'Lendlease', 'lastname' => 'Admin',   'email' => 'builder4@test.com', 'company_id' => $lendleaseId, 'brand_type' => 1, 'status' => 2, 'verified' => false, 'months_ago' => 1],
            ['firstname' => 'NewDev',    'lastname' => 'Pending', 'email' => 'builder5@test.com', 'company_id' => null,          'brand_type' => 1, 'status' => 2, 'verified' => false, 'months_ago' => 0],
        ];

        foreach ($builders as $b) {
            $createdAt = Carbon::now()->subMonths($b['months_ago'])->subDays(rand(0, 10));
            $userId = DB::table('users')->insertGetId([
                'firstname'         => $b['firstname'],
                'lastname'          => $b['lastname'],
                'display_name'      => $b['firstname'] . ' ' . $b['lastname'],
                'email'             => $b['email'],
                'password'          => Hash::make('password'),
                'company_id'        => $b['company_id'],
                'brand_type'        => $b['brand_type'],
                'status'            => $b['status'],
                'email_verified_at' => $b['verified'] ? $createdAt : null,
                'created_at'        => $createdAt,
                'updated_at'        => $createdAt,
            ]);
            if ($builderRoleId) {
                DB::table('model_has_roles')->insert([
                    'role_id'    => $builderRoleId,
                    'model_type' => 'App\\Models\\User',
                    'model_id'   => $userId,
                ]);
            }
        }

        $this->command->info('  ✓ Builders seeded');
    }

    // ── Properties ────────────────────────────────────────────────────────────

    private function seedProperties(): void
    {
        $meritonId   = DB::table('company_details')->where('slug', 'meriton-group')->value('id');
        $mirvacId    = DB::table('company_details')->where('slug', 'mirvac')->value('id');
        $stocklandId = DB::table('company_details')->where('slug', 'stockland')->value('id');

        $properties = [
            ['project_name' => 'Meriton Suites World Tower',  'developer_name' => $meritonId,   'status' => 1, 'state' => 'New South Wales', 'city' => 'Sydney',     'suburb' => 'World Square',  'post_code' => '2000', 'address' => '1 World Square, Sydney NSW 2000',         'lat' => '-33.8731', 'long' => '151.2069', 'cat' => self::CATEGORY_APARTMENT,   'months_ago' => 5],
            ['project_name' => 'Meriton Bondi Junction',      'developer_name' => $meritonId,   'status' => 1, 'state' => 'New South Wales', 'city' => 'Sydney',     'suburb' => 'Bondi Junction','post_code' => '2022', 'address' => '97 Grafton St, Bondi Junction NSW 2022',   'lat' => '-33.8917', 'long' => '151.2489', 'cat' => self::CATEGORY_APARTMENT,   'months_ago' => 4],
            ['project_name' => 'Mirvac Tullamore',            'developer_name' => $mirvacId,    'status' => 1, 'state' => 'Victoria',        'city' => 'Melbourne',  'suburb' => 'Doncaster',     'post_code' => '3108', 'address' => '301 Doncaster Rd, Doncaster VIC 3108',     'lat' => '-37.7838', 'long' => '145.1221', 'cat' => self::CATEGORY_HOMESITE,    'months_ago' => 4],
            ['project_name' => 'Mirvac Olivine',              'developer_name' => $mirvacId,    'status' => 1, 'state' => 'Victoria',        'city' => 'Melbourne',  'suburb' => 'Donnybrook',    'post_code' => '3064', 'address' => 'Donnybrook Rd, Donnybrook VIC 3064',       'lat' => '-37.5910', 'long' => '144.9942', 'cat' => self::CATEGORY_HOME,        'months_ago' => 3],
            ['project_name' => 'Stockland Aura',              'developer_name' => $stocklandId, 'status' => 1, 'state' => 'Queensland',      'city' => 'Caloundra',  'suburb' => 'Baringa',       'post_code' => '4551', 'address' => 'Baringa Pkwy, Baringa QLD 4551',           'lat' => '-26.8218', 'long' => '152.9919', 'cat' => self::CATEGORY_HOMESITE,    'months_ago' => 3],
            ['project_name' => 'Stockland Willowdale',        'developer_name' => $stocklandId, 'status' => 1, 'state' => 'New South Wales', 'city' => 'Sydney',     'suburb' => 'Denham Court',  'post_code' => '2565', 'address' => 'Denham Court Rd, Denham Court NSW 2565',   'lat' => '-33.9905', 'long' => '150.7865', 'cat' => self::CATEGORY_HOMESITE,    'months_ago' => 2],
            ['project_name' => 'Mirvac Garibaldi',            'developer_name' => $mirvacId,    'status' => 1, 'state' => 'New South Wales', 'city' => 'Sydney',     'suburb' => 'Alexandria',    'post_code' => '2015', 'address' => '1 Garibaldi St, Alexandria NSW 2015',      'lat' => '-33.9143', 'long' => '151.1938', 'cat' => self::CATEGORY_TOWNHOME,    'months_ago' => 2],
            ['project_name' => 'Meriton Parramatta',          'developer_name' => $meritonId,   'status' => 1, 'state' => 'New South Wales', 'city' => 'Parramatta', 'suburb' => 'Parramatta',    'post_code' => '2150', 'address' => '25 George St, Parramatta NSW 2150',        'lat' => '-33.8148', 'long' => '151.0022', 'cat' => self::CATEGORY_APARTMENT,   'months_ago' => 1],
            ['project_name' => 'Stockland Halcyon Landing',   'developer_name' => $stocklandId, 'status' => 1, 'state' => 'Queensland',      'city' => 'Greenbank',  'suburb' => 'Greenbank',     'post_code' => '4124', 'address' => 'Teviot Rd, Greenbank QLD 4124',            'lat' => '-27.7386', 'long' => '152.9813', 'cat' => self::CATEGORY_HOMEBUILDER, 'months_ago' => 1],
            ['project_name' => 'Mirvac Encompass',            'developer_name' => $mirvacId,    'status' => 1, 'state' => 'Victoria',        'city' => 'Craigieburn','suburb' => 'Craigieburn',   'post_code' => '3064', 'address' => 'Craigieburn Rd, Craigieburn VIC 3064',     'lat' => '-37.5999', 'long' => '144.9460', 'cat' => self::CATEGORY_HOMESITE,    'months_ago' => 0],
            ['project_name' => 'Stockland Carlingford Court', 'developer_name' => $stocklandId, 'status' => 0, 'state' => 'New South Wales', 'city' => 'Carlingford','suburb' => 'Carlingford',   'post_code' => '2118', 'address' => '339 Pennant Hills Rd, Carlingford NSW 2118','lat' => '-33.7743', 'long' => '151.0446', 'cat' => self::CATEGORY_APARTMENT,   'months_ago' => 0],
            ['project_name' => 'Meriton North Sydney',        'developer_name' => $meritonId,   'status' => 0, 'state' => 'New South Wales', 'city' => 'North Sydney','suburb' => 'North Sydney',  'post_code' => '2060', 'address' => '100 Miller St, North Sydney NSW 2060',     'lat' => '-33.8399', 'long' => '151.2072', 'cat' => self::CATEGORY_APARTMENT,   'months_ago' => 0],
        ];

        foreach ($properties as $p) {
            $createdAt  = Carbon::now()->subMonths($p['months_ago'])->subDays(rand(0, 15));
            $propertyId = DB::table('properties')->insertGetId([
                'project_name'   => $p['project_name'],
                'developer_name' => $p['developer_name'],
                'status'         => $p['status'],
                'state'          => $p['state'],
                'city'           => $p['city'],
                'suburb'         => $p['suburb'],
                'post_code'      => $p['post_code'],
                'address'        => $p['address'],
                'country'        => 'Australia',
                'lat'            => $p['lat'],
                'long'           => $p['long'],
                'slug'           => \Illuminate\Support\Str::slug($p['project_name']),
                'is_from'        => 'admin',
                'created_by'     => self::ADMIN_USER_ID,
                'created_at'     => $createdAt,
                'updated_at'     => $createdAt,
            ]);

            DB::table('property_available_categories')->insert([
                'property_id'          => $propertyId,
                'property_category_id' => $p['cat'],
            ]);
        }

        $this->command->info('  ✓ Properties seeded');
    }

    // ── Reviews ───────────────────────────────────────────────────────────────

    private function seedReviews(): void
    {
        $reviewerIds = DB::table('users')
            ->whereIn('email', array_map(fn($i) => "reviewer{$i}@test.com", range(1, 10)))
            ->pluck('id')
            ->toArray();

        $propertyIds = DB::table('properties')
            ->where('status', 1)
            ->pluck('id')
            ->toArray();

        $tagIds = DB::table('tags')->where('is_active', 1)->pluck('id')->toArray();

        $ratingConfIds = DB::table('rating_configurations')->pluck('id')->toArray();

        $titles = [
            'Great location and quality finish',
            'Exceeded our expectations',
            'Solid construction, minor defects',
            'Good value for first home buyers',
            'Disappointing settlement process',
            'Beautiful community, well planned',
            'Builder was responsive and professional',
            'Some delays but worth the wait',
            'Highly recommended to families',
            'Average experience overall',
        ];

        $comments = [
            'The team was very professional throughout the process. We are happy with our new home.',
            'Settlement took longer than expected but the final result is excellent.',
            'A few minor defects were found during inspection but all were resolved promptly.',
            'Great location, close to schools and transport. Very happy with the purchase.',
            'The customer service team could have been more responsive during construction.',
            'Beautiful community with great amenities nearby. Would buy here again.',
            'The builder kept us informed at every stage. Highly recommend.',
            'Some delays due to weather but the quality of the build is top notch.',
            'Perfect for young families. Lots of parks and good school zoning.',
            'Decent property but nothing exceptional. Met our basic requirements.',
        ];

        $states    = ['New South Wales', 'Victoria', 'Queensland', 'Western Australia', 'South Australia'];
        $suburbs   = ['Surry Hills', 'Fitzroy', 'Fortitude Valley', 'Subiaco', 'Norwood'];
        $postCodes = ['2010', '3065', '4006', '6008', '5067'];

        $reviewCount = 0;

        foreach ($propertyIds as $propertyId) {
            $reviewsForProperty = rand(2, 4);
            for ($r = 0; $r < $reviewsForProperty; $r++) {
                $createdAt  = Carbon::now()->subMonths(rand(0, 5))->subDays(rand(0, 25));
                $reviewerId = $reviewerIds[array_rand($reviewerIds)];
                $stateIdx   = array_rand($states);
                $titleIdx   = $reviewCount % count($titles);

                $reviewId = DB::table('reviews')->insertGetId([
                    'property_id'        => $propertyId,
                    'user_id'            => $reviewerId,
                    'category_id'        => self::CATEGORY_HOMESITE,
                    'title'              => $titles[$titleIdx],
                    'comment'            => $comments[$reviewCount % count($comments)],
                    'buyer_type'         => rand(1, 3),
                    'recommendation'     => rand(0, 1),
                    'state'              => $states[$stateIdx],
                    'suburb'             => $suburbs[$stateIdx],
                    'post_code'          => $postCodes[$stateIdx],
                    'year_of_completion' => rand(2020, 2024),
                    'status'             => 1,
                    'slug'               => \Illuminate\Support\Str::slug($titles[$titleIdx]) . '-' . $reviewCount,
                    'created_at'         => $createdAt,
                    'updated_at'         => $createdAt,
                ]);

                // review_tags: only review_id, tag_id, parent_id — no timestamps
                if (!empty($tagIds)) {
                    foreach (array_slice($tagIds, 0, rand(1, min(2, count($tagIds)))) as $tagId) {
                        DB::table('review_tags')->insert([
                            'review_id' => $reviewId,
                            'tag_id'    => $tagId,
                            'parent_id' => null,
                        ]);
                    }
                }

                // ratings: has timestamps
                foreach ($ratingConfIds as $confId) {
                    DB::table('ratings')->insert([
                        'review_id'      => $reviewId,
                        'rating_conf_id' => $confId,
                        'rate'           => rand(2, 5),
                        'parent_id'      => null,
                        'created_at'     => $createdAt,
                        'updated_at'     => $createdAt,
                    ]);
                }

                $reviewCount++;
            }

            // 1 pending review per property
            $pendingAt = Carbon::now()->subDays(rand(1, 14));
            $pendingId = DB::table('reviews')->insertGetId([
                'property_id'        => $propertyId,
                'user_id'            => $reviewerIds[array_rand($reviewerIds)],
                'category_id'        => self::CATEGORY_HOMESITE,
                'title'              => 'Pending: ' . $titles[$reviewCount % count($titles)],
                'comment'            => 'This review is awaiting moderation.',
                'buyer_type'         => 1,
                'recommendation'     => 1,
                'state'              => 'New South Wales',
                'suburb'             => 'Surry Hills',
                'post_code'          => '2010',
                'year_of_completion' => 2024,
                'status'             => 0,
                'slug'               => 'pending-review-' . $propertyId . '-' . $reviewCount,
                'created_at'         => $pendingAt,
                'updated_at'         => $pendingAt,
            ]);

            foreach ($ratingConfIds as $confId) {
                DB::table('ratings')->insert([
                    'review_id'      => $pendingId,
                    'rating_conf_id' => $confId,
                    'rate'           => rand(1, 5),
                    'parent_id'      => null,
                    'created_at'     => $pendingAt,
                    'updated_at'     => $pendingAt,
                ]);
            }

            $reviewCount++;
        }

        $this->command->info("  ✓ Reviews seeded ({$reviewCount} total)");
    }

    // ── Advertises ────────────────────────────────────────────────────────────

    private function seedAdvertises(): void
    {
        $builderUsers = DB::table('users')
            ->whereIn('email', ['builder1@test.com', 'builder2@test.com', 'builder3@test.com'])
            ->get(['id', 'company_id']);

        $advertises = [
            ['title' => 'Meriton Spring Sale',        'description' => 'Special offers on Sydney apartments this spring.',      'status' => 1],
            ['title' => 'Meriton Bondi Promo',        'description' => 'Limited release units at Bondi Junction.',              'status' => 0],
            ['title' => 'Mirvac Tullamore Launch',    'description' => 'New land lots now available at Tullamore Doncaster.',   'status' => 1],
            ['title' => 'Mirvac Olivine Stage 4',     'description' => 'Stage 4 now open at Olivine Donnybrook.',               'status' => 0],
            ['title' => 'Stockland Aura Townhomes',   'description' => 'Affordable townhomes in the heart of Caloundra South.', 'status' => 1],
            ['title' => 'Stockland Willowdale Offer', 'description' => 'First home buyer incentives at Willowdale.',            'status' => 1],
        ];

        foreach ($advertises as $i => $adv) {
            $builderUser = $builderUsers[$i % count($builderUsers)];
            $createdAt   = Carbon::now()->subMonths(rand(0, 3))->subDays(rand(0, 20));
            DB::table('advertise')->insert([
                'title'       => $adv['title'],
                'description' => $adv['description'],
                'status'      => $adv['status'],
                'created_by'  => $builderUser->id,
                'company_id'  => $builderUser->company_id,
                'created_at'  => $createdAt,
                'updated_at'  => $createdAt,
            ]);
        }

        $this->command->info('  ✓ Advertises seeded');
    }

    // ── Contact Us ────────────────────────────────────────────────────────────

    private function seedContactUs(): void
    {
        $submissions = [
            ['firstname' => 'Liam',    'lastname' => 'Chen',     'email' => 'liam.chen@example.com',    'subject' => 'Question about listing',        'message' => 'Hi, I would like to know how to list my property on your platform.'],
            ['firstname' => 'Olivia',  'lastname' => 'Park',     'email' => 'olivia.park@example.com',  'subject' => 'Review removal request',         'message' => 'I believe one of my reviews was incorrectly moderated. Can you please review it?'],
            ['firstname' => 'Noah',    'lastname' => 'Patel',    'email' => 'noah.patel@example.com',   'subject' => 'Partnership inquiry',            'message' => 'We are interested in partnering with RateMyEstate to promote our developments.'],
            ['firstname' => 'Emma',    'lastname' => 'Walsh',    'email' => 'emma.walsh@example.com',   'subject' => 'Technical issue on website',     'message' => 'The review submission form is not loading on mobile. Please fix this.'],
            ['firstname' => 'William', 'lastname' => 'Okafor',   'email' => 'will.okafor@example.com',  'subject' => 'Request for data export',        'message' => 'Can I get a copy of my submitted reviews in CSV format?'],
            ['firstname' => 'Sophia',  'lastname' => 'Nguyen',   'email' => 'sophia.n@example.com',     'subject' => 'Feedback on new website',        'message' => 'Love the new design! Much easier to use. Keep up the good work.'],
            ['firstname' => 'James',   'lastname' => 'OBrien',   'email' => 'james.obrien@example.com', 'subject' => 'Builder account query',          'message' => 'How do I get my company verified on the platform?'],
            ['firstname' => 'Ava',     'lastname' => 'Kim',      'email' => 'ava.kim@example.com',      'subject' => 'Incorrect property information', 'message' => 'The address for one of the properties appears to be wrong. Please check listing ID 5.'],
        ];

        foreach ($submissions as $s) {
            $createdAt = Carbon::now()->subDays(rand(0, 60));
            DB::table('contact_us')->insert(array_merge($s, [
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]));
        }

        $this->command->info('  ✓ Contact Us submissions seeded');
    }
}