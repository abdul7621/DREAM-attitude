<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'order_confirmation',
                'channel' => 'email',
                'subject' => 'Order Confirmation - {order_number}',
                'body' => "Hi {customer_name},\n\nThank you for your order {order_number}!\n\nYour order is confirmed and we will notify you once it ships.\n\nTotal: {grand_total}\n\nThanks,\nThe Team",
                'variables_guide' => [
                    'customer_name' => 'Name of the customer',
                    'order_number' => 'Unique order ID',
                    'grand_total' => 'Total order amount including currency',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'order_shipped_whatsapp',
                'channel' => 'whatsapp',
                'subject' => null,
                'body' => "Hi {customer_name}, great news! 🚀\nYour order {order_number} has been shipped via {carrier}.\n\nTrack your package here: {tracking_url}\n\nThank you for shopping with us!",
                'variables_guide' => [
                    'customer_name' => 'Name of the customer',
                    'order_number' => 'Unique order ID',
                    'carrier' => 'Shipping Carrier (e.g. BlueDart)',
                    'tracking_url' => 'Link to track the shipment',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'abandoned_cart_reminder',
                'channel' => 'email',
                'subject' => 'Did you forget something, {customer_name}?',
                'body' => "Hi {customer_name},\n\nWe noticed you left some great items in your cart. They're waiting for you!\n\nClick here to complete your purchase: {checkout_link}\n\nUse code RETURN10 for 10% off your order.",
                'variables_guide' => [
                    'customer_name' => 'Name of the customer',
                    'checkout_link' => 'Link to return to checkout',
                ],
                'is_active' => true,
            ]
        ];

        foreach ($templates as $template) {
            NotificationTemplate::firstOrCreate(
                ['name' => $template['name'], 'channel' => $template['channel']],
                $template
            );
        }
    }
}
