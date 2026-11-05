# 360-Degree Quality Assurance Strategy (QA_STRATEGY_360.md)
## Project: Invoicy by RedMaple — Comprehensive Testing & Quality Engineering

**Document Version:** 2.0.0  
**Status:** Approved & Implemented  
**Scope:** Multi-Tenancy, Financial Engine, Security, UI/UX, and Cloud Infrastructure  

---

## 1. QA Mission & Quality Philosophy

The primary objective of the Invoicy Quality Assurance Strategy is to ensure **zero-defect financial calculations**, **impenetrable multi-tenant data isolation**, **100% collision-free sequential numbering**, and **seamless responsive usability across 8 international languages** in a shared-hosting LiteSpeed cloud environment.

```
                  ┌───────────────────────────────┐
                  │    Production Smoke & E2E     │  10%
                  ├───────────────────────────────┤
                  │   Security & Tenancy Audits   │  20%
                  ├───────────────────────────────┤
                  │ Feature & Integration Suites  │  30%
                  ├───────────────────────────────┤
                  │ Unit & Calculation Regression │  40%
                  └───────────────────────────────┘
```

---

## 2. Testing Dimensions & Methodologies

### 2.1 Multi-Tenancy & Anti-IDOR Verification
- **Objective:** Guarantee that no workspace user can ever view, mutate, or delete records of another tenant.
- **Methodology:**
  1. Authenticate as Tenant A (`workspace_A`).
  2. Attempt direct API / POST / Livewire mutations targeting Tenant B's UUIDs (`invoice_id`, `client_id`, `service_id`).
  3. Verify that `WorkspaceScope` automatically filters queries and `BelongsToWorkspace` halts execution with HTTP 403.
  4. Verify that Super Admin privilege isolation blocks non-super-admins from accessing `/admin/*`.

### 2.2 Financial Calculation & Rounding Precision
- **Objective:** Validate that all tax, discount, line-item, and ledger calculations are mathematically exact.
- **Methodology:**
  1. Test complex fractional quantities (e.g. 17.5 hours @ $65.25/hr).
  2. Validate pre-tax percentage discounts (e.g. 15% discount on $1,245.50 subtotal) before applying multi-rate taxes.
  3. Validate pre-tax fixed discounts (e.g. $100 flat discount).
  4. Ensure integer cent math rule is strictly maintained: no floating-point arithmetic errors.

### 2.3 Concurrency & Race Condition Stress Testing
- **Objective:** Eliminate sequence collisions and duplicate coupon redemptions during high-traffic spikes.
- **Methodology:**
  1. Fire 25 simultaneous concurrent HTTP threads executing `generateNextInvoiceNumber()` for the same workspace.
  2. Verify that database pessimistic row locking (`SELECT ... FOR UPDATE`) forces sequential queueing.
  3. Assert that resulting invoice numbers are 100% unique, consecutive, and collision-free (`INV-2026-0001` through `INV-2026-0025`).
  4. Fire simultaneous checkout requests using a coupon with `max_redemptions = 1` and verify exactly one succeeds.

### 2.4 Localization, Typography & RTL Testing
- **Objective:** Ensure flawless visual and functional behavior across all 8 supported languages.
- **Methodology:**
  1. Validate Left-to-Right languages: English, Hindi, Turkish, Spanish, French, German, Chinese.
  2. Validate Right-to-Left (RTL) languages: Urdu (`ur`) and Arabic (`ar`).
  3. Verify `[dir="rtl"]` flips sidebar dock, text alignment, and directional navigation arrows.
  4. Verify that numbers, currency symbols, and invoice codes retain readable LTR alignment (`ltr-text`).

### 2.5 Shared-Hosting & Web Server Infrastructure Testing
- **Objective:** Validate performance and security boundaries under LiteSpeed Web Server on Hostinger.
- **Methodology:**
  1. Verify `.env`, `.git`, and private storage paths return `403 Forbidden` over direct HTTP.
  2. Verify that authenticated user sessions and CSRF tokens bypass LiteSpeed server-level page caching.
  3. Verify that zero-node fallback works seamlessly when executing server-side rendering.

---

## 3. Bug Classification & Severity Matrix

| Severity Level | Definition | SLA / Resolution Requirement |
| :--- | :--- | :--- |
| **P0 — Blocker** | Cross-tenant data leak, invoice sequence collision, payment calculation error, or 500 crash on core flow. | Immediate hotfix (< 2 hours). Halts all releases. |
| **P1 — Critical** | Super admin privilege bypass, PDF generation failure, failed email dispatch without fallback, or broken checkout. | Resolved within 6 hours. |
| **P2 — Major** | Layout defect in RTL language, broken filter on invoices table, or non-functional reminder schedule. | Resolved within 24 hours. |
| **P3 — Minor** | Typo in documentation article, minor badge color mismatch, or cosmetic padding discrepancy. | Scheduled in next sprint release. |

---

## 4. Test Execution & Reporting Pipeline

1. **Automated Unit & Feature Suite:** Executed via PHPUnit / Pest tests before every production deployment.
2. **End-to-End Simulation Scripts:** Dedicated Python verification scripts simulating real browser user agents, session cookies, and multi-tenant flows.
3. **Database Audit Script:** Direct PDO verification scripts inspecting database state, foreign key integrity, and column attributes.
4. **Production Sign-Off Checklist:** Formal review of caches, migrations, and access logs before closing deployment tickets.
