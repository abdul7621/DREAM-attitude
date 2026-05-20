<?php
$dir = new RecursiveDirectoryIterator('c:/Users/ADMIN/Dream Attitude/commerce/resources/views');
$ite = new RecursiveIteratorIterator($dir);
foreach($ite as $file) {
    if($file->isFile() && $file->getExtension() == 'php') {
        $content = file_get_contents($file->getPathname());
        if(stripos($content, 'free shipping') !== false) {
            echo $file->getPathname() . "\n";
        }
    }
}
