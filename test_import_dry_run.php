<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Storage;
use App\Services\ShopifyImporter;

Storage::disk('local')->put('imports/dummy.csv', "Handle,Title,Body (HTML),Vendor,Type,Tags,Published,Option1 Name,Option1 Value,Variant SKU,Variant Grams,Variant Inventory Tracker,Variant Inventory Qty,Variant Inventory Policy,Variant Fulfillment Service,Variant Price,Variant Compare At Price,Variant Requires Shipping,Variant Taxable,Variant Barcode,Image Src,Image Position,Image Alt Text,Gift Card,SEO Title,SEO Description,Google Shopping / Google Product Category,Google Shopping / Gender,Google Shopping / Age Group,Google Shopping / MPN,Google Shopping / AdWords Grouping,Google Shopping / AdWords Labels,Google Shopping / Condition,Google Shopping / Custom Product,Google Shopping / Custom Label 0,Google Shopping / Custom Label 1,Google Shopping / Custom Label 2,Google Shopping / Custom Label 3,Google Shopping / Custom Label 4,Variant Image,Variant Weight Unit,Variant Tax Code,Cost per item,Price / International,Compare At Price / International,Status
test-perfume,Test Perfume,A beautiful scent,Ikhlas,Attar,,true,Size,50ml,SKU123,50,shopify,10,deny,manual,150.00,200.00,true,true,,https://via.placeholder.com/150,1,,,SEO Title,SEO Description,,,,,,,,,,,,,,,,,,kg,,100,,active");

$importer = app(ShopifyImporter::class);
$path = Storage::disk('local')->path('imports/dummy.csv');

echo "CSV Path Resolved to: " . $path . "\n";
echo "File exists? " . (file_exists($path) ? 'Yes' : 'No') . "\n";

echo "Running dryRun...\n";
$stats = $importer->dryRun($path);

echo "Dry Run Stats:\n";
print_r($stats);
