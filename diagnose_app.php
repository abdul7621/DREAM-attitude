<?php
/**
 * Dream Attitude Application Diagnostics & Integrity Check
 * 
 * This script:
 * 1. Recursively syntax-checks (lints) all PHP files in key folders (app, routes, config).
 * 2. Boots the Laravel framework.
 * 3. Inspects all registered routes to verify controllers/actions are valid, importable, and have correct methods.
 */

// Define color helper for terminal output
function color($text, $colorCode) {
    return "\033[{$colorCode}m{$text}\033[0m";
}

echo color("=== starting system diagnostics ===\n", "36");

// 1. Syntax Check (Linting) PHP Files
echo "\n" . color("Step 1: Checking syntax of PHP files...", "33") . "\n";
$folders = ['app', 'config', 'routes', 'database/migrations', 'database/seeders'];
$invalidFiles = [];
$totalFiles = 0;

foreach ($folders as $folder) {
    $dirPath = __DIR__ . '/' . $folder;
    if (!is_dir($dirPath)) continue;

    $directory = new RecursiveDirectoryIterator($dirPath);
    $iterator = new RecursiveIteratorIterator($directory);
    $regex = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

    foreach ($regex as $file) {
        $filePath = $file[0];
        $totalFiles++;
        
        // Run php -l (lint check)
        $output = [];
        $returnVar = 0;
        exec("php -l " . escapeshellarg($filePath) . " 2>&1", $output, $returnVar);
        
        if ($returnVar !== 0) {
            $invalidFiles[] = [
                'file' => $filePath,
                'error' => implode("\n", $output)
            ];
            echo color("✗", "31");
        } else {
            // Echo tiny progress dot
            if ($totalFiles % 50 === 0) {
                echo ".";
            }
        }
    }
}

echo "\nLinted $totalFiles PHP files.\n";

if (!empty($invalidFiles)) {
    echo color("\n❌ Syntax errors found in the following files:\n", "31");
    foreach ($invalidFiles as $item) {
        echo color($item['file'], "33") . "\n" . $item['error'] . "\n\n";
    }
    exit(1);
} else {
    echo color("✔ All PHP files passed syntax linting!\n", "32");
}

// 2. Boot Laravel & Validate Routes
echo "\n" . color("Step 2: Booting Laravel to check route controllers and actions...", "33") . "\n";

try {
    require __DIR__.'/vendor/autoload.php';
    $app = require_once __DIR__.'/bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    echo color("✔ Laravel booted successfully!\n", "32");
} catch (\Throwable $e) {
    echo color("❌ Laravel failed to boot: " . $e->getMessage() . "\n" . $e->getTraceAsString(), "31") . "\n";
    exit(1);
}

// Get all routes
$routeCollection = Route::getRoutes();
$totalRoutes = count($routeCollection);
$routeErrors = [];
$checkedCount = 0;

echo "\n" . color("Step 3: Checking $totalRoutes registered routes...", "33") . "\n";

foreach ($routeCollection as $route) {
    $action = $route->getAction();
    $uri = $route->uri();
    $methods = implode('|', $route->methods());
    
    // Skip closure routes
    if (!isset($action['uses']) || is_callable($action['uses'])) {
        $checkedCount++;
        continue;
    }
    
    $uses = $action['uses'];
    
    if (is_string($uses) && str_contains($uses, '@')) {
        list($controllerClass, $methodName) = explode('@', $uses);
        
        // 1. Check if class exists
        if (!class_exists($controllerClass)) {
            $routeErrors[] = [
                'route' => "[$methods] $uri",
                'error' => "Controller class '{$controllerClass}' does not exist."
            ];
            echo color("✗", "31");
            continue;
        }
        
        // 2. Check if method exists in the class
        if (!method_exists($controllerClass, $methodName)) {
            $routeErrors[] = [
                'route' => "[$methods] $uri",
                'error' => "Method '{$methodName}' does not exist in '{$controllerClass}'."
            ];
            echo color("✗", "31");
            continue;
        }
        
        // 3. Try to resolve dependencies for controller instantiation (optional but useful)
        try {
            $refClass = new ReflectionClass($controllerClass);
            if ($refClass->isInstantiable()) {
                // Just checking if ReflectionClass can load it without errors
            }
        } catch (\Throwable $e) {
            $routeErrors[] = [
                'route' => "[$methods] $uri",
                'error' => "Reflection error on '{$controllerClass}': " . $e->getMessage()
            ];
            echo color("✗", "31");
            continue;
        }
    }
    
    $checkedCount++;
}

echo "\nChecked $checkedCount route controller bindings.\n";

if (!empty($routeErrors)) {
    echo color("\n❌ Route controller resolution errors found:\n", "31");
    foreach ($routeErrors as $item) {
        echo color($item['route'], "33") . "\n   → " . $item['error'] . "\n\n";
    }
    exit(1);
} else {
    echo color("✔ All route controllers, action bindings, and methods exist and are valid!\n", "32");
}

echo "\n" . color("✔ SUCCESS: All diagnostics passed without any errors!", "32") . "\n";
