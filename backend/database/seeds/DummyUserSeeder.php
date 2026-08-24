<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * DummyUserSeeder
 *
 * Creates one dummy login per user type (role):
 *   - admin    : dummy_admin@test.com    / Password@123
 *   - reviewer : dummy_reviewer@test.com / Password@123
 *   - builder  : dummy_builder@test.com  / Password@123
 *
 * Roles are managed via spatie/laravel-permission (roles table).
 *
 * Usage:
 *   php artisan db:seed --class=DummyUserSeeder
 *
 * Safe to re-run: skips users that already exist.
 */
class DummyUserSeeder extends Seeder
{
    const PASSWORD = 'Password@123';

    public function run()
    {
        $companyId = DB::table('company_details')->min('id');

        $users = [
            [
                'role'       => 'admin',
                'firstname'  => 'Dummy',
                'lastname'   => 'Admin',
                'email'      => 'dummy_admin@test.com',
                'company_id' => null,
                'brand_type' => null,
            ],
            [
                'role'       => 'reviewer',
                'firstname'  => 'Dummy',
                'lastname'   => 'Reviewer',
                'email'      => 'dummy_reviewer@test.com',
                'company_id' => null,
                'brand_type' => null,
            ],
            [
                'role'       => 'builder',
                'firstname'  => 'Dummy',
                'lastname'   => 'Builder',
                'email'      => 'dummy_builder@test.com',
                'company_id' => $companyId,
                'brand_type' => 2,
            ],
        ];

        foreach ($users as $u) {
            if (DB::table('users')->where('email', $u['email'])->exists()) {
                $this->command->info("  - {$u['email']} already exists, skipping.");
                continue;
            }

            $now = Carbon::now();
            $userId = DB::table('users')->insertGetId([
                'name'              => $u['role'],
                'firstname'         => $u['firstname'],
                'lastname'          => $u['lastname'],
                'display_name'      => strtolower($u['firstname'] . '_' . $u['lastname']),
                'email'             => $u['email'],
                'password'          => Hash::make(self::PASSWORD),
                'status'            => 1,
                'company_id'        => $u['company_id'],
                'brand_type'        => $u['brand_type'],
                'email_verified_at' => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);

            $roleId = DB::table('roles')->where('name', $u['role'])->value('id');
            if ($roleId) {
                DB::table('model_has_roles')->insert([
                    'role_id'    => $roleId,
                    'model_type' => 'App\\Models\\User',
                    'model_id'   => $userId,
                ]);
            }

            $this->command->info("  ✓ {$u['role']} created: {$u['email']} (id {$userId}).");
        }

        $this->command->info('DummyUserSeeder: done.');
    }
}
