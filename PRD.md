# Product Requirements Document (PRD)
## Project: Invoicy by RedMaple — Commercial Multi-Tenant Invoicing SaaS

**Document Version:** 2.0.0  
**Status:** Approved & Live in Production  
**Target Platform:** Hostinger Web Hosting & Cloud Architecture  
**Author:** RedMaple Engineering & Product Strategy Team  

---

## 1. Executive Summary & Product Vision

### 1.1 Vision Statement
**Invoicy by RedMaple** is a high-performance, multi-tenant commercial SaaS invoicing and agency billing platform designed specifically for digital agencies, creative studios, IT consultancies, and freelance professionals. The platform eliminates billing complexity by unifying client CRM, multi-currency invoicing, pre-tax international discounting, sequential concurrency locking, encrypted PDF PIN delivery, credit notes, automated dunning reminders, and retainer scheduling into a streamlined, high-speed interface.

### 1.2 Core Value Propositions
1. **Zero-Latency Invoicing:** Reactive real-time calculation engine with zero client-side lag.
2. **Audit-Compliant Numbering:** Concurrency-safe, collision-free sequential numbering (`INV-YYYY-XXXX`) guaranteed across parallel transactions.
3. **Global Multi-Lingual & Multi-Currency Billing:** Native support for 8+ international languages, including Right-to-Left (RTL) for Arabic and Urdu, and country-specific tax engines (VAT, Sales Tax, GST).
4. **Bank Remittance & Custom Gateway Ready:** Tailored for both modern electronic payment links (Stripe/PayPal) and traditional corporate wire transfer / bank remittances.
5. **Full Whitelabel & Custom Website CMS:** Agencies can white-label their PDFs, portal links, and customer communications, while platform admins configure landing pages, footer navigation, and sponsors.

---

## 2. Target Personas & Stakeholders

| Persona | Role / Description | Primary Needs & Pain Points |
| :--- | :--- | :--- |
| **Agency Owner / Director** | Proprietor of a creative, media, or tech agency | Needs revenue tracking, multi-client retainers, whitelabeled branding, and zero data leaks across staff or tenants. |
| **Finance / Billing Staff** | Staff accountant or operations manager | Needs fast invoice drafting, automated sequential numbering, pre-tax discounting, credit note issuing, and payment reconciliation. |
| **End Client / Customer** | Client receiving invoices for agency services | Needs instant invoice viewing, PIN-protected PDF downloads, clear bank remittance details, and friction-free payment links. |
| **Platform Super Admin** | Operator of the Invoicy SaaS platform | Needs centralized tenant subscription control, plan configuration, coupon management, website CMS, and subscription upgrade alerts. |

---

## 3. Business Model & Subscription Plans

Invoicy operates on a tiered subscription model backed by atomic coupons and an offline wire / invoice verification workflow:

| Plan Tier | Monthly Price | Invoice Limit | Client Limit | Team Seats | Key Features & Entitlements |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **14-Day Free Trial** | $0.00 | 15 | 10 | 1 | Full access trial, standard multi-currency, PIN-protected PDFs, basic email dispatch. |
| **Starter Agency** | $19.00 / mo | 60 | 35 | 2 | 60 monthly invoices, 35 saved clients, all 8 languages & RTL, industry presets, payment links. |
| **Pro Business** | $49.00 / mo | Unlimited | Unlimited | 5 | Unlimited invoices & clients, credit notes, recurring retainers, whitelabel colors, custom SMTP. |
| **Enterprise Scale** | $99.00 / mo | Unlimited | Unlimited | 15 | 100% white-labeling, custom domain mapping, 15 team seats, priority email delivery, raw CSV export. |

---

## 4. Functional Requirements by Domain

### 4.1 Multi-Tenancy & Access Control
- **FR-TEN-01:** Every user belongs to an isolated agency workspace (`WorkspaceScope`).
- **FR-TEN-02:** Anti-IDOR enforcement must block any attempt by workspace users to access, update, or delete records belonging to another tenant.
- **FR-TEN-03:** Strict separation of duties between Tenant Workspace Owners (`role = 'owner'`) and Platform Super Administrators (`is_super_admin = true`). Platform admin modules (`/admin/*`) are completely invisible and inaccessible (HTTP 403) to tenant owners.

### 4.2 Client CRM & Internationalization
- **FR-CRM-01:** Workspace owners can manage unlimited clients with company name, email, tax registration ID, phone, and complete billing address.
- **FR-CRM-02:** Each client profile stores default payment terms (`due_on_receipt`, `net_7`, `net_14`, `net_30`, `net_60`), currency preference, and language preference.
- **FR-CRM-03:** The application supports English, Urdu (RTL), Arabic (RTL), Hindi, Turkish, Spanish, French, German, and Chinese, dynamically adapting typography and reading direction.

### 4.3 Invoicing Engine & Sequential Locking
- **FR-INV-01:** Sequential invoice numbers must follow the format `INV-{YEAR}-{SEQUENCE}` (e.g. `INV-2026-0001`) with automatic rollover every calendar year.
- **FR-INV-02:** Number generation must use pessimistic database row locking (`SELECT ... FOR UPDATE`) to eliminate race conditions and collisions during simultaneous creation.
- **FR-INV-03:** Immutable historical snapshots: Changing a client's address or tax ID in CRM must not alter past issued invoices.
- **FR-INV-04:** Pre-tax discount calculation adhering strictly to GAAP and IFRS standards (Subtotal - Discount + Tax = Total).
- **FR-INV-05:** Status lifecycle: `draft` -> `issued` -> `partially_paid` -> `paid`, with automatic recalculation of balances on payment or credit note allocation.

### 4.4 PDF Security & Client Portal
- **FR-PDF-01:** Customer portal URL provides instant online invoice review without requiring registration.
- **FR-PDF-02:** Direct PDF downloads can be secured via a 4-digit PIN code challenge derived from the client's phone or custom security PIN.

### 4.5 Credit Notes & Ledger Adjustments
- **FR-CRN-01:** Ability to issue partial or full credit notes against existing invoices with unique sequence `CN-{YEAR}-{SEQUENCE}`.
- **FR-CRN-02:** Issuing a credit note automatically adjusts invoice `due_cents` and updates invoice status to `paid` if balance reaches $0.00.
- **FR-CRN-03:** Strict audit compliance: Issued credit notes cannot be arbitrarily deleted.

### 4.6 Automated Payment Reminders & Recurring Retainers
- **FR-REM-01:** Configurable automated payment reminder schedules (3 days before due, on due date, 7 days overdue).
- **FR-REM-02:** Automatic delivery of reminder emails with vendor copies and logging in `invoice_reminders`.
- **FR-REC-01:** Recurring retainer engine that automatically schedules and clones invoices on monthly, quarterly, or annual intervals.

### 4.7 Super Admin & Platform Management
- **FR-ADM-01:** Global subscription control panel to view all tenant workspaces, assigned plans, status badges, and expiration dates.
- **FR-ADM-02:** Ability to manually Activate, Suspend, Deactivate, or Cancel tenant subscriptions with instant reflection.
- **FR-ADM-03:** Plan rules and feature limits editor for Free Trial, Starter, Pro, and Enterprise tiers.
- **FR-ADM-04:** Automated notification engine for new user signups, plan upgrades, downgrades, and trial expirations.
- **FR-ADM-05:** Website Builder for managing master logos, favicon, navigation menus, custom landing pages, and footer links.
- **FR-ADM-06:** Advertisement engine supporting image banners, custom HTML widgets, and text ads across public and app pages.

---

## 5. Non-Functional Requirements (NFR)

- **NFR-PERF:** Page load and Livewire reactivity response time < 350ms on shared hosting.
- **NFR-SEC:** All input sanitized, CSRF protection on all forms, passwords hashed via Bcrypt (cost 12), encrypted tenant payment keys.
- **NFR-REL:** 99.9% uptime on Hostinger LiteSpeed infrastructure; zero data loss during concurrent operations.
- **NFR-COMP:** Fully responsive across Mobile (360px+), Tablet (768px+), and Desktop (1024px+).

---

## 6. Success Metrics & KPIs

1. **Transaction Accuracy:** 100% collision-free sequential numbering across all workspaces.
2. **Billing Integrity:** Zero rounding errors in multi-tax or discount calculations (calculated in integer cents).
3. **Tenant Isolation:** Zero cross-tenant data leaks during security and penetration testing.
4. **Delivery Rate:** 99.5%+ email delivery success rate via tenant-configured custom SMTP.
