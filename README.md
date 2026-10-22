# RedMaple Invoice Generator 🍁

> High-performance, multi-tenant commercial SaaS Invoicing platform built on modern Laravel 11, Livewire 3, and Tailwind CSS. Deployed live on Hostinger Web Hosting.

[![Live Site](https://img.shields.io/badge/Live-redmapleagency.com-red.svg)](https://redmapleagency.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%20%7C%208.3-blue.svg)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-pink.svg)](https://livewire.laravel.com)
[![License](https://img.shields.io/badge/License-Proprietary-darkgreen.svg)]()

---

## 🌐 Production Environment

| Portal / Module | Live URL | Status | Description |
| :--- | :--- | :--- | :--- |
| **Main Website** | [https://redmapleagency.com](https://redmapleagency.com) | `200 OK` | Public domain with automatic authentication redirect |
| **Agency Sign In** | [https://redmapleagency.com/login](https://redmapleagency.com/login) | `200 OK` | Secured Bcrypt login portal |
| **Documentation Hub** | [https://redmapleagency.com/docs](https://redmapleagency.com/docs) | `200 OK` | Multilingual user manual (English, Urdu, Arabic, Hindi, Turkish) |
| **Agency Dashboard** | [https://redmapleagency.com/dashboard](https://redmapleagency.com/dashboard) | `200 OK` | Revenue, pending balances, recent invoices & payments |
| **Invoice Management** | [https://redmapleagency.com/invoices](https://redmapleagency.com/invoices) | `200 OK` | Invoices list with status filtering & PDF download |
| **Invoice Builder** | [https://redmapleagency.com/invoices/create](https://redmapleagency.com/invoices/create) | `200 OK` | Reactive invoice creator with sequential locking (`INV-2026-0001`) |
| **Credit Notes Ledger** | [https://redmapleagency.com/credit-notes](https://redmapleagency.com/credit-notes) | `200 OK` | Audit-compliant credit notes & refund issuance |
| **Client Directory CRM** | [https://redmapleagency.com/clients](https://redmapleagency.com/clients) | `200 OK` | Client management with Urdu, Hindi, Turkish, Arabic & multi-currency |
| **Catalog & Presets** | [https://redmapleagency.com/services](https://redmapleagency.com/services) | `200 OK` | Custom services + 5 industry pre-loaded catalogs |
| **Plan & Billing** | [https://redmapleagency.com/membership](https://redmapleagency.com/membership) | `200 OK` | SaaS subscriptions & atomic coupon checkout |
| **Agency Settings** | [https://redmapleagency.com/settings](https://redmapleagency.com/settings) | `200 OK` | Profile, wire transfer bank instructions, brand colors, Stripe/PayPal keys |

---

## 🔑 Demo Access Credentials

The database has been seeded with initial agency data, demo clients, and subscription plans:

- **Email**: `admin@redmaplestudio.com`
- **Password**: `Password123!`
- **Agency Workspace**: RedMaple Digital Studio
- **Subscription Plan**: Pro Business Plan (Active)

---

## 🚀 Deployed Modules & Capabilities

1. **Client CRM with Urdu, Hindi, Turkish, and Arabic Language Preferences**:
   - Add clients with specific invoice language preferences:
     - 🇺🇸 English (`en`)
     - 🇵🇰 Urdu - اردو (`ur`) - Right-to-Left (RTL)
     - 🇮🇳 Hindi - हिन्दी (`hi`)
     - 🇹🇷 Turkish - Türkçe (`tr`)
     - 🇦🇪 Arabic - العربية (`ar`) - Right-to-Left (RTL)
     - 🇪🇸 Spanish, 🇫🇷 French, 🇩🇪 German, 🇨🇳 Chinese
   - Configurable default payment terms (`due_on_receipt`, `net_7`, `net_14`, `net_30`, `net_60`).

2. **Multilingual Documentation Hub (`/docs`)**:
   - Comprehensive interactive software guide available in **English**, **Urdu (اردو)**, **Arabic (العربية)**, **Hindi (हिन्दी)**, and **Turkish (Türkçe)**.
   - Live topics covering Multi-Tenancy, Client CRM, Invoice Numbering Locks, Tax Snapshots, PDF Passwords, Credit Notes, Stripe/PayPal Webhooks, and Coupons.

3. **Agency Workspace Settings & Branding Management (`/settings`)**:
   - Edit agency company name, tax ID, phone, email, website, and business address.
   - Configure bank wire remittance instructions (Bank Name, Account/IBAN, Routing Code, SWIFT).
   - Custom brand primary and secondary color customization with live hex preview.
   - Whitelabel toggle and dedicated inputs for Stripe & PayPal live credentials.

4. **Credit Notes & Refunds Management (`/credit-notes`)**:
   - Issue partial or full credit notes (`CN-YYYY-XXXX`) directly against any invoice.
   - Full ledger audit trail preventing arbitrary deletion of issued accounting records.
   - Automatic invoice balance adjustment and status updates.

5. **Platform UI Language Switcher (with RTL Layout)**:
   - Dynamic topbar language switcher supporting 8 languages.
   - Automatically switches document direction to `dir="rtl"` when Urdu or Arabic is selected.

6. **Invoicing & PDF Password PIN Security**:
   - Concurrency-safe sequential numbering per workspace.
   - Pre-tax discount calculation adhering to GAAP and IFRS standards.
   - Encrypted PDF download challenge with PIN protection.

7. **SaaS Membership & Atomic Coupons**:
   - Seeded promotional discount codes:
     - `LAUNCH2026`: 20% off
     - `HOSTINGER50`: 50% off
     - `SAVE10FIXED`: $10.00 flat discount
   - Concurrency-safe atomic redemption counter preventing race condition abuse during checkout spikes.

---

## 🔒 Production Architecture & Security Setup

- **Core Code Isolation**: Application code lives outside the public root in `/home/u858731124/domains/redmapleagency.com/redmaple_core`.
- **Public Web Root**: Only `index.php`, `.htaccess`, and public assets exist in `/public_html`. Direct access to `.env` returns `403 Forbidden`.
- **LiteSpeed Cache Rules**: Dynamic authenticated routes and CSRF tokens are exempted from server caching.

---

© 2026 RedMaple. All rights reserved.
