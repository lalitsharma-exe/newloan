<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$docs = App\Models\Document::all();
foreach($docs as $doc) {
    echo "Doc ID: {$doc->id}, App_ID: {$doc->application_id}, User_ID: {$doc->user_id}, Type: {$doc->type}\n";
}
