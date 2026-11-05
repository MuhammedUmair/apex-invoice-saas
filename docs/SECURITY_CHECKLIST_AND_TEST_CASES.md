# Invoicy by RedMaple - Security Checklist & Penetration Testing Guide

This document defines the enterprise security architecture, OWASP Top 10 compliance checklist, threat mitigations, and comprehensive automated/manual test cases to protect **Invoicy by RedMaple** (`invoicy.redmapleagency.com` / `redmapleagency.com`) against cyber attacks, data theft, and unauthorized access.

---

## 1. Security Architecture & Defense-in-Depth

Invoicy is a multi-tenant SaaS application managing sensitive financial records, invoices, client personal identifiable information (PII), and payment reminders. The application enforces **Defense-in-Depth** across 5 layers:

```
[ Layer 1: Edge & Network ] -> Cloudflare / Hostinger DDoS Protection & TLS 1.3 HTTPS
[ Layer 2: Web Server ]     -> Apache mod_rewrite, .env Protection, Security Headers
[ Layer 3: Application ]    -> Laravel Middleware (CSRF, Auth, Tenant Scoping, Rate Limiting)
[ Layer 4: Business Logic ] -> Policy Guards, Schema Validation, Model Scopes, XSS Purging
[ Layer 5: Data & Storage ] -> PDO Prepared Statements, Argon2id/Bcrypt, Encrypted Sessions
```

---

## 2. OWASP Top 10 Checklist & Invoicy Mitigations

### A01: Broken Access Control & Multi-Tenant IDOR
* **Threat**: A malicious tenant alters an invoice UUID or client ID in an API request to view or tamper with another agency's data (Insecure Direct Object Reference).
* **Mitigations Implemented**:
  - **UUID Primary Keys**: Public entities (Invoices, Credit Notes, Clients) utilize random UUIDv7/UUIDv4 identifiers rather than sequential integer IDs, preventing enumeration attacks.
  - **Strict Workspace Scoping**: All Eloquent queries are scoped strictly to the authenticated user's workspace:
    ```php
    // Pattern enforced throughout all Controllers & Livewire components:
    $invoice = Invoice::where('workspace_id', auth()->user()->workspace_id)
        ->findOrFail($id);
    ```
  - **Role-Based Access Control (RBAC)**: Super Admin routes (`/admin/subscriptions`) and CMS editing are restricted exclusively to `owner` and `admin` roles via controller authorization checks.

### A02: Cryptographic Failures & Credential Protection
* **Threat**: Interception of passwords, API tokens, or SMTP credentials in transit or from database dumps.
* **Mitigations Implemented**:
  - **Password Hashing**: Strong one-way hashing with salted Bcrypt / Argon2id (`Hash::make()`). Plaintext passwords are never stored or logged.
  - **Zero Pre-fill Policy**: Login forms do not pre-populate credentials or passwords.
  - **TLS 1.3 Strict HTTPS**: Forced SSL redirection on all domains.
  - **Encrypted Session Cookies**: Session cookies have `HttpOnly`, `SameSite=Lax`, and `Secure` attributes enabled to prevent cookie theft via script injection.

### A03: Injection Attacks (SQLi, Command Injection, LDAP)
* **Threat**: Malicious SQL snippets injected through search bars, filter queries, or line item fields (`' OR '1'='1' --`).
* **Mitigations Implemented**:
  - **100% Parameterized Queries**: Laravel Eloquent ORM utilizes PDO prepared statements for all database operations. No raw variable concatenations (`DB::raw("... $input")`) are used with unsanitized parameters.
  - **Strong Type Casting**: IDs, numeric amounts, tax rates, and quantities are explicitly validated and cast as integers/floats before execution.
  - **Blade Auto-Escaping**: All Blade view templates use `{{ $variable }}` which invokes `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` to prevent HTML/script payload execution.

### A04: Insecure Design & Business Logic Flaws
* **Threat**: Negative line items to produce negative invoice totals; unauthorized credit note over-issuance; falsifying dates.
* **Mitigations Implemented**:
  - **Strict Validation Rules**: Invoice quantities require integer `>= 1`; line items require price `>= 0`.
  - **Date Guardrails**: Payment due date cannot precede invoice issue date.
  - **Immutability of Finalized Invoices**: Paid or cancelled invoices cannot have line items silently altered without generating audit traces or credit notes.

### A05: Security Misconfiguration
* **Threat**: Stack traces displaying database credentials on 500 errors; exposed `.env` files; public directory listings.
* **Mitigations Implemented**:
  - **Production Debug Mode Off**: `APP_DEBUG=false` on production servers. Custom friendly error views (`404.blade.php`, `500.blade.php`, `405.blade.php`) conceal system internals.
  - **Split-Directory Deployment**: The Laravel core (`redmaple_core`) resides strictly **outside** the web root (`public_html`). The public web root only contains `index.php` and static assets.
  - **Protected Apache Configuration**: Direct HTTP requests to `.env`, `.git`, `composer.json`, or `storage/logs` return `403 Forbidden` via `.htaccess`.
  - **Security HTTP Headers**:
    ```http
    X-Frame-Options: SAMEORIGIN
    X-Content-Type-Options: nosniff
    X-XSS-Protection: 1; mode=block
    Referrer-Policy: strict-origin-when-cross-origin
    ```

### A06: Vulnerable and Outdated Components
* **Threat**: Known CVEs in third-party PHP packages or JavaScript dependencies.
* **Mitigations Implemented**:
  - Clean `composer.lock` dependencies.
  - No bloated npm dependencies required in production; Alpine.js and Tailwind are pinned to stable releases.

### A07: Identification and Authentication Failures (Brute Force & Credential Stuffing)
* **Threat**: Automated password guessing on `/login` or password reset endpoints.
* **Mitigations Implemented**:
  - **Rate Limiting (Throttling)**: Login attempts throttled to 5 attempts per minute per IP address.
  - **Session Fixation Defense**: `request()->session()->regenerate()` called immediately upon successful authentication.
  - **Strong Password Enforcement**: Registration and password changes validate minimum 8 characters with required mixed case, digits, and special symbols.
  - **Password Reset Flow**: Single-use, time-limited cryptographic tokens (expires in 60 minutes) hashed in the `password_reset_tokens` table.

### A08: Software and Data Integrity Failures (CSRF)
* **Threat**: Cross-Site Request Forgery executing unauthorized actions on behalf of a logged-in user.
* **Mitigations Implemented**:
  - **Mandatory CSRF Tokens**: All state-changing `POST`, `PUT`, `PATCH`, and `DELETE` routes require `@csrf` verification via `VerifyCsrfToken` middleware.
  - Livewire requests carry cryptographically validated checksum signatures on every component payload.

### A09: Security Logging and Monitoring Failures
* **Threat**: Attackers probing systems without detection or paper trail.
* **Mitigations Implemented**:
  - Centralized application logging (`storage/logs/laravel.log`) capturing critical exceptions and mail transport failures.
  - Invoice reminders, payment state shifts, and admin subscription renewals log actor ID, workspace ID, and timestamp.

### A10: Server-Side Request Forgery (SSRF) & Safe Webhooks
* **Threat**: Forcing the server to make requests to internal network services (`http://169.254.169.254` or `http://localhost`).
* **Mitigations Implemented**:
  - SMTP testing only connects to user-defined public mail hosts with standard port verification (465/587).
  - Outgoing mail connections validate hostnames and avoid private IP CIDR ranges (`127.0.0.1`, `10.0.0.0/8`, `192.168.0.0/16`).

---

## 3. Penetration Testing Test Cases

Below is the verified test suite for QA and security audits.

### Test Suite 1: Authentication & Session Security

| Test ID | Test Scenario | Payload / Step | Expected Result | Status |
| :--- | :--- | :--- | :--- | :--- |
| **SEC-AUTH-01** | Brute force password guessing | 6 rapid incorrect logins on `/login` | `429 Too Many Requests` triggered. Account temporarily locked for 60 seconds. | **PASS** |
| **SEC-AUTH-02** | Credential pre-fill leakage | Inspect DOM on `/login` | Input fields have empty `value=""`, `autocomplete="off"` or standard autocomplete. | **PASS** |
| **SEC-AUTH-03** | Password reset token reuse | Use expired or already-used reset token | Token rejected with "Invalid or expired token" alert. | **PASS** |
| **SEC-AUTH-04** | Session Fixation attack | Capture session cookie before and after login | Session ID changes immediately upon successful authentication. | **PASS** |
| **SEC-AUTH-05** | Weak password registration | Attempt registering with `123456` | Form validation rejects with error: Password requires >= 8 characters. | **PASS** |

### Test Suite 2: Multi-Tenant Isolation (IDOR)

| Test ID | Test Scenario | Payload / Step | Expected Result | Status |
| :--- | :--- | :--- | :--- | :--- |
| **SEC-IDOR-01** | Cross-tenant invoice view | Login as Tenant B; request `/invoices/{tenant_a_uuid}` | `404 Not Found` or `403 Forbidden`. No data leaked. | **PASS** |
| **SEC-IDOR-02** | Cross-tenant client edit | `POST /clients/{tenant_a_client_id}` from Tenant B | `404 Not Found`. Update blocked by workspace scope. | **PASS** |
| **SEC-IDOR-03** | Cross-tenant reminder trigger | `POST /invoices/{tenant_a_uuid}/reminder` from Tenant B | Action blocked; reminder aborted. | **PASS** |
| **SEC-IDOR-04** | Super Admin access privilege | Regular user navigating to `/admin/subscriptions` | Access blocked or redirected; non-owners cannot alter subscriptions. | **PASS** |

### Test Suite 3: SQL Injection (SQLi)

| Test ID | Test Scenario | Payload / Step | Expected Result | Status |
| :--- | :--- | :--- | :--- | :--- |
| **SEC-SQLI-01** | Client search parameter injection | Search query: `test' UNION SELECT id, password FROM users --` | Input treated as literal string; zero matching results; no SQL error. | **PASS** |
| **SEC-SQLI-02** | Invoice sorting injection | `GET /invoices?sort=id; DROP TABLE invoices;--` | Ignored or filtered by allowed columns list. | **PASS** |
| **SEC-SQLI-03** | Line item price parameter | Price input: `100' OR '1'='1` | Validation error: `price must be a valid number`. | **PASS** |

### Test Suite 4: Cross-Site Scripting (XSS)

| Test ID | Test Scenario | Payload / Step | Expected Result | Status |
| :--- | :--- | :--- | :--- | :--- |
| **SEC-XSS-01** | Stored XSS in Client Name | `<script>alert('XSS')</script> Corp` | Output sanitized via `htmlspecialchars` in HTML views and PDF generator. No script execution. | **PASS** |
| **SEC-XSS-02** | Stored XSS in Invoice Notes | `<img src=x onerror=alert(document.cookie)>` | Rendered as escaped text `&lt;img src=x...&gt;`. | **PASS** |
| **SEC-XSS-03** | CMS HTML Content Rendering | CMS Page body with malicious event handlers | Rendered safely or purged before display. | **PASS** |

### Test Suite 5: Cross-Site Request Forgery (CSRF)

| Test ID | Test Scenario | Payload / Step | Expected Result | Status |
| :--- | :--- | :--- | :--- | :--- |
| **SEC-CSRF-01** | Third-party form submission | External HTML submitting `POST` to `/invoices` | `419 Page Expired` (CSRF token missing or invalid). | **PASS** |
| **SEC-CSRF-02** | Delete client without token | Direct `DELETE /clients/1` without `X-CSRF-TOKEN` | `419 Page Expired`. Record remains untouched. | **PASS** |

### Test Suite 6: File & Information Disclosure

| Test ID | Test Scenario | Payload / Step | Expected Result | Status |
| :--- | :--- | :--- | :--- | :--- |
| **SEC-INFO-01** | Direct HTTP access to `.env` | `GET https://redmapleagency.com/.env` | `403 Forbidden` or `404 Not Found`. Contents never served. | **PASS** |
| **SEC-INFO-02** | Direct access to storage logs | `GET https://redmapleagency.com/storage/logs/laravel.log` | `403 Forbidden`. Core directory outside web root. | **PASS** |
| **SEC-INFO-03** | Uncaught exception handling | Trigger an erroneous route | Production displays custom branded error page, never exposing database credentials. | **PASS** |

---

## 4. Production Hardening Checklist for Hostinger

- [x] **Core Directory Isolation**: Laravel core placed in `~/domains/redmapleagency.com/redmaple_core` outside `public_html`.
- [x] **File Permissions**:
  - Directories: `755`
  - PHP & Config files: `644`
  - `storage/` and `bootstrap/cache/`: `775`
- [x] **Environment Variables**:
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - `APP_KEY` securely generated and populated.
- [x] **Database Security**:
  - Database user restricted to `localhost`.
  - Database password uses high-entropy alphanumeric and special characters.
- [x] **Mail Security**:
  - Tenant outgoing SMTP credentials wrapped in TLS/SSL.
  - Failures caught gracefully without exposing server internals.
