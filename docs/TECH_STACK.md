# Technology Stack Specification
## Project: Invoicy by RedMaple — Enterprise Multi-Tenant SaaS

**Document Version:** 2.0.0  
**Status:** Approved & Live in Production  
**Maintained By:** RedMaple DevOps & Infrastructure Architecture  

---

## 1. Stack Summary Matrix

| Layer | Technology | Version | Purpose |
| :--- | :--- | :--- | :--- |
| **Backend Framework** | Laravel Framework | `11.x` | Core application framework, routing, ORM, IoC container |
| **Runtime Engine** | PHP | `8.2.x` / `8.3.x` | Server-side execution engine (OPcache enabled) |
| **Reactivity Layer** | Livewire | `3.x` | Real-time frontend reactivity without complex SPA toolchains |
| **Client Scripting** | Alpine.js | `3.x` | Lightweight declarative client state (modals, dropdowns, tabs) |
| **CSS Styling Engine** | Tailwind CSS | `3.x` | Utility-first CSS framework with custom design tokens |
| **Iconography** | Phosphor Icons | `2.1.1` | Web-optimized dual-weight icon library (Regular & Bold) |
| **Database Engine** | MySQL / MariaDB | `8.0+` / `10.11+` | Relational database with InnoDB transactional storage engine |
| **Web Server** | LiteSpeed Enterprise | `Latest` | High-performance HTTP/2 & HTTP/3 server with caching engine |
| **Hosting Infrastructure**| Hostinger Premium Cloud | Shared / Cloud | Production hosting environment with cPanel/hPanel architecture |
| **Document Generation**| DomPDF & Native Blade | `2.x` | High-fidelity invoice PDF generation with vector graphics |
| **Email Transport** | SMTP / PHPMailer | TLS v1.3 | Authenticated mail delivery (System default & per-workspace) |
| **Deployment Pipeline**| Python + Git + FTP API | `3.10+` | Automated deployment bridge with atomic cache compilation |

---

## 2. Backend & Framework Architecture

### 2.1 PHP Core Extensions Required
- `ext-pdo_mysql`: High-throughput database abstraction.
- `ext-bcmath`: Arbitrary precision mathematics for currency operations.
- `ext-mbstring`: Multi-byte string manipulation for Urdu, Arabic, Hindi, and Chinese.
- `ext-gd` / `ext-imagick`: Image processing for workspace logos and ad banners.
- `ext-json`: Native JSON schema handling for `features_json` and `bank_details`.
- `ext-curl`: Secure API communication with external services.
- `ext-openssl`: Cryptographic token signing and AES-256-CBC at-rest encryption.

### 2.2 Laravel 11 System Components
- **Eloquent ORM:** Advanced relational mappings (`hasMany`, `belongsTo`, `latestOfMany`, `HasUuids`).
- **Global Tenancy Scopes:** Enforced at database driver layer via `WorkspaceScope`.
- **Artisan Console:** Background scheduling for expiration scans and dunning reminders.
- **Blade Templating Engine:** High-performance server-rendered view hierarchy with layout inheritance.
- **Form Request & Validation:** Strict data-type verification, integer limits, and anti-XSS validation.

---

## 3. Frontend & Presentation Layer

### 3.1 Livewire 3 Reactive Components
Invoicy leverages Laravel Livewire 3 to deliver single-page application (SPA) fluidity without the overhead of client-side JavaScript frameworks:
- **`InvoiceBuilder`:** Real-time line item updates, tax recalculation, client selection, and draft autosaving.
- **`ClientDirectory`:** Dynamic search, pagination, and inline editing without full page reloads.
- **`ServiceCatalog`:** Dynamic catalog management and 1-click industry preset imports.
- **`MembershipCheckout`:** Real-time coupon code validation, price calculation, and upgrade request dispatching.

### 3.2 Alpine.js Micro-Interactions
- Mobile sidebar navigation drawer toggle.
- Modal dialogs (Renew subscription modal, Edit owner contact modal, Ad creator modal).
- Tabbed user interface switching on Dashboard, Website Builder, and Subscription Control Panel.
- Live preview hex pickers for workspace branding.

### 3.3 Tailwind CSS Theme Configuration
Custom design tokens configured directly into the application layout:
- **Brand Red:** `#dc2626` (Red-600) with deep hover states (`#b91c1c`).
- **Slate Neutrals:** Modern zinc/slate palette ranging from `#0f172a` (Slate-900) to `#f8fafc` (Slate-50).
- **RTL Typography Rules:** Dedicated CSS rules for `[dir="rtl"]` supporting Arabic and Urdu typography.

---

## 4. Database & Storage Architecture

### 4.1 Storage Engine & Collations
- **Engine:** `InnoDB` across all 20+ tables for full ACID compliance and row-level locking.
- **Default Character Set:** `utf8mb4`
- **Default Collation:** `utf8mb4_unicode_ci` (guaranteeing exact multi-lingual sort orders and emoji support).

### 4.2 Primary Key Strategy
- **UUID v4 / Ordered UUIDs (`HasUuids`):** Employed on primary domain entities (`Workspace`, `Client`, `Service`, `Invoice`, `Payment`, `CreditNote`, `SubscriptionAlert`) to prevent sequential enumeration attacks and IDOR scraping.
- **Integer Auto-Increment:** Employed on internal pivot/sequence counters (`invoice_sequences`, `plans`) for maximum index look-up performance.

---

## 5. Security & Firewall Architecture

### 5.1 Dual-Tier Security Perimeter
1. **Tier 1 — Web Server (.htaccess & LiteSpeed):**
   - Blocks direct HTTP access to hidden files (`.env`, `.git`).
   - Forces HTTPS redirection across all incoming traffic.
   - Blocks malicious bots, scanners, and exploit attempts targeting sensitive paths.
2. **Tier 2 — Application Middleware:**
   - `FirewallMiddleware`: Inspects request payloads for SQL injection keywords, traversal sequences, and XSS vectors.
   - `SecurityHeadersMiddleware`: Emits strict HTTP headers (`X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`).
   - `EnsureSuperAdmin`: Restricts all `/admin/*` management routes exclusively to users with `is_super_admin = true`.

---

## 6. Deployment & CI/CD Tooling

- **Repository:** Git hosted on GitHub (`origin/main`).
- **Deployment Protocol:** Atomic ZIP packaging via Python FTPS with remote bridge extraction.
- **Cache Optimization:** Production pipeline executes:
  - `php artisan view:cache`
  - `php artisan route:cache`
  - `php artisan config:cache`
  - `php artisan optimize`
