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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Event::listen(Login::class, function (Login $event): void {
            app(CartService::class)->mergeOnLogin($event->user);
        });

        Event::listen(OrderPlaced::class, SendOrderPlacedNotification::class);
        Event::listen(OrderShipped::class, SendOrderShippedNotification::class);

        View::composer('layouts.storefront', function ($view): void {
            $view->with('cartCount', app(CartService::class)->count());
        });
    }
}
