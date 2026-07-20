<?php
$css = file_get_contents('public/css/storefront.css');
$lines = explode("\n", $css);
foreach ($lines as $i => $line) {
    if (strpos($line, 'sf-pdp') !== false || strpos($line, 'pdp-') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
    }
}
