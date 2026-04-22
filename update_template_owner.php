<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Updating template ownership...\n\n";

// Get the template
$template = \App\Models\CertificateTemplate::find(14);

if (!$template) {
    echo "Template not found!\n";
    exit(1);
}

echo "Current template:\n";
echo "ID: {$template->id}\n";
echo "Name: {$template->name}\n";
echo "user_id: {$template->user_id}\n";
echo "created_by: {$template->created_by}\n\n";

// Ask for confirmation
echo "Do you want to change user_id from {$template->user_id} to 2 (Organizer)? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
$answer = trim($line);

if (strtolower($answer) !== 'yes') {
    echo "Cancelled.\n";
    exit(0);
}

// Update
$template->user_id = 2;
$template->save();

echo "\nTemplate updated successfully!\n";
echo "New user_id: {$template->user_id}\n";
