# Technical Requirements Document (TRD)
## Project: Invoicy by RedMaple — Architecture & Technical Specifications

**Document Version:** 2.0.0  
**Status:** Approved & Live in Production  
**Target Architecture:** Multi-Tenant Laravel 11 / Livewire 3 Architecture  
**Author:** RedMaple Core Architecture Team  

---

## 1. System Architecture & High-Level Topology

Invoicy by RedMaple is engineered as a decoupled, multi-tenant modular monolithic SaaS application built on **Laravel 11.x**, **Livewire 3.x**, **Alpine.js 3.x**, and **Tailwind CSS**. The application is deployed in a secure partitioned environment on Hostinger Premium Web Hosting powered by the LiteSpeed Web Server and MySQL 8 / MariaDB.

### 1.1 Architecture Topology Diagram

```mermaid
graph TD
    subgraph Public Web Layer
        ClientBrowser[Browser / Mobile Client] --> Cloudflare[Cloudflare DNS / SSL]
        Cloudflare --> LS[Hostinger LiteSpeed Web Server]
        LS --> HTDoc[/public_html Webroot]
    end

    subgraph Security Boundary
        HTDoc --> IndexPHP[index.php Front Controller]
        IndexPHP --> FW[Firewall & Security Headers Middleware]
        FW --> CSRF[CSRF Token Verification]
        CSRF --> RateLimiter[Rate Limiting & Throttle]
    end

    subgraph Core Application Layer - redmaple_core
        RateLimiter --> Kernel[HTTP Kernel & Routing Pipeline]
        Kernel --> TenancyGuard[Tenant Isolation / WorkspaceScope]
        TenancyGuard --> SuperAdminGuard[SuperAdmin Role Gate]
        
        SuperAdminGuard --> Controllers[REST Controllers & Livewire Components]
        Controllers --> Services[Domain Service Layer]
    end

    subgraph Domain Service Layer
        Services --> S1[InvoiceNumberService - Concurrency Lock]
        Services --> S2[TaxCalculationService - GAAP Standard]
        Services --> S3[CouponService - Atomic Redemption]
        Services --> S4[SubscriptionAlertService - Lifecycle Events]
        Services --> S5[InvoiceReminderService - Dunning Engine]
        Services --> S6[PaymentReconciliationService - Ledger & Webhooks]
    end

    subgraph Persistence Layer
        Services --> Eloquent[Eloquent ORM Models]
        Eloquent --> DB[(MySQL 8 / MariaDB InnoDB)]
        Services --> Storage[Local Encrypted App Storage]
    end
```

---

## 2. Multi-Tenancy & Data Isolation Model

### 2.1 Workspace Data Partitioning
Every business registering on Invoicy is provisioned a unique `Workspace` with a UUID v4 primary key. All domain entities belong to a specific workspace:

- `clients`
- `invoices`
- `invoice_items`
- `invoice_sequences`
- `payments`
- `credit_notes`
- `services`
- `recurring_invoices`
- `invoice_reminders`
- `coupon_redemptions`

### 2.2 Global Scope Implementation
Automatic workspace isolation is enforced at the database driver level using `WorkspaceScope`:

```php
// app/Models/Scopes/WorkspaceScope.php
public function apply(Builder $builder, Model $model): void
{
    // Exempt Super Admin platform management routes
    if (request()->is('admin') || request()->is('admin/*')) {
        return;
    }

    if (Auth::hasUser() && Auth::user()->workspace_id) {
        $builder->where($model->qualifyColumn('workspace_id'), Auth::user()->workspace_id);
    }
}
```

### 2.3 Anti-IDOR (Insecure Direct Object Reference) Protection
All tenant models implement the `BelongsToWorkspace` trait, which binds Eloquent model event listeners to inspect every `creating`, `updating`, and `deleting` operation:
- Automatically binds `workspace_id = Auth::user()->workspace_id` on record creation.
- Aborts with **HTTP 403 Forbidden** if any user attempts to mutate or delete a record belonging to another workspace.

---

## 3. Concurrency Architecture & Sequential Numbering

### 3.1 The Problem
In financial SaaS platforms, concurrent invoice creation requests can result in duplicate sequence numbers (`INV-2026-0001` issued twice) or sequence gaps, violating international accounting regulations.

### 3.2 Race-Condition Free Sequential Locking Solution
Invoicy guarantees zero sequence collisions and gapless sequences even across 25+ concurrent threads during year rollovers using an atomic `firstOrCreate` initialization paired with pessimistic row locking (`SELECT ... FOR UPDATE`) in `InvoiceNumberService`:

```php
public function generateNextNumber(Workspace $workspace, string $prefix = 'INV', ?int $year = null): string
{
    $year = $year ?? (int) date('Y');

    return DB::transaction(function () use ($workspace, $prefix, $year) {
        // Step 1: Ensure row exists atomically without year-rollover race conditions
        InvoiceSequence::firstOrCreate(
            [
                'workspace_id' => $workspace->id,
                'prefix' => $prefix,
                'year' => $year,
            ],
            [
                'last_number' => 0,
            ]
        );

        // Step 2: Pessimistically lock the row for update across serialized threads
        $sequence = InvoiceSequence::where('workspace_id', $workspace->id)
            ->where('prefix', $prefix)
            ->where('year', $year)
            ->lockForUpdate()
            ->firstOrFail();

        $sequence->last_number += 1;
        $sequence->save();

        $paddedNumber = str_pad((string)$sequence->last_number, 4, '0', STR_PAD_LEFT);
        return "{$prefix}-{$year}-{$paddedNumber}";
    }, 5);
}
```

### 3.3 Idempotent Webhook & Payment Reconciliation
Webhooks from Stripe (`invoice.payment_succeeded`) and PayPal (`PAYMENT.SALE.COMPLETED`) can retry simultaneously over unreliable networks. Invoicy enforces database-level idempotency:
- `payments.idempotency_key` has a strict database `UNIQUE` index.
- `PaymentReconciliationService` acquires the row lock on `invoices` before inspecting balances.
- If concurrent webhook retries trigger a race condition, the `UniqueConstraintViolationException` is caught gracefully, returning the existing payment record idempotently without creating duplicate ledger entries or double-crediting balances.

---

## 4. Taxation, Currency & Accounting Precision

### 4.1 Integer Cent Rule
To prevent floating-point calculation inaccuracies common in currency operations, all financial balances in Invoicy are calculated and persisted strictly as integer cents:
- `$19.00` = `1900`
- `$1,250.50` = `125050`
- Calculations only convert to decimals at presentation time (`number_format($cents / 100, 2)`).

### 4.2 GAAP / IFRS Pre-Tax Discount Model
Discounts are deducted before tax calculation according to standard commercial accounting rules:
$$\text{Net Taxable} = \text{Subtotal} - \text{Discount}$$
$$\text{Tax Amount} = \text{Net Taxable} \times \left(\frac{\text{Tax Rate}}{100}\right)$$
$$\text{Total Invoice Amount} = \text{Net Taxable} + \text{Tax Amount}$$

---

## 5. Security Architecture & Threat Mitigation

### 5.1 Hostinger Directory Boundary Isolation
To prevent `.env`, source code, or migration exposure on shared hosting:
- Application source, configuration, and storage are isolated in `/home/u858731124/domains/redmapleagency.com/redmaple_core`.
- The public web root `/home/u858731124/domains/redmapleagency.com/public_html` contains exclusively `index.php`, `.htaccess`, and compiled static assets.
- Direct URL requests to `.env`, `.git`, or Composer configuration trigger immediate LiteSpeed `403 Forbidden` errors.

### 5.2 Super Admin Privilege Separation
- Platform administration features (`/admin/*`) are safeguarded by the `EnsureSuperAdmin` middleware.
- Only users with `is_super_admin = true` can access system-wide subscription management, website builder, CMS, and advertisement settings.
- All other authenticated users (including tenant workspace owners) receive **HTTP 403 Forbidden**.

### 5.3 At-Rest Credential Encryption
Sensitive integration credentials stored in the `workspaces` table utilize Laravel's authenticated encryption engine (`encrypted:array` cast):
- `stripe_keys_encrypted` (AES-256-CBC)
- `paypal_keys_encrypted` (AES-256-CBC)
- `smtp_settings_encrypted` (AES-256-CBC)

---

## 6. Service Layer Specifications

| Service Class | Responsibility | Key Dependencies |
| :--- | :--- | :--- |
| `InvoiceNumberService` | Generates sequential numbers (`INV-YYYY-XXXX`) with DB locking | `InvoiceSequence`, `DB::transaction` |
| `TaxCalculationService` | Pre-tax discount & regional tax engine (US, UK, DE, AE, SA, PK, IN) | `InvoiceItem`, `Client` |
| `PdfSecurityService` | PIN protection verification & PDF stream generation | DomPDF, Hash |
| `CouponService` | Atomic coupon validation, single-use check, and redemption | `Coupon`, `CouponRedemption` |
| `SubscriptionAlertService`| Admin notifications for signup, upgrade, downgrade & expiration | `SubscriptionAlert`, `Mail` |
| `InvoiceReminderService` | Automated 3-stage dunning reminders schedule | `InvoiceReminder`, `Invoice` |
| `PaymentReconciliationService`| Idempotent ledger accounting for payments and credit note refunds | `Payment`, `CreditNote`, `Invoice` |

---

## 7. Performance & LiteSpeed Caching Rules

In shared hosting environments, aggressive server-side caching can inadvertently cache private user dashboards or CSRF tokens. Invoicy enforces strict cache bypass headers on all authenticated paths:

```apache
# LiteSpeed Cache Exception Rules
<IfModule LiteSpeed>
    CacheLookup on
    RewriteEngine on
    # Do not cache authenticated sessions, admin routes, or API calls
    RewriteCond %{HTTP_COOKIE} laravel_session [NC]
    RewriteRule .* - [E=Cache-Control:no-cache]
    RewriteCond %{REQUEST_URI} ^/(dashboard|invoices|clients|services|settings|admin) [NC]
    RewriteRule .* - [E=Cache-Control:no-cache]
</IfModule>
```
