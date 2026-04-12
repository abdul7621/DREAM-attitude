# Client Onboarding Guide

Welcome to the **Ikhlas Fragrance** D2C platform admin dashboard.

## 1. Initial Setup Checklist

Go to **Settings** in the Admin panel and configure the following:

- [ ] **Store Information**: Update store name, email, phone, and address.
- [ ] **Payments**: Add your Razorpay Key ID and Secret. Enable/Disable COD as needed.
- [ ] **Shipping**: Set your origin pincode and default/free shipping fees.
- [ ] **Shiprocket**: If using Shiprocket, enter your Shiprocket Email and Password, and enable the integration.
- [ ] **Tax/GST**: Configure your default GST rate, GSTIN, and choose whether prices are inclusive or exclusive of tax.
- [ ] **Email/SMTP**: Configure your Mail Host, Username, and Password to enable order emails.

## 2. Managing Products & Variants

1. Go to **Catalog > Products**.
2. Click **Add Product** or import your Shopify CSV.
3. Every product requires at least one **Variant** to be purchasable (even if it's a "Default Title" variant).
4. **Compare Price**: Fill this out if you want to show a discount badge/strikethrough price.

## 3. Order Processing & Fulfillment

1. When an order is placed, it will show up in **Orders**.
2. Click on the order to view details.
3. If Shiprocket is enabled, shipments can be created directly from the dashboard.
4. Update the **Order Status** (Processing, Shipped, Delivered) to trigger email/SMS notifications to the customer.

## 4. Troubleshooting Support

If you face any issues related to payments, double check your Razorpay keys. If emails are not sending, verify your SMTP credentials in Settings > Email/SMTP.
