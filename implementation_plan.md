# Debug and Fix Plan (8 Open Issues)

This plan strictly focuses on executing the 8 fixes required to correct system behavior without introducing new features or complex overengineering, ensuring complete frontend/backend synchronization.

## User Review Required
> [!IMPORTANT]
> The root causes for the 8 reported issues have been identified by closely examining your `CartService`, `ShopifyImporter`, `product-card.blade.php`, `store.js`, and `checkout.blade.php`. Please review the fixes below. Once approved, I will implement them all in a single pass.

## Proposed Changes

### 1. Variant Product Add to Cart Logic (Issues 1 & 4)
**Root Cause:** `product-card.blade.php` always submits the first variant regardless of whether explicit choices exist. On the product page, hitting `Buy Now` on disabled/Out of Stock items does not provide visual feedback prior to failing on the backend.
**Fix:**
- Modify `product-card.blade.php`: If `$product->variants->count() > 1`, hide the "Add to Cart" form and replace it with a button saying "Select Options" that links to the product detail page.
- Modify `storefront.product.blade.php` (JS & button logic): When `buyNowBtn` is disabled due to stock, change its innerHTML to "Out of Stock" so it's visually apparent.

### 2. Out of Stock Logic & 422 Errors (Issues 2 & 3)
**Root Cause:** When `CartService->maxQty()` calculates `0`, it triggers `abort(422)`, which causes Laravel to crash with a generic 500/422 page because the codebase lacks a `422.blade.php` screen and isn't handling normal form submissions correctly via flash messaging. Secondly, imported products that had "continue selling" mapped to Shopify are getting clamped to `track_inventory = true`.
**Fix:**
- **Backend:** Replace `abort(422, '...')` in `CartService.php` with `throw \Illuminate\Validation\ValidationException::withMessages(['qty' => '...'])`. This ensures correct JSON payloads via AJAX and clean redirect flash messages natively.
- **Import Logic:** Update `ShopifyImporter.php` to read the `Variant Inventory Policy` CSV column. If the policy is `continue`, it will set `track_inventory = false` so products matching this rule will remain in stock regardless of inventory qty.

### 3. Product Page CSS (Related Products) (Issue 5)
**Root Cause:** The `related.blade.php` loop may not be using the updated `.sf-product-grid` classes from the new design system shift.
**Fix:**
- Ensure `related.blade.php` and `gallery.blade.php` use `sf-container`, `sf-product-grid`, and `<x-product-card/>` components identically to the homepage so spacing and borders align perfectly.

### 4. Cart Count UI Issue (Issue 6)
**Root Cause:** The `.cart-badge` UI is mispositioned relative to its parent container on header navigation.
**Fix:**
- Adjust `.cart-badge` CSS in `storefront.css` (or `storefront.blade.php`) to use `position: absolute; top: 0; right: 0; transform: translate(50%, -50%);` on a `position: relative` wrapping icon.

### 5. Pincode Auto Fill Broken (Issue 7)
**Root Cause:** A missing HTML element `<div id="pin_spinner">` in `checkout.blade.php`. Because it's missing, JavaScript `document.getElementById('pin_spinner').classList` throws an immediate runtime error (`null.classList`) which halts all further code execution, preventing the API from fetching the state/city.
**Fix:**
- Reintroduce `<div id="pin_spinner" class="spinner-border spinner-border-sm d-none"></div>` directly adjacent to the pincode input field.

### 6. Profile / Account Page CSS (Issue 8)
**Root Cause:** Slight mismatches inside user dashboard, profile management, and layout files.
**Fix:**
- Verify `layouts/account.blade.php` completely encapsulates all account sub-pages (Profile, Returns, Addresses, Orders). Inject `sf-container` and tighten grid gaps for full layout consistency.

## Verification Plan

### Manual Verification
- Attempt to add out of stock items and verify Toast warning replaces the 422 crash error.
- Review Homepage: Variant products correctly show "Select Options" instead of "Add to cart".
- Test Checkout: Type a 6-digit Pincode to ensure the spinner appears and auto-fills districts.
- Test Shopify Import (Dry Run / Sub-file): Confirm `continue` policy respects stock limits correctly.
