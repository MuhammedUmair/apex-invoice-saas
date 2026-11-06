# Implementation & Engineering Roadmap (IMPLEMENTATION_PLAN.md)
## Project: Invoicy by RedMaple — Comprehensive Architecture & Development Plan

**Document Version:** 2.0.0  
**Status:** Successfully Implemented & Deployed on Production  
**Execution Environment:** Hostinger Enterprise Cloud Infrastructure  

---

## 1. Execution Roadmap Overview

```
Phase 1: Tenancy & Auth ──> Phase 2: CRM & Catalog ──> Phase 3: Invoicing Engine
                                                                 │
Phase 6: CMS & Website Builder <── Phase 5: Reminders & Retainers <── Phase 4: PDF & Payments
         │
         └──> Phase 7: Super Admin Isolation ──> Phase 8: Option B Billing ──> Phase 9: Hostinger Prod
```

---

## 2. Phase-by-Phase Implementation Breakdown

### Phase 1: Core Foundation & Multi-Tenancy Engine
- **Objective:** Establish isolated workspace partitioning, database migrations, and authentication.
- **Key Deliverables:**
  - `workspaces` and `users` migrations with UUID v4 primary keys.
  - `WorkspaceScope` applying automatic tenant isolation at Eloquent driver level.
  - `BelongsToWorkspace` anti-IDOR trait intercepting create, update, and delete events.
  - User registration and login flows with Bcrypt password hashing and session regeneration.
- **Status:** **Completed & Verified**

### Phase 2: Client CRM & Industry Service Presets
- **Objective:** Provide robust customer directory management and 1-click industry service catalog importation.
- **Key Deliverables:**
  - `clients` table supporting company name, VAT/Tax ID, address, preferred currency, and language preference.
  - Multilingual support for English, Urdu (RTL), Arabic (RTL), Hindi, Turkish, Spanish, French, German, and Chinese.
  - Industry presets seeder for 5 sectors: Web Development, SMM, Custom Apparel/Digitizing, Branding, and IT Consulting.
  - Livewire `ClientDirectory` and `ServiceCatalog` reactive components.
- **Status:** **Completed & Verified**

### Phase 3: Reactive Invoicing Engine & Sequential Locking
- **Objective:** Build a collision-free, concurrency-safe invoice generation system with live calculation.
- **Key Deliverables:**
  - Livewire `InvoiceBuilder` with real-time reactive subtotal, pre-tax discount, and tax computation.
  - `InvoiceNumberService` with pessimistic DB row locking (`SELECT ... FOR UPDATE`) on `invoice_sequences` table.
  - Format: `INV-{YEAR}-{SEQUENCE:04d}` with annual calendar rollover.
  - Frozen historical JSON snapshots for both client address and workspace profile.
  - GAAP/IFRS pre-tax discount deduction logic in `TaxCalculationService`.
- **Status:** **Completed & Verified**

### Phase 4: PDF PIN Security, Customer Portal & Gateways
- **Objective:** Client-facing invoice portal, secure PDF access challenge, and payment reconciliation.
- **Key Deliverables:**
  - Public customer portal route (`/invoices/p/{token}`) accessible via 64-char expiring token.
  - 4-digit PIN security challenge with 3-attempt/60-min lockout (`/invoices/p/{token}/pdf-unlock`).
  - Bank wire transfer remittance details rendered on invoices and portal.
  - Dual payment gateway integration (Stripe & PayPal) with webhook handler in `WebhookController`.
  - Idempotent transaction ledger recording in `PaymentReconciliationService`.
- **Status:** **Completed & Verified**

### Phase 5: Credit Notes, Reminders & Recurring Billing
- **Objective:** Complete the accounting loop with audit-safe adjustments and automated dunning.
- **Key Deliverables:**
  - `credit_notes` table and UI ledger for issuing official partial or full refunds against invoices.
  - Automatic invoice `due_cents` recalculation and status synchronization.
  - `InvoiceReminderService` executing 3-stage automated payment reminders (-3d, 0d, +7d).
  - Background artisan console command `invoices:process-reminders` with email logging.
  - Recurring invoice retainers module for automated contract renewal.
- **Status:** **Completed & Verified**

### Phase 6: Website Builder, Master Branding & Advertisement Engine
- **Objective:** Platform customization for both agency branding and public website management.
- **Key Deliverables:**
  - Unified single master logo and favicon customizer in `WebsiteBuilderController`.
  - Custom public pages module (`/p/{slug}`) with dynamic `show_in_footer` toggle.
  - Header and footer navigation link management.
  - Advertisement manager supporting Image banners, custom HTML widgets, and Text ads with impression and click tracking.
  - Multilingual documentation CMS (`/admin/cms`) with auto-translation capability.
- **Status:** **Completed & Verified**

### Phase 7: Super Admin Control & Role Isolation
- **Objective:** Strict separation between Tenant Workspace Owners and Platform Super Administrators.
- **Key Deliverables:**
  - Database migration adding `users.is_super_admin` boolean flag (`default: 0`).
  - `EnsureSuperAdmin` middleware securing all `/admin/*` routes against unauthorized tenants (HTTP 403).
  - UI sidebar segregation concealing "Platform Admin" links from normal agency owners.
  - Global tenant scope bypass on `/admin/*` to allow Super Admin oversight across all workspaces.
  - Subscriptions & Plans Manager with real-time KPI metrics and action controls (`Activate`, `Suspend`, `Deactivate`, `Cancel`).
- **Status:** **Completed & Verified**

### Phase 8: Option B Manual Invoicing & Upgrade Request Flow
- **Objective:** Eliminate mock/free auto-upgrades and implement corporate wire transfer verification.
- **Key Deliverables:**
  - Replaced immediate subscription activation in `MembershipCheckout` with manual upgrade request flow.
  - Added customer **Payment Reference / Wire Notes** text input for transaction IDs or purchase orders.
  - New `SubscriptionAlert` type `upgrade_request` with automated email dispatch to Super Admin.
  - Added 1-click **"Approve Upgrade"** button in Super Admin Alerts Automation tab to activate accounts upon wire confirmation.
  - Preserved 100% free promotional coupons (`FREE100`) as automated marketing activations.
- **Status:** **Completed & Verified**

### Phase 9: Hostinger Production Infrastructure Hardening
- **Objective:** Maximum stability, security, and performance on Hostinger LiteSpeed hosting.
- **Key Deliverables:**
  - Physical code separation: Core Laravel application in `/redmaple_core`, webroot in `/public_html`.
  - LiteSpeed server cache rules bypassing authenticated routes and session cookies.
  - Automated Python deployment bridges packaging atomic ZIP releases.
  - Route, view, config, and bootstrap caching.
- **Status:** **Completed & Verified**

---

## 3. Future Enhancement Milestones

| Milestone | Target Horizon | Planned Capability |
| :--- | :--- | :--- |
| **v2.1** | Q1 2027 | WhatsApp Business API integration for automated invoice PDF notifications. |
| **v2.2** | Q2 2027 | Native Peppol / ZATCA e-Invoicing XML compliance for Saudi Arabia and EU. |
| **v2.3** | Q3 2027 | Multi-currency automated bank feed reconciliation via Plaid / Open Banking APIs. |
| **v2.4** | Q4 2027 | Native mobile companion apps (iOS & Android) via Flutter / Capacitor. |
