<?php

namespace App\Services;

use App\Models\NotificationLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Pluggable notification service — WhatsApp (2factor/Wati) + SMTP email.
 * Credentials come from Settings; falls back gracefully if not configured.
 */
class NotificationService
{
    public function __construct(private readonly SettingsService $settings) {}

    /**
     * Send order placed notification.
     */
    public function orderPlaced(array $order): void
    {
        $vars = [
            'order_number' => $order['order_number'],
            'customer_name' => $order['customer_name'],
            'grand_total'  => '₹'.number_format($order['grand_total'], 2),
        ];

        $this->sendWhatsApp(
            $order['phone'] ?? '',
            'order_placed',
            $vars
        );

        if ($order['email'] ?? null) {
            $this->sendEmail(
                $order['email'],
                'Order Confirmed — #'.$order['order_number'],
                'emails.order-placed',
                $vars
            );
        }
    }

    /**
     * Send order shipped notification.
     */
    public function orderShipped(array $order, string $awb = '', string $trackingUrl = ''): void
    {
        $vars = [
            'order_number' => $order['order_number'],
            'customer_name' => $order['customer_name'],
            'awb'          => $awb,
            'tracking_url' => $trackingUrl,
        ];

        $this->sendWhatsApp($order['phone'] ?? '', 'order_shipped', $vars);

        if ($order['email'] ?? null) {
            $this->sendEmail(
                $order['email'],
                'Your Order Has Shipped — #'.$order['order_number'],
                'emails.order-shipped',
                $vars
            );
        }
    }

    /**
     * Send abandoned cart recovery reminder.
     */
    public function abandonedCart(string $phone, string $email, string $recoveryUrl): void
    {
        $vars = ['recovery_url' => $recoveryUrl];

        $this->sendWhatsApp($phone, 'abandoned_cart', $vars);

        if ($email) {
            $this->sendEmail($email, 'You left something in your cart!', 'emails.abandoned-cart', $vars);
        }
    }

    // ─── Internal senders ─────────────────────────────────────────────────────

    private function sendWhatsApp(string $phone, string $event, array $vars): void
    {
        if (! $phone) {
            return;
        }

        $provider = $this->settings->get('notify.whatsapp_provider');  // 'wati' | '2factor' | null
        $token    = $this->settings->get('notify.whatsapp_token');
        $template = $this->settings->get("notify.wa_template_{$event}");

        $status = 'skipped';
        $error  = null;

        if ($provider && $token && $template) {
            try {
                $message = $this->interpolate($template, $vars);

                if ($provider === '2factor') {
                    // 2factor.in WhatsApp send
                    $url = "https://2factor.in/API/V1/{$token}/WHATSAPP/SEND/{$phone}/{$template}/";
                    \Illuminate\Support\Facades\Http::post($url, $vars);
                } elseif ($provider === 'wati') {
                    $instanceUrl = rtrim((string) $this->settings->get('notify.wati_url'), '/');
                    \Illuminate\Support\Facades\Http::withHeaders(['Authorization' => "Bearer {$token}"])
                        ->post("{$instanceUrl}/api/v1/sendTemplateMessage?whatsappNumber={$phone}", [
                            'template_name' => $template,
                            'broadcast_name' => 'order_notify',
                            'parameters'     => collect($vars)->map(fn ($v, $k) => ['name' => $k, 'value' => $v])->values(),
                        ]);
                }

                $status = 'sent';
            } catch (\Throwable $e) {
                $status = 'failed';
                $error  = $e->getMessage();
                Log::warning("WhatsApp notify failed ({$event}): ".$e->getMessage());
            }
        }

        NotificationLog::query()->create([
            'channel'    => 'whatsapp',
            'event'      => $event,
            'to_address' => $phone,
            'payload'    => $vars,
            'status'     => $status,
            'error'      => $error,
        ]);
    }

    private function sendEmail(string $to, string $subject, string $view, array $vars): void
    {
        $status = 'skipped';
        $error  = null;

        try {
            Mail::send($view, $vars, function ($m) use ($to, $subject): void {
                $m->to($to)->subject($subject);
            });
            $status = 'sent';
        } catch (\Throwable $e) {
            $status = 'failed';
            $error  = $e->getMessage();
            Log::warning("Email notify failed: ".$e->getMessage());
        }

        NotificationLog::query()->create([
            'channel'    => 'email',
            'event'      => $subject,
            'to_address' => $to,
            'payload'    => $vars,
            'status'     => $status,
            'error'      => $error,
        ]);
    }

    private function interpolate(string $template, array $vars): string
    {
        foreach ($vars as $k => $v) {
            $template = str_replace('{{'.$k.'}}', $v, $template);
        }

        return $template;
    }
}
