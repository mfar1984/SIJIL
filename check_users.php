<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking users and roles...\n\n";

$users = \App\Models\User::with('roles')->get();

echo "Total users: " . $users->count() . "\n\n";

foreach ($users as $user) {
    echo "ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
    echo "---\n";
}
