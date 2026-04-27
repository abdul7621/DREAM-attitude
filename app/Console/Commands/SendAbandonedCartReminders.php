<?php

namespace App\Console\Commands;

use App\Models\Cart;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendAbandonedCartReminders extends Command
{
    protected $signature   = 'cart:send-reminders';
    protected $description = 'Send sequence-based WhatsApp reminders for abandoned carts';

    public function __construct(private readonly NotificationService $notifier)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $sequence = config('commerce.conversion_engine.abandonment_sequence', []);
        if (empty($sequence)) {
            $this->info("No abandonment sequence configured.");
            return self::SUCCESS;
        }

        $sent = 0;
        
        // Loop through each step in the sequence
        foreach ($sequence as $stepIndex => $stepConfig) {
            $delayMinutes = (int) ($stepConfig['delay_minutes'] ?? 60);
            
            // We want carts that are exactly at this step, and have been idle for AT LEAST $delayMinutes
            $carts = Cart::query()
                ->where('abandoned_reminder_step', $stepIndex)
                ->whereNotNull('updated_at')
                ->where('updated_at', '<=', now()->subMinutes($delayMinutes))
                ->with(['items', 'user'])
                ->get();

            foreach ($carts as $cart) {
                if ($cart->items->isEmpty()) {
                    continue;
                }

                $user = $cart->user;
                $phone = $user?->phone ?: $cart->guest_phone;
                $name = $user?->name ?: 'Customer';

                if (!$phone) {
                    continue; // Cannot recover without phone
                }

                $recoveryUrl = url('/cart?recover='.$cart->id);
                $template = $stepConfig['template'] ?? "Hi {name}, your cart is waiting: {link}";
                
                $messageBody = str_replace(
                    ['{name}', '{link}'],
                    [$name, $recoveryUrl],
                    $template
                );

                try {
                    $this->notifier->sendRawWhatsApp($phone, 'abandoned_sequence_step_'.$stepIndex, $messageBody, [
                        'cart_id' => $cart->id,
                        'recovery_url' => $recoveryUrl
                    ]);
                    
                    $cart->update([
                        'abandoned_reminder_step' => $stepIndex + 1,
                        // update the timestamp so the next step's delay starts from NOW
                        'updated_at' => now(), 
                    ]);
                    $sent++;
                } catch (\Throwable $e) {
                    $this->error('Cart '.$cart->id.': '.$e->getMessage());
                }
            }
        }

        $this->info("Sent {$sent} sequence reminder(s).");

        return self::SUCCESS;
    }
}
