<?php
$css = file_get_contents('public/css/storefront.css');
$lines = explode("\n", $css);
$in_block = false;
$brace_count = 0;
foreach ($lines as $i => $line) {
    if (strpos($line, '.sf-pdp') !== false || strpos($line, '.sf-pdp-gallery') !== false || strpos($line, 'pdp-breadcrumb') !== false) {
        $in_block = true;
    }
    if ($in_block) {
        echo "Line " . ($i + 1) . ": " . $line . "\n";
        $brace_count += substr_count($line, '{');
        $brace_count -= substr_count($line, '}');
        if ($brace_count <= 0 && strpos($line, '}') !== false) {
            $in_block = false;
            echo "\n";
        }
    }
}
