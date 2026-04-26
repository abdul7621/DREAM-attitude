<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AnalyticsEvent;
use App\Models\AnalyticsSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PruneAnalyticsEvents extends Command
{
    protected $signature = 'decision-engine:prune {--days=90 : Number of days to keep raw logs}';
    protected $description = 'Prune old raw analytics events to prevent database explosion';

    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoff = Carbon::now()->subDays($days);
        
        $this->info("Pruning Decision Engine events older than {$cutoff->toDateString()}...");

        try {
            // 1. Delete old raw events
            $eventCount = AnalyticsEvent::where('created_at', '<', $cutoff)->delete();
            $this->info("Deleted {$eventCount} old events.");

            // 2. Delete old sessions that did NOT result in a purchase
            // Keep purchased sessions forever for LTV and historical attribution
            $sessionCount = AnalyticsSession::where('started_at', '<', $cutoff)
                ->where('reached_purchase', false)
                ->delete();
            $this->info("Deleted {$sessionCount} old unpurchased sessions.");

            Log::info("Decision Engine Pruning Complete. Events deleted: {$eventCount}, Sessions deleted: {$sessionCount}");

        } catch (\Exception $e) {
            $this->error('Pruning failed: ' . $e->getMessage());
            Log::error('Decision Engine Pruning failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
