# Build log

## Module 1 — Core system (complete)

- Laravel 11 skeleton in `commerce/`.
- `config/commerce.php`, `config/installer.php`.
- `settings` migration + `Setting` model + `SettingsService` (cached merge with config; safe if table missing).
- `App\Support\Installation`.
- `.env.example`: MySQL default, commerce + integrations placeholders.
- `AppServiceProvider`: singletons, Bootstrap 5 paginator.

## Module 2 — Product system (complete)

- Migrations: `categories`, `products`, `product_variants`, `product_images`; `users` + `is_admin`, `phone`, `role`.
- Models: `Category`, `Product`, `ProductVariant`, `ProductImage`; `User` admin helpers.
- Services: `SlugService`, `PricingService` (retail / reseller / bulk min-qty via settings).
- Storefront: `HomeController`, `CategoryController`, `ProductController` + Blade (Bootstrap 5) + `x-product-card`.
- Admin: resource controllers for products & categories; auth `LoginController`; `EnsureUserIsAdmin` middleware.
- Routes: `/`, `/c/{slug}`, `/p/{slug}`, `/login`, `/admin/*`.
- Seeder: `admin@example.com` / `password`.

## Module 3 — Cart + checkout (complete)

- Migrations: `carts`, `cart_items`, `orders`, `order_items`.
- Models: `Cart`, `CartItem`, `Order`, `OrderItem`.
- Services: `CartService` (session `cart_id` for guests, user cart for auth, merge on login, stock-aware qty), `OrderService` (COD place + stock decrement, Razorpay pending order + finalize + stock decrement), `RazorpayService` (REST order create, HMAC signature verify).
- Controllers: `CartController`, `CheckoutController`, `PaymentController`, `OrderSuccessController`.
- Routes: `/cart`, `/cart/items` CRUD, `/checkout`, `POST /payments/razorpay/verify`, `/order/{orderNumber}/success`.
- Views: `storefront/cart`, `checkout`, `checkout-razorpay` (Razorpay Checkout.js), `order-success`; navbar cart badge + mobile toggler; PDP add-to-cart.
- `Login` event → `CartService::mergeOnLogin`; storefront layout view composer → `cartCount`.

## Pending (blueprint modules 4+)

Shipping admin, SEO, ads/feeds/CAPI, notifications, search, reviews, returns, PDFs, imports, conversion tools, installer/license/backup.
