#!/usr/bin/env php
<?php

/**
 * Create (or reset) test admin users so they can log into the CMS.
 *
 *   php create-test-admin-users.php
 *
 * Password for every account: admin123
 */

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

const TEST_ADMIN_PASSWORD = 'admin123';

$admins = [
    [
        'email' => 'testadmin1@yopmail.com',
        'firstname' => 'Test',
        'lastname' => 'Admin 1',
        'name' => 'Test Admin 1',
    ],
    [
        'email' => 'testadmin2@yopmail.com',
        'firstname' => 'Test',
        'lastname' => 'Admin 2',
        'name' => 'Test Admin 2',
    ],
    [
        'email' => 'testadmin3@yopmail.com',
        'firstname' => 'Test',
        'lastname' => 'Admin 3',
        'name' => 'Test Admin 3',
    ],
    [
        'email' => 'anjali@castledigital.com.au',
        'firstname' => 'Anjali',
        'lastname' => 'Castle Digital',
        'name' => 'Anjali',
    ],
    [
        'email' => 'vishal@castledigital.com.au',
        'firstname' => 'Vishal',
        'lastname' => 'Castle Digital',
        'name' => 'Vishal',
    ],
    [
        'email' => 'alex@castledigital.com.au',
        'firstname' => 'Alex',
        'lastname' => 'Castle Digital',
        'name' => 'Alex',
    ],
];

$backend = resolveBackendPath(__DIR__);
if ($backend === null) {
    fwrite(STDERR, "Could not find backend/vendor/autoload.php from " . __DIR__ . PHP_EOL);
    exit(1);
}

require $backend . '/vendor/autoload.php';

$app = require $backend . '/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

fwrite(STDOUT, "Creating test admin users." . PHP_EOL . PHP_EOL);

foreach ($admins as $admin) {
    $user = User::updateOrCreate(
        ['email' => $admin['email']],
        [
            'name' => $admin['name'],
            'firstname' => $admin['firstname'],
            'lastname' => $admin['lastname'],
            'password' => bcrypt(TEST_ADMIN_PASSWORD),
            'status' => 1,
            'email_verified_at' => now(),
        ]
    );

    $action = $user->wasRecentlyCreated ? 'created' : 'updated';

    if (!$user->hasRole('admin')) {
        $user->assignRole('admin');
    }

    fwrite(STDOUT, sprintf(
        "  %s  %s  (id %d, role admin)%s",
        $action,
        $admin['email'],
        $user->id,
        PHP_EOL
    ));
}

fwrite(STDOUT, PHP_EOL . "Password for all accounts: " . TEST_ADMIN_PASSWORD . PHP_EOL);

$kernel->terminate(new Symfony\Component\Console\Input\ArgvInput, 0);
exit(0);

/**
 * Repo-root run uses backend/ next to this file. Docker bind-mounts this
 * file into the PHP container's backend directory.
 *
 * @param string $scriptDir
 * @return string|null
 */
function resolveBackendPath($scriptDir)
{
    if (is_file($scriptDir . '/vendor/autoload.php') && is_file($scriptDir . '/bootstrap/app.php')) {
        return $scriptDir;
    }

    $nested = $scriptDir . '/backend';
    if (is_file($nested . '/vendor/autoload.php') && is_file($nested . '/bootstrap/app.php')) {
        return $nested;
    }

    return null;
}
