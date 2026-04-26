<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Abandoned cart reminders — runs every hour on shared hosting
Schedule::command('cart:send-reminders --hours=2')->hourly();

// Release reserved stock for abandoned online payment checkouts
Schedule::command('orders:release-expired')->everyFiveMinutes();

// Decision Engine daily aggregation
Schedule::command('decision-engine:aggregate')->dailyAt('01:00');
