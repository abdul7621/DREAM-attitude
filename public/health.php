<?php
/**
 * Standalone Health Check — works even if Laravel can't boot.
 * URL: https://dreamattitude.com/health.php
 * Returns JSON { status: "healthy"|"unhealthy", checks: {...}, errors: [...] }
 */
header('Content-Type: application/json');
header('Cache-Control: no-store');

$checks = ['php' => true, 'autoloader' => false, 'laravel' => false, 'database' => false, 'storage' => false];
$errors = [];

// Detect base path (works for both public/ and public_html/)
$basePath = is_dir(__DIR__ . '/../dream-app') ? realpath(__DIR__ . '/../dream-app') : realpath(__DIR__ . '/..');

// 1. Autoloader
$autoloaderPath = $basePath . '/vendor/autoload.php';
if (file_exists($autoloaderPath)) {
    try {
        require $autoloaderPath;
        $checks['autoloader'] = true;
    } catch (\Throwable $e) {
        $errors[] = 'Autoloader: ' . $e->getMessage();
    }
} else {
    $errors[] = 'vendor/autoload.php not found at: ' . $autoloaderPath;
}

// 2. Laravel Boot
if ($checks['autoloader']) {
    try {
        $app = require $basePath . '/bootstrap/app.php';
        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
        $checks['laravel'] = true;

        // 3. Database
        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            $checks['database'] = true;
        } catch (\Throwable $e) {
            $errors[] = 'DB: ' . $e->getMessage();
        }
    } catch (\Throwable $e) {
        $errors[] = 'Laravel: ' . $e->getMessage();
    }
}

// 4. Storage writable
$checks['storage'] = is_writable($basePath . '/storage/logs');
if (!$checks['storage']) $errors[] = 'storage/logs not writable';

$healthy = !in_array(false, $checks, true);
http_response_code($healthy ? 200 : 500);

echo json_encode([
    'status'    => $healthy ? 'healthy' : 'unhealthy',
    'checks'    => $checks,
    'errors'    => $errors,
    'commit'    => trim(@shell_exec("cd {$basePath} && git rev-parse --short HEAD 2>/dev/null") ?: 'unknown'),
    'php'       => PHP_VERSION,
    'timestamp' => date('Y-m-d H:i:s'),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
