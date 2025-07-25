<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

use App\Models\User;

$user = User::first();

if ($user) {
    echo "User: {$user->email}\n";
    echo "Has 'view_archives' permission: ";
    var_export($user->hasPermissionTo('view_archives'));
    echo "\nRoles: ";
    var_export($user->getRoleNames());
    echo "\nPermissions: ";
    var_export($user->getAllPermissions()->pluck('name'));
    echo "\n";
} else {
    echo "No user found.\n";
}
