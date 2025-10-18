<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use Illuminate\\Support\\Facades\\DB;

$email = 'khairuni90@gmail.com';
$exists = DB::table('pwa_participants')->where('email', $email)->exists();
echo "exists=" . ($exists ? 'yes' : 'no') . PHP_EOL;
if ($exists) {
    $u = DB::table('pwa_participants')->where('email', $email)->first();
    echo json_encode($u, JSON_PRETTY_PRINT) . PHP_EOL;
}
