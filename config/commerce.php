<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application identity (defaults; overridden by settings + .env)
    |--------------------------------------------------------------------------
    */
    'name' => env('COMMERCE_STORE_NAME', env('APP_NAME', 'Store')),

    'gstin' => env('COMMERCE_GSTIN'),

    'currency' => env('COMMERCE_CURRENCY', 'INR'),

    'timezone' => env('COMMERCE_TIMEZONE', 'Asia/Kolkata'),

    /*
    |--------------------------------------------------------------------------
    | Feature toggles (database settings override these keys when present)
    |--------------------------------------------------------------------------
    */
    'features' => [
        'guest_checkout' => env('COMMERCE_GUEST_CHECKOUT', true),
        'reviews' => env('COMMERCE_REVIEWS_ENABLED', true),
        'wishlist' => env('COMMERCE_WISHLIST_ENABLED', true),
        'maintenance' => env('COMMERCE_MAINTENANCE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pricing display
    |--------------------------------------------------------------------------
    */
    'pricing' => [
        'show_compare_at' => true,
        'low_stock_threshold' => (int) env('COMMERCE_LOW_STOCK_THRESHOLD', 5),
        'bulk_min_qty' => (int) env('COMMERCE_BULK_MIN_QTY', 0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Integrations — credentials live in .env; enable flags in DB or here
    |--------------------------------------------------------------------------
    */
    'razorpay' => [
        'key' => env('RAZORPAY_KEY'),
        'secret' => env('RAZORPAY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    ],

    'meta' => [
        'pixel_id' => env('META_PIXEL_ID'),
        'capi_token' => env('META_CAPI_ACCESS_TOKEN'),
        'capi_test_event_code' => env('META_CAPI_TEST_EVENT_CODE'),
    ],

    'google' => [
        'analytics_id' => env('GA4_MEASUREMENT_ID'),
        'merchant_id' => env('GOOGLE_MERCHANT_ID'),
    ],

    'gtm' => [
        'container_id' => env('GTM_CONTAINER_ID'),
    ],

    'whatsapp' => [
        'business_number' => env('WHATSAPP_BUSINESS_NUMBER'),
        'api_token' => env('WHATSAPP_CLOUD_API_TOKEN'),
        'api_phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    ],

    'shiprocket' => [
        'email' => env('SHIPROCKET_EMAIL'),
        'password' => env('SHIPROCKET_PASSWORD'),
        'webhook_secret' => env('SHIPROCKET_WEBHOOK_SECRET'),
    ],

    'delhivery' => [
        'api_token' => env('DELHIVERY_API_TOKEN'),
        'client_id' => env('DELHIVERY_CLIENT_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | License (optional; validated when COMMERCE_LICENSE_ENFORCE=true)
    |--------------------------------------------------------------------------
    */
    'license' => [
        'key' => env('COMMERCE_LICENSE_KEY'),
        'domain' => env('COMMERCE_LICENSE_DOMAIN'),
        'enforce' => filter_var(env('COMMERCE_LICENSE_ENFORCE', false), FILTER_VALIDATE_BOOL),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feeds (public URLs relative to APP_URL)
    |--------------------------------------------------------------------------
    */
    'feeds' => [
        'google_path' => 'feed/google.xml',
        'facebook_path' => 'feed/facebook.xml',
    ],

    /*
    |--------------------------------------------------------------------------
    | Conversion Copy Defaults (Fallback text if admin leaves them empty)
    |--------------------------------------------------------------------------
    */
    'conversion_copy' => [
        'checkout' => [
            'prepaid_message' => 'Safe and secure payments powered by Razorpay.',
            'prepaid_badge' => 'Recommended',
            'cod_message' => 'Pay when your order is delivered to you.',
            'cod_fee_message' => '₹0 Additional Fee',
            'delivery_eta' => 'Estimated: 2-5 Business Days',
            'secure_message' => '100% safe & protected payments with SSL encryption',
            'payment_error' => 'Payment failed — please try again.',
            'place_order_cta' => 'Place Order',
        ],
        'product' => [
            'urgency_message' => 'Hurry, limited stock available!',
            'buy_now_subtext' => '',
            'trust_badges_title' => 'Secure Checkout',
            'trust_badges_text' => '100% safe & protected payments',
            'delivery_promise' => 'Estimated: 2-5 Business Days',
            'offer_message' => 'Save an extra 10% on prepaid orders',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Conversion Engine (Proprietary Capture & Trust OS)
    |--------------------------------------------------------------------------
    */
    'conversion_engine' => [
        'capture_offer' => [
            'engine_enabled' => true,
            'recovery_enabled' => false,
            'recovery_dry_run' => true,
            'enabled' => true,
            'trigger' => 'add_to_cart', // forced to ATC as per feedback
            'cooldown_days' => 14,
            'traffic_split_percent' => 80, // % of visitors who see the offer
            'min_cart_value' => 400, // Margin protection
            'offer_coupon_code' => 'FREESHIP',
            'ui_headline' => '🚚 Unlock Free Priority Shipping',
            'ui_subtext' => 'Save your mobile number to get free shipping and save your cart.',
            'ui_button_text' => 'Unlock Free Shipping'
        ],
        'checkout_os' => [
            'cod_badge_enabled' => true,
            'trust_badges' => ['Secure Checkout', 'UPI/Cards', 'Fast Delivery'],
            'reassurance_copy' => 'Your information is secure. No advance payment required for COD orders.',
            'cta_text' => 'Complete My Order'
        ],
        'abandonment_sequence' => [
            [
                'delay_minutes' => 30, 
                'template' => "Hi {name}, you left something behind in your cart! Complete your order now and enjoy your items: {link}"
            ],
            [
                'delay_minutes' => 360, // 6 hours (Escalation / Benefit reminder)
                'template' => "Your free shipping offer is waiting! The items in your cart are selling out fast. Claim your free shipping here: {link}"
            ],
            [
                'delay_minutes' => 1440, // 24 hours (Loss aversion)
                'template' => "Final reminder! We will be clearing your cart soon. Complete your order now before your items are gone forever: {link}"
            ]
        ]
    ],

];
