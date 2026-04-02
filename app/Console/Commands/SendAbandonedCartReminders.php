<?php

namespace App\Console\Commands;

use App\Models\Cart;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendAbandonedCartReminders extends Command
{
    protected $signature   = 'cart:send-reminders {--hours=2 : Hours idle before cart is considered abandoned}';
    protected $description = 'Send WhatsApp/email reminders for abandoned carts';

    public function __construct(private readonly NotificationService $notifier)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $hours = (int) $this->option('hours');

        $carts = Cart::query()
            ->whereNull('abandoned_reminder_sent_at')
            ->whereNotNull('last_activity_at')
            ->where('last_activity_at', '<=', now()->subHours($hours))
            ->with(['items', 'user'])
            ->get();

        $sent = 0;

        foreach ($carts as $cart) {
            // Skip empty carts
            if ($cart->items->isEmpty()) {
                continue;
            }

            $user  = $cart->user;
            $phone = $user?->phone ?? '';
            $email = $user?->email ?? '';

            if (! $phone && ! $email) {
                continue;
            }

            // Generate a signed recovery URL using the cart id
            $recoveryUrl = url('/cart?recover='.$cart->id);

            try {
                $this->notifier->abandonedCart($phone, (string) $email, $recoveryUrl);
                $cart->update(['abandoned_reminder_sent_at' => now()]);
                $sent++;
            } catch (\Throwable $e) {
                $this->error('Cart '.$cart->id.': '.$e->getMessage());
            }
        }

        $this->info("Sent {$sent} abandoned cart reminder(s).");

        return self::SUCCESS;
    }
}
