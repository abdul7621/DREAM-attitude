<?php
$c = file_get_contents('c:/Users/ADMIN/Dream Attitude/commerce/public/css/storefront.css');
if (strpos($c, 'd-md-none') !== false) {
    echo "Found d-md-none\n";
} else {
    echo "NOT FOUND d-md-none\n";
}
if (strpos($c, 'd-md-flex') !== false) {
    echo "Found d-md-flex\n";
}
if (strpos($c, 'd-md-block') !== false) {
    echo "Found d-md-block\n";
}
