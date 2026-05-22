<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
// don't run kernel, just boot
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $visitor = App\Models\Visitor::firstOrCreate(
        ['visitor_uuid' => 'test-uuid-1234'],
        [
            'device_type' => 'desktop',
            'browser' => 'Unknown',
            'os' => 'Unknown',
            'first_seen_at' => now(),
        ]
    );
    
    $session = App\Models\AnalyticsSession::create([
        'session_uuid' => 'test-sess-1234',
        'visitor_id' => $visitor->id,
        'device_type' => 'desktop',
        'started_at' => now(),
        'ended_at' => now(),
    ]);

    echo "Success! Visitor ID: " . $visitor->id . ", Session ID: " . $session->id;
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
