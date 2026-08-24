<?php

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $admin = User::updateOrCreate(
            ['email' => 'admin@ratemyestate.com.au'],
            [
                'name' => 'admin',
                'firstname' => 'admin',
                'lastname' => 'admin',
                'password' => bcrypt('Qwerty6789$&!'),
                'status' => 1,
                'email_verified_at' => now(),
            ]
        );

        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
