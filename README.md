# Ikhlas D2C Commerce (Laravel 11)

Single-store ecommerce for the Indian market: catalog, session cart, checkout, COD + Razorpay, coupons, rule-based shipping, marketing attribution (UTM / gclid / fbclid), Meta Pixel + GTM + GA4 (config), Meta Conversions API (Purchase), Google and Facebook product feeds, XML sitemap, robots.txt (editable via settings), URL redirects, search with suggestions, PDF invoice and packing slip (admin), and admin order list.

## Requirements

- PHP 8.2+
- Composer 2
- MySQL 8 (recommended for production) or SQLite (local)
- Node 18+ (only if you rebuild frontend assets)

## Installation (local)

```bash
cd commerce
composer install
cp .env.example .env
php artisan key:generate
```

Create the database, set `DB_*` in `.env`, then:

```bash
php artisan migrate
php artisan storage:link
```

Create an admin user (Tinker or a seeder): users must have `is_admin = 1` to access `/admin`.

```bash
php artisan serve
```

Visit `/` for the storefront and `/login` for admin access.

## Shared hosting deployment

1. Upload the project (or deploy via Git).
2. Point the domain **document root** to the `public/` directory (not the project root).
3. Ensure `storage/` and `bootstrap/cache/` are writable by the web server.
4. Set `APP_URL` to your HTTPS URL in `.env`.
5. Run `composer install --no-dev --optimize-autoloader` on the server (SSH), then `php artisan migrate --force`, `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`.
6. If the host does not expose SSH, use a local build and upload `vendor/`, run migrations via a one-off route (not included) or host-provided migration tool.

## Configuration (`.env` / `config/commerce.php`)

| Purpose | Variables |
|--------|------------|
| Store | `COMMERCE_STORE_NAME`, `COMMERCE_CURRENCY`, `COMMERCE_TIMEZONE`, `COMMERCE_GSTIN` (GST invoice header) |
| Razorpay | `RAZORPAY_KEY`, `RAZORPAY_SECRET` |
| Meta Pixel + CAPI | `META_PIXEL_ID`, `META_CAPI_ACCESS_TOKEN`, optional `META_CAPI_TEST_EVENT_CODE` |
| Google Analytics 4 | `GA4_MEASUREMENT_ID` (used when GTM is not set) |
| Google Tag Manager | `GTM_CONTAINER_ID` (if set, GTM loads first; GA4 direct snippet is skipped) |
| WhatsApp Cloud API | `WHATSAPP_BUSINESS_NUMBER`, `WHATSAPP_CLOUD_API_TOKEN`, `WHATSAPP_PHONE_NUMBER_ID` |
| Shiprocket / Delhivery | `SHIPROCKET_*`, `DELHIVERY_*` (for future carrier integrations) |

Database-backed settings (via `settings` table / `SettingsService`) override `config/commerce.php` keys where implemented.

## Important URLs

| URL | Description |
|-----|-------------|
| `/feed/google.xml` | Google Merchant–style RSS feed |
| `/feed/facebook.xml` | Facebook catalog–style RSS feed |
| `/sitemap.xml` | Sitemap |
| `/robots.txt` | Robots; body from settings key `seo.robots_body` if set |
| `/search?q=` | Product search |
| `/api/search/suggest?q=` | JSON autocomplete suggestions |
| `/pages/{slug}` | CMS pages (`pages` table) |

## Marketing attribution

First-touch parameters are stored in session: `utm_*`, `gclid`, `fbclid`. They are copied onto orders at checkout.

## Admin

- `/admin/orders` — order list and detail, invoice PDF, packing slip PDF  
- `/admin/products`, `/admin/categories` — catalog CRUD  

## License / updates / backup (blueprint hooks)

Optional env: `COMMERCE_LICENSE_KEY`, `COMMERCE_LICENSE_DOMAIN`, `COMMERCE_LICENSE_ENFORCE`. Full installer/OTA update flows are not part of this README’s scope; use standard Laravel deployment and database backups for production.

## Tests

```bash
php artisan test
```

---

See `FINAL_STATUS_REPORT.md` in this directory for a concise implementation checklist against the product blueprint.
