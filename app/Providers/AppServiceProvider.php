<?php

namespace App\Providers;

use App\Events\OrderPlaced;
use App\Events\OrderShipped;
use App\Listeners\SendOrderPlacedNotification;
use App\Listeners\SendOrderShippedNotification;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\ImageFetchPipeline;
use App\Services\MetaConversationsApiService;
use App\Services\NotificationService;
use App\Services\OrderService;
use App\Services\PricingService;
use App\Services\RazorpayService;
use App\Services\SettingsService;
use App\Services\ShippingService;
use App\Services\ShopifyImporter;
use App\Services\SlugService;
use App\Services\WooImporter;
use Illuminate\Auth\Events\Login;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
        $this->app->singleton(SlugService::class);
        $this->app->singleton(PricingService::class);
        $this->app->singleton(CartService::class);
        $this->app->singleton(CouponService::class);
        $this->app->singleton(ShippingService::class);
        $this->app->singleton(RazorpayService::class);
        $this->app->singleton(OrderService::class);
        $this->app->singleton(MetaConversationsApiService::class);
        $this->app->singleton(NotificationService::class);
        $this->app->singleton(ImageFetchPipeline::class);
        $this->app->singleton(ShopifyImporter::class);
        $this->app->singleton(WooImporter::class);
        
        $this->app->singleton(\App\Services\PaymentManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Event::listen(\Illuminate\Auth\Events\Login::class, function (\Illuminate\Auth\Events\Login $event): void {
            app(CartService::class)->mergeOnLogin($event->user);
            
            \App\Models\AuditLog::log(
                'login_success',
                $event->user,
                [],
                [
                    'user_agent' => request()->userAgent(),
                    'email' => $event->user->email
                ]
            );
        });

        Event::listen(\Illuminate\Auth\Events\Failed::class, function (\Illuminate\Auth\Events\Failed $event): void {
            \App\Models\AuditLog::log(
                'login_failed',
                $event->user, // Might be null, supported by signature
                [],
                [
                    'user_agent' => request()->userAgent(),
                    'email' => $event->credentials['email'] ?? 'Unknown'
                ]
            );
        });

        Event::listen(OrderPlaced::class, SendOrderPlacedNotification::class);
        Event::listen(OrderPlaced::class, \App\Listeners\UpdateProductSalesCount::class);
        
        Event::listen(OrderShipped::class, SendOrderShippedNotification::class);
        
        Event::listen(\App\Events\OrderStatusChanged::class, \App\Listeners\LogOrderAudit::class);
        Event::listen(\App\Events\OrderStatusChanged::class, \App\Listeners\CreateStatusLogEntry::class);


        View::composer('layouts.storefront', function ($view): void {
            $cartService = app(CartService::class);
            $settingsService = app(SettingsService::class);
            
            $cartCount = $cartService->count();
            $view->with('cartCount', $cartCount);
            
            $view->with('cartSummary', [
                'count' => $cartCount,
                'total' => $cartService->subtotalFormatted(),
            ]);
            
            $view->with('storeSettings', [
                'codEnabled' => (bool)$settingsService->get('payment.cod_enabled', true),
                'currency' => config('commerce.currency', 'INR'),
                'deliveryEta' => $settingsService->get('store.delivery_eta', '2-5 Business Days'),
                'store_name' => $settingsService->get('store.name', config('app.name')),
                'meta_description' => $settingsService->get('store.meta_description', ''),
            ]);

            // Pass SettingsService to layout for announcement bar, etc.
            $view->with('ss', $settingsService);
        });
        
        View::composer('layouts.admin', function ($view): void {
            $view->with('badgeCountPendingOrders', \App\Models\Order::where('order_status', \App\Models\Order::ORDER_STATUS_PLACED)->count());
            $view->with('badgeCountLowStock', \App\Models\ProductVariant::where('track_inventory', true)->where('stock_qty', '<=', 5)->where('is_active', true)->count());
            $view->with('badgeCountPendingReturns', \App\Models\ReturnRequest::where('status', 'requested')->count());
            $view->with('badgeCountPendingReviews', \App\Models\Review::where('is_approved', false)->count());
        });
    }
}
