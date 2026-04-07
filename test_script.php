<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Storage;
$path = Storage::disk('local')->path('imports/test.csv');
echo "Resolved Path: " . $path . "\n";
echo "File Exists: " . (file_exists($path) ? "Yes" : "No") . "\n";
