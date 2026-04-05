<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ImportController as AdminImportController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\RedirectController as AdminRedirectController;
use App\Http\Controllers\Admin\ReturnRequestController as AdminReturnRequestController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\ShippingRuleController as AdminShippingRuleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Storefront\AccountController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CategoryController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\FeedController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\OrderSuccessController;
use App\Http\Controllers\Storefront\PageController;
use App\Http\Controllers\Storefront\PaymentController;
use App\Http\Controllers\Storefront\ProductController;
use App\Http\Controllers\Storefront\ReturnRequestController;
use App\Http\Controllers\Storefront\ReviewController;
use App\Http\Controllers\Storefront\RobotsController;
use App\Http\Controllers\Storefront\SearchController;
use App\Http\Controllers\Storefront\SitemapController;
use Illuminate\Support\Facades\Route;

// ── Public Storefront ──────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [RobotsController::class, 'index'])->name('robots');

Route::get('/feed/google.xml', [FeedController::class, 'google'])->name('feed.google');
Route::get('/feed/facebook.xml', [FeedController::class, 'facebook'])->name('feed.facebook');

Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/api/search/suggest', [SearchController::class, 'suggest'])->name('search.suggest');

Route::get('/pages/{page:slug}', [PageController::class, 'show'])->name('page.show');

Route::get('/c/{category:slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/p/{product:slug}', [ProductController::class, 'show'])->name('product.show');

// Reviews (submit - auth not strictly required; visible to public)
Route::post('/p/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

// ── Cart ───────────────────────────────────────────────────────────────────
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
Route::put('/cart/items/{item}', [CartController::class, 'update'])->name('cart.items.update');
Route::delete('/cart/items/{item}', [CartController::class, 'destroy'])->name('cart.items.destroy');
Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
Route::delete('/cart/coupon', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

// ── Checkout ───────────────────────────────────────────────────────────────
Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

Route::post('/payments/razorpay/verify', [PaymentController::class, 'razorpayVerify'])->name('payments.razorpay.verify');

Route::get('/order/{orderNumber}/success', [OrderSuccessController::class, 'show'])->name('order.success');

// ── Auth ───────────────────────────────────────────────────────────────────
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// ── Customer Account (auth required) ──────────────────────────────────────
Route::middleware('auth')->prefix('account')->name('account.')->group(function (): void {
    Route::get('orders', [AccountController::class, 'orders'])->name('orders');
    Route::get('orders/{order}', [AccountController::class, 'orderShow'])->name('orders.show');
    Route::post('orders/{order}/return', [ReturnRequestController::class, 'store'])->name('orders.return.store');
    Route::get('returns', [ReturnRequestController::class, 'index'])->name('returns');
});

// ── Admin Panel ────────────────────────────────────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Orders
    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');
    Route::get('orders/{order}/invoice', [AdminOrderController::class, 'invoicePdf'])->name('orders.invoice');
    Route::get('orders/{order}/packing', [AdminOrderController::class, 'packingPdf'])->name('orders.packing');

    // Catalog
    Route::resource('products', AdminProductController::class)->except(['show']);
    Route::resource('categories', AdminCategoryController::class)->except(['show']);

    // Coupons
    Route::resource('coupons', AdminCouponController::class)->except(['show']);

    // Shipping Rules
    Route::resource('shipping-rules', AdminShippingRuleController::class)->except(['show']);

    // Returns
    Route::get('returns', [AdminReturnRequestController::class, 'index'])->name('returns.index');
    Route::get('returns/{returnRequest}', [AdminReturnRequestController::class, 'show'])->name('returns.show');
    Route::patch('returns/{returnRequest}', [AdminReturnRequestController::class, 'update'])->name('returns.update');

    // Reviews
    Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::patch('reviews/{review}', [AdminReviewController::class, 'update'])->name('reviews.update');
    Route::delete('reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

    // Customers
    Route::get('customers', [AdminCustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/{user}', [AdminCustomerController::class, 'show'])->name('customers.show');

    // Settings
    Route::get('settings', [AdminSettingController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [AdminSettingController::class, 'update'])->name('settings.update');

    // Redirects
    Route::resource('redirects', AdminRedirectController::class)->except(['show']);

    // CMS Pages
    Route::resource('pages', AdminPageController::class)->except(['show']);

    // Import Wizard
    Route::get('import', [AdminImportController::class, 'index'])->name('import.index');
    Route::post('import/upload', [AdminImportController::class, 'upload'])->name('import.upload');
    Route::post('import/{importJob}/preview', [AdminImportController::class, 'preview'])->name('import.preview');
    Route::post('import/{importJob}/confirm', [AdminImportController::class, 'confirm'])->name('import.confirm');
});
