<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking certificate templates...\n\n";

$templates = \App\Models\CertificateTemplate::select('id', 'name', 'user_id', 'created_by')->get();

echo "Total templates: " . $templates->count() . "\n\n";

foreach ($templates as $template) {
    echo "ID: {$template->id}\n";
    echo "Name: {$template->name}\n";
    echo "user_id: " . ($template->user_id ?? 'NULL') . "\n";
    echo "created_by: " . ($template->created_by ?? 'NULL') . "\n";
    echo "---\n";
}
