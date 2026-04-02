# Final status report (blueprint alignment)

This report reflects the codebase state after the latest implementation pass. It is intended for engineering handoff, not marketing.

## Confirmed implemented (production-usable paths)

- **Catalog & storefront**: Categories, products, variants, images, pricing tiers, cart, checkout, COD, Razorpay, order success page.
- **Shipping**: `shipping_rules` (flat / weight bands / pincode prefixes), `ShippingService::quote()`, `shipments` row created per order (carrier `manual`, status `pending`), AWB/tracking columns available on `shipments`.
- **Coupons**: `coupons` table, apply/remove in cart, validation, discount in totals, snapshot on order, usage increment (COD at place; Razorpay at payment finalize).
- **Attribution**: Session capture of UTM + `gclid` + `fbclid`; persisted on `orders`.
- **SEO / feeds**: Dynamic meta + JSON-LD on product page; `/sitemap.xml`; `/robots.txt` (DB setting `seo.robots_body` when set); `redirects` middleware; `/feed/google.xml` and `/feed/facebook.xml`.
- **Ads & analytics**: GTM and/or GA4 and/or Meta Pixel in layout; `dataLayer` events for `view_item`, `add_to_cart`, `begin_checkout`, `purchase` (and Meta `fbq` mirrors where configured); **Meta Conversions API** `Purchase` from order success (deduped per session key).
- **Search**: `/search` with LIKE + optional MySQL `SOUNDEX`; `/api/search/suggest` for autocomplete.
- **PDF**: GST-oriented invoice template + packing slip; Dompdf; admin download routes.
- **Admin**: Orders index/show, PDFs, existing product/category CRUD; admin nav link to orders.
- **CMS pages**: `pages` table + `/pages/{slug}`.

## Partially implemented or stubbed

- **Carrier APIs**: Shiprocket/Delhivery credentials exist in config; no full create-label/track webhook flow in code (data model supports AWB/URL/status).
- **Admin breadth**: No full UI yet for coupons, shipping rules, redirects, SEO editor, import wizard, reviews moderation, returns, analytics dashboard, feed “preview” screen, customer CRM — only orders + catalog as above.
- **Migration wizard**: `import_jobs` schema exists; Shopify/Woo importers are not completed in this pass.
- **Notifications**: WhatsApp/e-mail event listeners are not fully wired for all order states.
- **Reviews / wishlist / recently viewed / returns / abandoned cart / themes / installer**: Tables and/or models may exist from migrations; end-to-end flows and admin UIs are not all finished.
- **Productization**: License enforcement, in-app updater, and one-click DB backup are not fully delivered as product features (standard Laravel + hosting backup practices apply).

## Gaps to reach “100% blueprint”

Remaining work is primarily: admin modules for all entities, import pipelines, notification listeners, abandoned-cart and recovery jobs, return/refund workflows with store credit, theme JSON + switcher, hardened carrier integrations, and optional licensing/installer packaging.

No single automated test suite assertion was run in CI for this report; run `php artisan migrate` and smoke-test checkout before production.
