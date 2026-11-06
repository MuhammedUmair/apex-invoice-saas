# Application Flow & User Journeys (APPFLOW.md)
## Project: Invoicy by RedMaple — Commercial Multi-Tenant SaaS

**Document Version:** 2.0.0  
**Status:** Approved & Live in Production  
**Target Audience:** Product Managers, Developers, QA Engineers, Support Ops  

---

## 1. Global Navigation & User Flow Map

```
[Public Visitor] ──> Landing Page (/) ──> Custom Pages (/p/{slug}) ──> Docs Hub (/docs)
                            │
                            ├──> [Register] ──> 14-Day Free Trial Provisioned ──> [Agency Dashboard]
                            │
                            └──> [Login] ──────────────────────────────────────────┘
                                                              │
   ┌──────────────────────────────────────────────────────────┴────────────────────────────────────────────────────────┐
   │                                              TENANT WORKSPACE                                                     │
   │  Dashboard ──> Invoices ──> Recurring ──> Credit Notes ──> Reminders ──> Clients ──> Services ──> Plan & Billing │
   └──────────────────────────────────────────────────────────┬────────────────────────────────────────────────────────┘
                                                              │ (If is_super_admin == true)
   ┌──────────────────────────────────────────────────────────▼────────────────────────────────────────────────────────┐
   │                                           PLATFORM SUPER ADMIN                                                    │
   │  Website Builder ──> Advertisement Manager ──> Website CMS ──> Subscriptions & Plans ──> Coupons & Offers        │
   └───────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Journey 1: Guest Experience, Public Pages & Documentation

1. **Homepage Landing (`/`)**:
   - Visitor arrives on `https://redmapleagency.com`.
   - The landing page presents the platform hero, feature breakdown, live pricing table (Free Trial, Starter Agency, Pro Business, Enterprise Scale), testimonials, and dynamic footer navigation.
   - Header contains the dynamic master logo, main navigation links, and **"Sign In"** / **"Start 14-Day Free Trial"** action buttons.
2. **Public Custom Pages (`/p/{slug}`)**:
   - Visitors can navigate to custom pages configured via Website Builder (e.g. Terms of Service, Privacy Policy, Enterprise SLA, Affiliate Program).
   - If a page has `show_in_footer = true`, it is dynamically linked in the landing and guest layout footer.
3. **Multilingual Documentation Hub (`/docs`)**:
   - Visitors and users can browse the documentation manual in 5 languages (English, Urdu, Arabic, Hindi, Turkish).
   - Articles cover tenancy isolation, client CRM, invoice sequential numbering, tax models, PDF PIN security, credit notes, and webhooks.

---

## 3. Journey 2: Agency Registration & 14-Day Pro Trial Onboarding

1. **User Registration Form (`/register`)**:
   - User provides: Agency Company Name, Full Name, Work Email, Password, Country Code, and Default Currency (`USD`, `GBP`, `EUR`, `AED`, `PKR`, etc.).
2. **Atomic Provisioning Pipeline**:
   - Database creates `Workspace` with unique URL slug (e.g., `next-gen-shop-zcfsu`).
   - Database creates `User` with `role = 'owner'` and `is_super_admin = false`.
   - Automatically provisions a `Subscription` with `status = 'trialing'`, `gateway = 'trial'`, and `trial_ends_at = now()->addDays(14)`.
   - Automatically seeds default invoice sequence row for the current calendar year.
3. **Admin Signup Alert**:
   - System dispatches a `signup` alert to the Super Admin alert inbox and sends an automated notification email.
4. **Immediate Redirection**:
   - User is authenticated and redirected directly into their private **Agency Dashboard** with an active 14-day trial banner.

---

## 4. Journey 3: Workspace Setup & Industry Catalog Import

1. **Agency Settings (`/settings`)**:
   - Owner configures legal business address, phone, contact email, and Tax/VAT ID.
   - Configures Bank Wire Remittance Details: Bank Name, Account/IBAN, Routing Number, SWIFT/BIC code.
   - Sets primary and secondary brand colors (with live hex color picker).
   - Uploads agency logo (propagates across PDFs, portal, and sidebar).
2. **Industry Presets Importer (`/services`)**:
   - Agency visits the Service Catalog.
   - Can add individual custom line items OR click **"Import Industry Catalog"**.
   - Available pre-built presets:
     - *Web Development & UI/UX Design*
     - *Social Media Marketing (SMM) & Content*
     - *Custom Apparel, Embroidery & Vector Digitizing*
     - *Branding & Graphic Design Studio*
     - *Business Strategy & IT Consulting*
   - Importing populates standard service names, description templates, units (Hour, Project, 1000 Stitches, Month), and default rates.

---

## 5. Journey 4: Client CRM Management

1. **Client Directory (`/clients`)**:
   - Owner/Staff creates new client profile.
   - Fills in Company Name, Primary Contact, Email, Phone, and Tax Registration ID.
   - Sets **Default Payment Terms**: `Due on Receipt`, `Net 7`, `Net 14`, `Net 30`, or `Net 60`.
   - Sets **Preferred Language**: English, Urdu (RTL), Arabic (RTL), Hindi, Turkish, Spanish, French, German, Chinese.
   - Sets **Preferred Currency** and **Tax-Exempt Status**.
2. **Audit History**:
   - Each client card shows total invoices issued, lifetime paid amount, and outstanding balance.

---

## 6. Journey 5: Reactive Invoicing & Sequential Locking

1. **Invoice Builder (`/invoices/create`)**:
   - Livewire component initializes with today's date and default payment terms.
   - User selects Client: System auto-populates currency, tax rate, language, and due date based on payment terms.
2. **Adding Line Items**:
   - Items can be typed manually or auto-selected from the Services Catalog.
   - As quantities, rates, and tax rates are modified, Subtotal, Discount, Tax, and Total calculate in real time.
3. **Pre-Tax Discount Deduction**:
   - User can enter percentage or fixed discount. Deducted strictly before tax (GAAP compliance).
4. **Finalizing & Issuing**:
   - Clicking **"Issue & Finalize Invoice"** triggers `InvoiceNumberService`.
   - Acquires pessimistic DB row lock on `invoice_sequences` for the workspace.
   - Generates next collision-free number: `INV-2026-0001`.
   - Freezes client and workspace snapshots into JSON fields.
   - Generates random 4-digit security PIN.
   - Status changes from `draft` to `issued`.
5. **Dispatching to Client**:
   - Can send invoice directly to client email via custom or system SMTP with PDF link.

---

## 7. Journey 6: Customer Portal & PDF PIN Security

1. **Customer Access (`/invoices/p/{token}`)**:
   - Client clicks unique secure link received via email containing a 64-character unguessable cryptographic token.
   - Token validity is enforced by `portal_token_expires_at` (returns HTTP 410 Gone once expired).
   - Portal displays modern responsive invoice view with agency branding and status badge. Trial invoices render a prominent diagonal watermark.
2. **Payment Remittance**:
   - Client views Agency Bank Details (IBAN, Swift, Account #) for direct wire transfer.
   - If agency enabled Stripe/PayPal, client clicks **"Pay Now"** link to pay online via idempotent ledger processing.
3. **PIN-Protected PDF Download (`/invoices/p/{token}/pdf-unlock`)**:
   - Clicking **"Download Official PDF"** prompts for PIN verification if enabled.
   - Throttled per token: 3 incorrect PIN attempts triggers an automatic 60-minute lockout on the token (`pin_locked_until`).
   - Entering correct PIN unlocks immediate vector PDF download at `/invoices/p/{token}/pdf`.

---

## 8. Journey 7: Credit Notes & Refund Issuance

1. **Credit Note Generation (`/credit-notes`)**:
   - In case of dispute, cancellation, or partial refund, agency selects target invoice.
   - Enters refund amount (cents) and formal legal reason.
2. **Ledger Adjustment**:
   - Generates unique sequence `CN-2026-0001`.
   - Decrements invoice `due_cents`. If due balance reaches $0.00, invoice status becomes `paid`.
   - Creates immutable credit note record; deletion is blocked to preserve audit trail.

---

## 9. Journey 8: Tenant Membership Upgrade (Option B Manual Flow)

1. **Plan & Billing (`/membership`)**:
   - Tenant reviews pricing packages: Starter ($19/mo), Pro ($49/mo), Enterprise ($99/mo).
   - Active package is indicated with an "Active Plan" badge.
2. **Selecting New Package**:
   - Tenant selects target plan (e.g. Enterprise Scale).
   - Order summary updates showing price and optional coupon code input.
3. **Offline Wire Transfer Instructions**:
   - System displays corporate bank wire remittance details and a **Payment Reference / Wire Notes** text input.
   - Tenant enters their wire transaction reference or purchase order number.
4. **Submitting Upgrade Request**:
   - Tenant clicks **"Submit Upgrade Request to [Plan]"**.
   - **Crucial Security Rule:** The account is NOT automatically upgraded for free.
   - System dispatches an `upgrade_request` alert to the Super Admin alert queue.
   - Tenant is shown a green confirmation card: *"Upgrade Request Submitted! Our billing department will verify your wire transfer before activating your account."*

---

## 10. Journey 9: Platform Super Admin Control

1. **Access Gate**:
   - Restricted strictly to users with `is_super_admin = true`.
2. **Subscriptions & Plans Manager (`/admin/subscriptions`)**:
   - **Tab 1: Tenants:** Displays all workspaces, owners, plans, status badges, and expiration dates.
   - Action buttons:
     - `[Renew / Change]`: Modal to assign any plan for 1, 3, 6, or 12 months.
     - `[Activate]`: Instantly sets status to `active` and extends period by 1 month.
     - `[Suspend]`: Instantly sets status to `suspended` (blocks tenant invoicing).
     - `[Deactivate]`: Instantly sets status to `inactive`.
     - `[Cancel]`: Sets status to `canceled` and records timestamp in `canceled_at`.
     - Edit Owner Contact icon: Updates owner name and email.
   - **Tab 2: Plan Configuration:** Edit prices, limits, seats, and bullet points.
   - **Tab 3: Alerts Automation:** View real-time alert log. For pending `UPGRADE REQUEST` alerts, displays a 1-click **"Approve Upgrade"** button to activate the plan once wire payment is confirmed.
3. **Website Builder (`/admin/website-builder`)**:
   - Master Logo & Favicon customizer.
   - Header navigation re-ordering.
   - Custom landing pages with `show_in_footer` toggle.
4. **Advertisement Management (`/admin/advertisements`)**:
   - Create and schedule Image banners, HTML ad code, and Text ads across the app.
