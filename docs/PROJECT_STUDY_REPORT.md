# Full Project Study Report - Ikhlas D2C Commerce

Report date: 2026-04-11  
Project path: `commerce/`  
Primary goal: White-label, single-store D2C commerce system for Indian sellers.

## 1. Executive Summary

This project is a Laravel 11 based white-label e-commerce application designed for one brand/store per installation. It is not a hosted SaaS or shared multi-tenant platform. The codebase targets Indian D2C use cases: COD-heavy checkout, online payment gateways, coupons, shipping rules, GST-style PDFs, marketing attribution, feeds, admin reporting, reviews, wishlist, returns, and import tools.

Overall status: the project is more than a basic MVP. It contains a working storefront and a broad admin system, with many production-facing modules already implemented. The main remaining work is hardening: fixing a few code-level bugs, completing notification/payment edge cases, improving test coverage, and doing a full smoke test with real database, storage, queue, mail, and payment credentials.

## 2. Business And Product Direction

The included blueprint defines this as a licensed, non-SaaS, white-label commerce product. Each client should get:

- Own hosting
- Own domain
- Own MySQL database
- Own credentials for payments, analytics, mail, WhatsApp, and carriers
- No shared merchant database
- No required central vendor server

This direction is consistent with the current codebase. Configuration is handled through `.env`, `config/commerce.php`, and database-backed settings. The architecture does not show multi-tenant row-level merchant separation, which is correct for the stated product model.

## 3. Technology Stack

- Backend: Laravel 11
- PHP requirement: PHP 8.2+
- Database: MySQL recommended, SQLite possible locally
- Frontend rendering: Blade templates
- CSS/JS: Bootstrap assets, Tailwind/Vite scaffolding, custom `public/css/storefront.css`, custom `public/js/store.js`
- PDF: `barryvdh/laravel-dompdf`
- Queue/cache/session: database drivers configured in `.env.example`
- Payments: dynamic payment method table plus gateway drivers
- Deployment target: shared hosting or normal Laravel hosting with document root pointed to `public/`

Important package files:

- `composer.json`
- `package.json`
- `config/commerce.php`
- `.env.example`

## 4. Repository Structure

Main app lives in `commerce/`.

Important directories:

- `app/Http/Controllers/Storefront` - public store, account, cart, checkout, search, feeds
- `app/Http/Controllers/Admin` - admin dashboard, catalog, orders, settings, reports, imports, returns, reviews
- `app/Models` - 30 domain models
- `app/Services` - cart, order, pricing, shipping, notification, import, payment manager
- `app/Services/Payment` - Razorpay, PhonePe, Cashfree, Instamojo, PayU drivers
- `database/migrations` - 21 migrations
- `resources/views` - storefront, admin, account, PDF, email, components
- `routes/web.php` - all public, account, and admin routes
- `routes/console.php` - scheduled commands

Codebase size indicators from inspection:

- Admin controllers: 21
- Storefront controllers: 17
- Models: 30
- Payment drivers: 5
- Console commands: 4
- Migrations: 21
- Product page section views: 12

## 5. Main User Flows

### Storefront Flow

Customer can browse home, categories, product pages, search, cart, checkout, and order success.

Routes include:

- `/`
- `/c/{slug}`
- `/p/{slug}`
- `/search`
- `/api/search/suggest`
- `/cart`
- `/checkout`
- `/payments/verify/{gateway}`
- `/order/{orderNumber}/success`
- `/pages/{slug}`
- `/feed/google.xml`
- `/feed/facebook.xml`
- `/sitemap.xml`
- `/robots.txt`

Storefront features found:

- Featured/latest/bestseller products
- Categories
- Product variants
- Product images
- Retail/reseller/bulk pricing
- Reviews
- Related products
- Frequently bought products
- Recently viewed tracking
- Wishlist
- Cart coupons
- Shipping quote by rule
- Guest-to-customer identity creation at checkout
- COD and online payment flow
- Marketing attribution capture
- SEO feeds and sitemap

### Admin Flow

Admin area is under `/admin` and protected by auth plus admin middleware.

Admin modules found:

- Dashboard KPIs
- Orders list/detail/update/bulk update/export
- Invoice and packing slip PDF
- Products CRUD
- Categories CRUD
- Coupons CRUD
- Shipping rules CRUD
- Returns management
- Reviews moderation
- Customers list/detail
- Sales/product/customer/coupon/inventory reports
- Audit logs
- General settings
- Payment settings
- Theme settings
- Notification templates
- Menus/menu items
- Redirects
- CMS pages
- Import wizard
- Notification logs

This is a strong admin footprint for a white-label commerce product.

## 6. Database And Domain Model Study

Core domain tables:

- `users`
- `settings`
- `categories`
- `products`
- `product_variants`
- `product_images`
- `carts`
- `cart_items`
- `orders`
- `order_items`
- `coupons`
- `shipping_rules`
- `shipments`
- `redirects`
- `pages`
- `reviews`
- `return_requests`
- `wishlists`
- `recently_viewed`
- `import_jobs`
- `store_credit_balances`
- `store_credit_ledger`
- `notification_logs`
- `audit_logs`
- `notification_templates`
- `menus`
- `menu_items`
- `payment_methods`
- `order_status_logs`
- `addresses`

The data model supports a real commerce lifecycle: catalog, customer, cart, order, payment, shipment, returns, store credit, review, notification, and admin auditing.

Key model relationships:

- `Product` belongs to `Category`
- `Product` has many `ProductVariant`
- `Product` has many `ProductImage`
- `Cart` has many `CartItem`
- `CartItem` belongs to `ProductVariant`
- `Order` has many `OrderItem`
- `Order` has many `Shipment`
- `Order` has many `ReturnRequest`
- `Order` has many `OrderStatusLog`
- `User` has many `Order`, `Address`, `Wishlist`
- `User` has one `StoreCreditBalance`

## 7. Catalog And Pricing

Catalog implementation is solid for MVP and early production:

- Products have status: draft, active, archived
- Categories can be nested by `parent_id`
- Variants support SKU, barcode/options, price tiers, compare-at price, inventory, weight
- Product image ordering and primary image support exist
- Home page caches featured, bestseller, latest, categories, and reviews
- Slug generation service exists

Pricing supports:

- Retail price
- Reseller price for users with `role = reseller`
- Bulk price when quantity is above configured threshold
- Compare-at display price

Potential improvement:

- Add automated tests for price tier resolution and cart total behavior.
- Add admin UX guardrails for invalid variant combinations and missing default variant.

## 8. Cart And Checkout

Cart implementation includes:

- Session cart for guests
- User cart for logged-in customers
- Merge guest cart on login
- Inventory-aware quantity limits
- Coupon apply/remove
- Totals calculation
- Shipping calculation by pincode/weight/subtotal

Checkout implementation includes:

- Indian phone validation
- Customer creation/login by phone/email
- COD order creation
- Pending online order creation
- Online payment verification callback
- Cart clearing after successful payment
- Marketing attribution copied from session to order

Important note: online orders reserve stock before payment by decrementing inventory. The scheduled `orders:release-expired` command restores stock for pending online orders older than 30 minutes. This is a valid approach, but payment-failure and retry flows need careful testing.

## 9. Payments

Payment system is more advanced than a single Razorpay integration. It has:

- `PaymentGatewayInterface`
- `PaymentManager`
- `payment_methods` table
- Admin payment settings
- Razorpay driver
- PhonePe driver
- Cashfree driver
- Instamojo driver
- PayU driver
- COD as a payment method row

Gateway flow:

1. Checkout validates that selected `payment_method` is active.
2. COD creates order directly.
3. Online methods create a pending order.
4. Driver creates gateway order/session.
5. Customer is sent to gateway or checkout widget.
6. Gateway callback is verified.
7. Order is finalized and marked paid.

Payment maturity:

- Razorpay is the most complete browser checkout integration.
- PayU has form redirect handling.
- PhonePe/Cashfree/Instamojo redirect-based flows are present.
- Refund methods exist in drivers but need deeper end-to-end validation.

## 10. Shipping And Fulfillment

Shipping support includes:

- `shipping_rules`
- `ShippingService`
- Rule types: flat, weight bands, pincode prefixes
- `shipments` table
- Manual shipment creation/status
- AWB and tracking URL support
- Admin shipment updates during order status change

Carrier credentials exist for Shiprocket and Delhivery in config, but full carrier API create-label/tracking webhook workflow is not implemented yet.

## 11. Orders, Statuses, Returns, And Store Credit

Order lifecycle constants include:

- `awaiting_payment`
- `placed`
- `confirmed`
- `packed`
- `shipped`
- `delivered`
- `cancelled`
- `refunded`
- `abandoned_checkout`

Status transition rules are defined in the `Order` model.

Admin can:

- View orders
- Filter/search orders
- Change status
- Add shipment data
- Bulk confirm/pack
- Export CSV
- Download invoice/packing PDFs

Returns:

- Customer return request route exists
- Admin returns index/show/update exists
- Return request model/table exists

Store credit:

- Balance and ledger models/tables exist
- Store credit appears data-ready but not fully surfaced as a complete refund/payment workflow.

## 12. Marketing, SEO, Analytics, And Growth

Implemented pieces:

- UTM, `gclid`, `fbclid` capture middleware
- Order attribution persistence
- Google product feed
- Facebook product feed
- Sitemap
- Robots endpoint from settings
- Redirect middleware
- Product meta fields
- Product JSON-LD / analytics hooks visible in layout/product views
- Meta Pixel / GA4 / GTM-style hooks in layout and partials
- Meta Conversions API service exists

This area aligns well with the D2C blueprint because ad tracking and feeds are core for Indian performance marketing stores.

Risk:

- Meta CAPI service binding has a class-name mismatch in the service provider. See Critical Risks section.

## 13. Notifications And Retention

Notification system includes:

- Notification templates
- Notification logs
- Email sending by SMTP
- WhatsApp placeholder sender logic
- Order placed listener
- Order shipped listener
- Abandoned cart reminder command
- Review request command
- Replenishment reminder command

Scheduled commands:

- `cart:send-reminders --hours=2` hourly
- `orders:release-expired` every five minutes

Commands also exist for review and replenishment reminders, but they are not scheduled in `routes/console.php` at the time of this study.

Notification maturity:

- Email is closer to usable.
- WhatsApp is not a real provider integration yet; it logs messages when settings exist and contains placeholder branches for providers.
- Template coverage is incomplete for review request and replenishment reminder unless admins create templates manually.

## 14. Import And Migration Tools

Import wizard supports:

- Upload source: Shopify or Woo
- Upload type: products, customers, orders
- CSV/TXT upload
- Import job record
- Preview/dry run
- Confirm import

Actual importer implementation is currently focused on products:

- `ShopifyImporter`
- `WooImporter`
- `ImageFetchPipeline`

Customers/orders import are accepted by upload validation but not implemented in preview/confirm flow. That is okay if marked as future scope, but UI should clearly communicate it.

## 15. Theme, CMS, Menus, And Store Modes

Theme/admin support includes:

- Primary/secondary colors
- Font family
- Logo/favicon
- Border radius
- Button style
- Card shadow
- Homepage sections
- Hero content/image
- Announcement bar
- Offers banner

CMS support:

- Pages CRUD
- Storefront page route
- Redirects CRUD and middleware
- Menu and menu items CRUD

Store lifecycle:

- Live
- Coming soon
- Maintenance
- Admin preview bypass

Issue found:

- Storefront layout references `$globalMenus`, but no view composer or provider code was found that populates it. Header/footer menus may not render even when menus exist.

## 16. Admin Reporting And Analytics

Admin reporting is strong for this stage.

Dashboard includes:

- Today's orders
- Today's revenue
- Total revenue
- AOV
- Pending orders
- Low stock count
- High-risk COD orders
- Pending reviews
- Pending returns
- Revenue chart
- COD vs prepaid chart
- Top products
- Recent orders
- Recent reviews

Report pages include:

- Sales report
- Product report
- Customer report
- Coupon report
- Inventory report

These reports are useful for the target seller profile and match the blueprint's "revenue ops admin" direction.

## 17. Security And Operational Notes

Good signs:

- Admin routes require auth and admin middleware
- Status update transitions are constrained
- Payment verification exists per driver
- Password update checks current password
- Redirect middleware only handles GET
- Settings are centralized
- Audit logs exist
- Storage and install writability support exists

Needs attention:

- Seeded admin credentials are `admin@example.com` / `password`; safe only for local setup and must be changed in production.
- Some user-facing and code comments show encoding corruption, especially rupee symbols and box-drawing comments. This does not always break logic, but it hurts polish and can break copied text.
- Need a production installation checklist for storage link, queue worker/scheduler, cron, cache, and backups.
- Need full role/permission model if non-admin staff accounts are expected.
- Need rate limiting for login, reviews, checkout, and search suggestion endpoints.

## 18. Testing And Verification Status

The repository contains only default example tests:

- `tests/Feature/ExampleTest.php`
- `tests/Unit/ExampleTest.php`

No domain test suite was found for:

- Cart merging
- Pricing tiers
- Coupon validation
- Shipping rules
- Order creation
- Payment verification
- Stock reservation/release
- Admin status transitions
- Import dry run/import
- Notifications

Because dependencies/vendor files were not present in the inspected working tree, a full `php artisan test` run was not available during this study without installing dependencies.

Recommended minimum test suite:

- CartService test
- PricingService test
- CouponService test
- ShippingService test
- OrderService COD test
- OrderService online pending/finalize test
- ReleaseExpiredReservations command test
- PaymentManager driver selection test
- Admin order status transition feature test
- Import dry-run feature test

## 19. Critical Risks / Bugs Found

These should be fixed before production smoke testing.

### P1 - Wrong Meta CAPI Service Class Binding

File: `app/Providers/AppServiceProvider.php`  
Lines found: 12 and 43

The provider imports and registers `MetaConversationsApiService`, but the actual file/class is `MetaConversionsApiService`.

Impact:

- The intended Meta Conversions API service may not be registered correctly.
- Any code relying on container binding for the correct service may fail or miss singleton behavior.

Fix direction:

- Replace `MetaConversationsApiService` with `MetaConversionsApiService`.

### P1 - Product Sales Count Listener Uses Wrong Relationship

File: `app/Listeners/UpdateProductSalesCount.php`  
Line found: 13

The listener loops over `$event->order->items`, but the `Order` model defines `orderItems()` and does not define `items()`.

Impact:

- When `OrderPlaced` is dispatched, bestseller/sales count update can fail.
- This may also break payment finalization if listener exception bubbles.

Fix direction:

- Use `$event->order->orderItems` or define a proper `items()` relationship alias.

### P1 - COD Orders Do Not Dispatch `OrderPlaced`

File: `app/Services/OrderService.php`  
COD order creation starts at line 26; `OrderPlaced::dispatch($order)` appears only around line 190 in online payment finalization.

Impact:

- COD orders may not trigger order confirmation notifications.
- COD product sales count/cache invalidation may not happen through listeners.
- Dashboard/home bestsellers can be stale for COD-heavy stores.

Fix direction:

- Dispatch `OrderPlaced` after COD transaction commits.
- Prefer `DB::afterCommit()` to avoid notifying before transaction success.

### P1 - Shipped Notification Event Is Not Fired

File: `app/Http/Controllers/Admin/OrderController.php`  
Shipping status branch appears around lines 82-88; only `OrderStatusChanged` is fired around line 97.

Impact:

- `OrderShipped` listener exists but is not triggered by admin order update.
- Shipping WhatsApp/email notifications may never send.

Fix direction:

- Dispatch `OrderShipped` when status changes to `shipped`, passing AWB/tracking URL.

### P2 - Product Layout Config Can TypeError

Files:

- `app/Models/Product.php`
- `app/Http/Controllers/Storefront/ProductController.php`

`Product` casts `layout_config` to JSON/array. Product page then calls `json_decode($product->layout_config ?? ...)`.

Impact:

- If a product has custom layout saved as JSON-cast array, PHP can throw a type error because `json_decode()` expects a string.

Fix direction:

- Normalize layout config by checking `is_array()` first, and only decoding strings.

### P2 - Payment Failure Does Not Immediately Release Reserved Stock

Files:

- `app/Services/OrderService.php`
- `app/Http/Controllers/Storefront/PaymentController.php`
- `app/Console/Commands/ReleaseExpiredReservations.php`

Online checkout decrements stock before payment. Expired pending orders are released after 30 minutes. But direct verification failure marks the order as failed/abandoned immediately.

Impact:

- Failed payments may keep inventory reduced unless another release path handles them.
- This can cause stock mismatch in high-volume stores.

Fix direction:

- Add a single stock-release method for failed/expired online orders and call it from both payment failure and reservation expiry.

### P2 - Menu Builder Data Is Not Injected Into Storefront Layout

File: `resources/views/layouts/storefront.blade.php`  
Lines found: 120, 122, 244, 245

Layout checks `$globalMenus`, but no provider/view composer was found to load it.

Impact:

- Admin-created menus may not appear on storefront.

Fix direction:

- Add a storefront layout view composer that loads active `header` and `footer` menus with parent/children items.

### P3 - Encoding Corruption In Text

Multiple files show corrupted rupee symbols and decorative comments.

Impact:

- UI can show broken text like `â‚¹`.
- Comments/docs become harder to maintain.

Fix direction:

- Standardize files to UTF-8 and replace corrupted sequences in views, seeders, and docs.

## 20. Production Readiness Score

Approximate current score: 70/100.

Reasoning:

- Feature breadth: high
- Admin coverage: high
- India D2C alignment: high
- Payment breadth: medium-high
- Test coverage: low
- Production hardening: medium
- Notification/carrier completion: medium-low
- Known critical bugs: present but fixable

With the P1/P2 issues fixed and smoke tests completed, this can become a strong sellable MVP.

## 21. Recommended Next Work Plan

### Phase 1 - Fix Critical Bugs

1. Fix Meta CAPI service class name.
2. Fix product sales count relationship.
3. Dispatch `OrderPlaced` for COD.
4. Dispatch `OrderShipped` for shipped status.
5. Normalize product layout config decoding.
6. Release reserved stock on payment failure.
7. Add `$globalMenus` view composer.

### Phase 2 - Verification

1. Install dependencies.
2. Run migrations on local DB.
3. Seed admin user and payment methods.
4. Run `php artisan route:list`.
5. Run `php artisan test`.
6. Smoke test:
   - Create category/product/variant/image
   - Add to cart
   - Apply coupon
   - Checkout COD
   - Checkout online test gateway
   - Update order status
   - Download invoice/packing slip
   - Submit/moderate review
   - Create return request
   - Test search and feeds

### Phase 3 - Sellable Product Polish

1. Add setup/install checklist.
2. Add production admin onboarding screen.
3. Add backup/export guide.
4. Add client handover document.
5. Improve WhatsApp provider integration.
6. Complete Shiprocket/Delhivery integration or clearly label manual shipping.
7. Add import support status labels for products/customers/orders.

## 22. Final Conclusion

This is a serious white-label D2C commerce codebase, not just a starter template. It already covers storefront, admin, payments, reporting, marketing attribution, SEO feeds, PDFs, retention commands, reviews, wishlist, returns, theme settings, and import tooling.

The strongest parts are the breadth of commerce modules and the India-first product direction. The weakest parts are automated tests, a few broken event/service wiring points, and incomplete real-world integrations for WhatsApp/carriers/import types beyond products.

Best next move: fix the P1/P2 issues first, then run a full checkout/admin smoke test. After that, prepare deployment and client-handover documentation so the product can be repeatedly sold and installed.
