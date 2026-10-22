# RedMaple Invoice Generator 🍁

> High-performance, multi-tenant commercial SaaS Invoicing platform built on modern Laravel 11, Livewire 3, and Tailwind CSS. Deployed live on Hostinger Web Hosting.

[![Live Site](https://img.shields.io/badge/Live-redmapleagency.com-red.svg)](https://redmapleagency.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%20%7C%208.3-blue.svg)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-pink.svg)](https://livewire.laravel.com)
[![License](https://img.shields.io/badge/License-Proprietary-darkgreen.svg)]()

---

## 🌐 Production Environment

| Parameter | Production Value |
| :--- | :--- |
| **Live Domain** | [https://redmapleagency.com](https://redmapleagency.com) |
| **Login Portal** | [https://redmapleagency.com/login](https://redmapleagency.com/login) |
| **Registration** | [https://redmapleagency.com/register](https://redmapleagency.com/register) |
| **Hosting Platform** | Hostinger Premium Cloud / Web Hosting |
| **Web Server** | LiteSpeed Enterprise Web Server with HTTP/3 & SSL |
| **Database** | MySQL 8.0 (`u858731124_rmacom_Inv_DB`) |
| **Core Path (Private)** | `/home/u858731124/domains/redmapleagency.com/redmaple_core` |
| **Web Root (Public)** | `/home/u858731124/domains/redmapleagency.com/public_html` |

---

## 🔑 Demo Access Credentials

The database has been seeded with initial agency data and subscription plans:

- **Email**: `admin@redmaplestudio.com`
- **Password**: `Password123!`
- **Role**: Agency Owner (`RedMaple Digital Studio`)
- **Active Subscription**: Pro Plan

---

## 🚀 Key Modules & Capabilities

1. **Multi-Tenant Agency Workspace Isolation**:
   - Automatic workspace scoping via Eloquent global scope (`WorkspaceScope`).
   - Every invoice, client, service preset, and subscription belongs to an isolated agency tenant.

2. **Client Directory CRM**:
   - Comprehensive client management with multi-currency (`USD`, `EUR`, `GBP`, `AED`, `CAD`, `AUD`, `PKR`, `INR`).
   - Multi-language defaults including English, Arabic (العربية), Urdu (اردو), Hindi (हिन्दी), Turkish (Türkçe), German, French, Spanish, and Chinese.

3. **Dynamic Invoice Builder**:
   - Real-time line item calculations with tax rates, discounts, and payment terms.
   - Sequential, atomic invoice number generation per workspace (`INV-2026-0001`).

4. **Secure Multilingual PDF Generation**:
   - Powered by `mpdf/mpdf` with UTF-8 character shaping and RTL (Right-to-Left) bidirectional support for Arabic and Urdu.
   - Optional PIN / password challenge on public customer invoice links.

5. **SaaS Membership & Coupon Engine**:
   - Pre-configured plans: **Free Trial**, **Starter Agency ($19/mo)**, **Pro Business ($49/mo)**, **Enterprise Scale ($99/mo)**.
   - Seeded promotional discount coupons:
     - `LAUNCH2026`: 20% off
     - `HOSTINGER50`: 50% off
     - `SAVE10FIXED`: $10.00 flat discount

6. **Payment Gateways & Webhooks**:
   - Native integration endpoints for Stripe and PayPal recurring subscriptions and one-off customer invoice payments.
   - CSRF bypass for automated webhook handlers (`/webhooks/stripe`, `/webhooks/paypal`).

---

## 🔒 Security & Architecture Highlights

- **Separation of Core & Public Web Root**:
  The application source code, vendor packages, `.env`, and storage files are placed **above** the public root in `/redmaple_core`, preventing direct HTTP access to configuration files or source code.
- **LiteSpeed Cache Exemption**:
  Dynamic application routes (`/dashboard`, `/invoices`, `/login`, etc.) are exempted from LiteSpeed caching in `.htaccess` to ensure instant data synchronization and CSRF token integrity.
- **Symlinked Public Storage**:
  `public_html/storage` points to `redmaple_core/storage/app/public` via a direct filesystem symlink.

---

## 🛠️ Maintenance & CLI Commands

Commands can be run via SSH or maintenance bridge scripts:

```bash
# Clear & Cache Configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Re-run Migrations & Seeders
php artisan migrate --force
php artisan db:seed --force
```

© 2026 RedMaple. All rights reserved.
