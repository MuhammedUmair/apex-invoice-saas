# Security Architecture & Audit Checklist (SECURITY_CHECKLIST.md)
## Project: Invoicy by RedMaple — Enterprise Multi-Tenant Security Standards

**Document Version:** 2.0.0  
**Status:** 100% Passed & Verified on Hostinger Production  
**Audit Standard:** OWASP Top 10 + SaaS Multi-Tenancy Data Isolation  

---

## 1. Multi-Tenant Isolation & Anti-IDOR Guards

| Check ID | Security Verification Item | Mechanism / Implementation | Status |
| :--- | :--- | :--- | :--- |
| **SEC-TEN-01** | Global Tenant Scoping | `WorkspaceScope` automatically applies `where workspace_id = Auth::user()->workspace_id` to all tenant Eloquent queries. | **PASS** |
| **SEC-TEN-02** | Anti-IDOR Creation Guard | `BelongsToWorkspace::creating()` forces `workspace_id = Auth::user()->workspace_id`, preventing spoofed workspace IDs. | **PASS** |
| **SEC-TEN-03** | Anti-IDOR Update Guard | `BelongsToWorkspace::updating()` aborts with HTTP 403 if target `workspace_id` does not match authenticated user. | **PASS** |
| **SEC-TEN-04** | Anti-IDOR Deletion Guard | `BelongsToWorkspace::deleting()` aborts with HTTP 403 if deletion target belongs to another tenant. | **PASS** |
| **SEC-TEN-05** | Direct Route Model Binding | Controllers verify that requested model instances belong to the tenant before executing actions. | **PASS** |

---

## 2. Super Admin Privilege Separation & Route Gates

| Check ID | Security Verification Item | Mechanism / Implementation | Status |
| :--- | :--- | :--- | :--- |
| **SEC-ADM-01** | Dedicated Super Admin Flag | Added `is_super_admin` boolean column to `users` table (`default: 0`). Normal workspace owners cannot elevate privileges. | **PASS** |
| **SEC-ADM-02** | Route Middleware Guard | `EnsureSuperAdmin` middleware inspects `isSuperAdmin()` on all `/admin/*` routes; aborts with HTTP 403 for unauthorized tenants. | **PASS** |
| **SEC-ADM-03** | UI Menu Concealment | "Platform Admin" sidebar section is wrapped in `@if(auth()->user()->isSuperAdmin())`; hidden from tenant owners. | **PASS** |
| **SEC-ADM-04** | Super Admin Tenancy Exemption | Global tenant scope safely bypassed on `/admin/*` routes, enabling Super Admin to monitor across all tenant contracts. | **PASS** |

---

## 3. Authentication, Password & Session Security

| Check ID | Security Verification Item | Mechanism / Implementation | Status |
| :--- | :--- | :--- | :--- |
| **SEC-AUT-01** | Strong Password Hashing | All passwords stored using **Bcrypt** with algorithmic cost factor of 12. Plaintext passwords never logged. | **PASS** |
| **SEC-AUT-02** | Login Rate Limiting | Authentication endpoints enforce IP-based rate limiting to prevent brute-force credential stuffing. | **PASS** |
| **SEC-AUT-03** | Session Fixation Protection | `session()->regenerate()` executed on every successful authentication. | **PASS** |
| **SEC-AUT-04** | Safe Session Destruction | Logout invalidates session, regenerates CSRF token, and redirects cleanly to `/login`. | **PASS** |
| **SEC-AUT-05** | MethodNotAllowed Protection | In `bootstrap/app.php`, unexpected HTTP methods on `/logout` safely trigger automated session termination without 500 errors. | **PASS** |

---

## 4. Financial Calculations & Concurrency Safety

| Check ID | Security Verification Item | Mechanism / Implementation | Status |
| :--- | :--- | :--- | :--- |
| **SEC-FIN-01** | Integer Cent Math | All monetary values calculated in integer cents to eliminate IEEE 754 floating-point rounding exploits. | **PASS** |
| **SEC-FIN-02** | Invoice Sequence Concurrency | `SELECT ... FOR UPDATE` pessimistic database locking on `invoice_sequences` prevents duplicate invoice numbering. | **PASS** |
| **SEC-FIN-03** | Atomic Coupon Redemption | Concurrency lock on `coupons` table prevents race condition double-spending during flash promotions. | **PASS** |
| **SEC-FIN-04** | Pre-Tax Discount Integrity | GAAP/IFRS compliant deduction preventing tax underreporting or calculation manipulation. | **PASS** |
| **SEC-FIN-05** | Immutable Audit Ledger | Credit notes and completed invoice payments cannot be deleted; full accounting history is preserved. | **PASS** |
| **SEC-FIN-06** | Idempotent Webhook Recording | Payment webhooks use unique transaction/event keys in `payments.idempotency_key` to reject duplicate billing events. | **PASS** |

---

## 5. Data at Rest & Encryption

| Check ID | Security Verification Item | Mechanism / Implementation | Status |
| :--- | :--- | :--- | :--- |
| **SEC-ENC-01** | Payment Key Encryption | `stripe_keys_encrypted` and `paypal_keys_encrypted` stored using **AES-256-CBC** authenticated encryption. | **PASS** |
| **SEC-ENC-02** | Custom SMTP Encryption | Agency email credentials stored using **AES-256-CBC** authenticated encryption. | **PASS** |
| **SEC-ENC-03** | PDF Security PIN | Customer PDF downloads protected by a 4-digit PIN code challenge verified against stored invoice hash. | **PASS** |

---

## 6. Web Security, XSS & Injection Defense

| Check ID | Security Verification Item | Mechanism / Implementation | Status |
| :--- | :--- | :--- | :--- |
| **SEC-WEB-01** | SQL Injection Defense | 100% of queries executed through Eloquent ORM or parameterized PDO statements. Raw SQL concatenation strictly prohibited. | **PASS** |
| **SEC-WEB-02** | Cross-Site Scripting (XSS) | Blade default `{{ $var }}` auto-escaping enabled on all user inputs (company names, client notes, descriptions). | **PASS** |
| **SEC-WEB-03** | CSRF Protection | Global CSRF verification enabled on all `POST`, `PUT`, `DELETE` requests. Exempted only on signed `/webhooks/*`. | **PASS** |
| **SEC-WEB-04** | Application Firewall | `FirewallMiddleware` automatically inspects incoming payloads for malicious path traversal, injection, or scanner payloads. | **PASS** |
| **SEC-WEB-05** | HTTP Security Headers | `SecurityHeadersMiddleware` emits `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin`. | **PASS** |

---

## 7. Hostinger Shared-Hosting Hardening

| Check ID | Security Verification Item | Mechanism / Implementation | Status |
| :--- | :--- | :--- | :--- |
| **SEC-HST-01** | Codebase Isolation | Core Laravel codebase deployed outside the public web root in `/redmaple_core`. Only `index.php` in `/public_html`. | **PASS** |
| **SEC-HST-02** | Configuration File Protection | Web server rules actively block requests to `.env`, `.git`, `composer.json`, and hidden dotfiles (HTTP 403). | **PASS** |
| **SEC-HST-03** | LiteSpeed Cache Isolation | Authenticated session cookies and dynamic routes are excluded from server cache to prevent session leakage. | **PASS** |
| **SEC-HST-04** | Symlink Attack Defense | `Options -Indexes` and `FollowSymLinks` properly configured in `.htaccess`. | **PASS** |
