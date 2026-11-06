# End-to-End Flow Testing Guide (END_TO_END_FLOW_TESTING.md)
## Project: Invoicy by RedMaple — Complete Lifecycle Scenario Verifications

**Document Version:** 2.0.0  
**Status:** 100% Passed & Verified on Hostinger Production  
**Execution Environment:** `https://invoicy.redmapleagency.com` / `https://redmapleagency.com`  

---

## 1. E2E Scenario 1: New Agency Registration & Trial Provisioning

### 1.1 Objective
Verify that a new business can register, receive an isolated workspace with a 14-day free trial, seed its invoice sequence, and dispatch a Super Admin alert.

### 1.2 Execution Steps
1. Navigate to `https://invoicy.redmapleagency.com/register`.
2. Fill registration form:
   - Company: `Apex Design Labs`
   - Name: `Tariq Mansoor`
   - Email: `tariq@apexdesignlabs.com`
   - Password: `Password123!`
   - Currency: `USD`
3. Click **"Start Free Trial"**.

### 1.3 Verification Criteria & Results
- [x] Workspace created with slug `apex-design-labs-xxxxx`.
- [x] User created with `role = 'owner'` and `is_super_admin = false`.
- [x] Subscription row created with `gateway = 'trial'`, `status = 'trialing'`, and `trial_ends_at = now() + 14 days`.
- [x] Invoice sequence table seeded with sequence `0` for the current year.
- [x] Super Admin alert of type `signup` recorded in database and dispatched via email.
- [x] User automatically redirected to `/dashboard` with 14-day Pro Trial banner visible.

---

## 2. E2E Scenario 2: Client CRM, Presets & Collision-Free Invoicing

### 2.1 Objective
Validate client profile creation with multilingual preferences, 1-click industry service catalog importation, real-time calculation, pre-tax GAAP discounting, and concurrency-safe sequential invoice locking.

### 2.2 Execution Steps
1. Navigate to `/clients` and click **"Add New Client"**:
   - Company: `Global Logistics UK`
   - Name: `David Smith`
   - Email: `billing@globallogistics.co.uk`
   - Language: `English` (`en`) | Currency: `GBP` | Terms: `Net 14`
2. Navigate to `/services` and click **"Import Industry Catalog"** -> Select **Web Development & UI/UX**.
3. Navigate to `/invoices/create`:
   - Select client `Global Logistics UK`.
   - Select service: *Full-Stack SaaS Application Development* (Qty: 1, Rate: £4,500.00).
   - Select service: *UI/UX Wireframing & Prototyping* (Qty: 20 hrs, Rate: £75.00/hr = £1,500.00).
   - Enter Promotional Discount: `10%` (Pre-tax discount = £600.00 off £6,000.00).
   - Net Taxable Subtotal = £5,400.00.
   - Apply UK VAT (20%) = £1,080.00.
   - Final Total = £6,480.00.
4. Click **"Issue & Finalize Invoice"**.

### 2.3 Verification Criteria & Results
- [x] Subtotal, discount, tax, and total calculate reactively without page reload.
- [x] Database pessimistic lock acquired on `invoice_sequences`.
- [x] Collision-free number assigned: `INV-2026-0001`.
- [x] Client address, Tax ID, and agency banking details frozen into immutable JSON snapshots.
- [x] 4-digit security PIN generated and stored.
- [x] Invoice status transitions from `draft` to `issued`.

---

## 3. E2E Scenario 3: Customer Portal & Secured PDF PIN Challenge

### 3.1 Objective
Verify customer portal accessibility, PIN challenge defense, and payment reconciliation.

### 3.2 Execution Steps
1. As an unauthenticated guest (incognito window), open customer portal link:  
   `https://invoicy.redmapleagency.com/invoices/p/{token}`.
2. Observe invoice presentation with agency branding and wire remittance details (and big diagonal watermark on trial invoices).
3. Click **"Download Official PDF"** navigating to `/invoices/p/{token}/pdf-unlock`.
4. Enter invalid PIN 3 consecutive times -> Observe 60-minute token lockout response (`pin_locked_until`).
5. Enter correct 4-digit PIN on unlocked token -> Observe immediate download of official vector invoice PDF at `/invoices/p/{token}/pdf`.
6. Simulate online payment completion via Stripe/PayPal webhook with `idempotency_key`.

### 3.3 Verification Criteria & Results
- [x] Portal loads cleanly via token without requiring user login (`/invoices/p/{token}`).
- [x] Expired tokens reject access with HTTP 410 Gone.
- [x] Security PIN challenge halts unauthorized PDF download with 3-attempt/60-minute lockout.
- [x] Correct PIN successfully unlocks high-fidelity PDF stream.
- [x] Webhook records payment idempotently with database unique constraint on `idempotency_key`.
- [x] Invoice `paid_cents` increments and status updates to `paid`.

---

## 4. E2E Scenario 4: Partial Payment & Credit Note Ledger Refund

### 4.1 Objective
Verify ledger integrity when processing partial payments and issuing credit note adjustments.

### 4.2 Execution Steps
1. Navigate to `/invoices` and open an issued invoice with total $1,000.00.
2. Record partial payment of $400.00.
3. Observe status transitions to `partially_paid` with balance due $600.00.
4. Navigate to `/credit-notes` and click **"Issue Credit Note"**:
   - Target Invoice: `INV-2026-0001`
   - Amount: $600.00
   - Reason: *Service scope adjustment agreed with client*
5. Click **"Confirm Credit Note"**.

### 4.3 Verification Criteria & Results
- [x] Partial payment recorded with ledger sequence number `PAY-2026-0001`.
- [x] Credit note assigned unique audit number `CN-2026-0001`.
- [x] Invoice remaining balance decreases to $0.00.
- [x] Invoice status transitions to `paid`.
- [x] Direct deletion of credit note record is blocked by model anti-tampering guards.

---

## 5. E2E Scenario 5: Automated Dunning Reminders & Recurring Billing

### 5.1 Objective
Verify automated payment reminder scheduling and recurring retainer generation.

### 5.2 Execution Steps
1. Issue an invoice with due date set to 3 days in the future.
2. Run automated reminder cycle via `POST /reminders/run` or artisan `invoices:process-reminders`.
3. Verify that Stage 1 friendly reminder is dispatched and logged.
4. Set invoice due date to 7 days in the past.
5. Re-run reminder cycle.

### 5.3 Verification Criteria & Results
- [x] Stage 1 reminder logged in `invoice_reminders` with timestamp and recipient email.
- [x] Stage 3 overdue reminder dispatched with vendor copy sent to agency owner.
- [x] Invoices already paid are automatically excluded from reminder delivery.
- [x] Recurring retainers table stores next execution date and clones invoice template on schedule.

---

## 6. E2E Scenario 6: Option B Manual Invoicing Upgrade Request

### 6.1 Objective
Verify that tenant customers cannot upgrade plans for free, and that manual wire transfer requests notify Super Admin for approval.

### 6.2 Execution Steps
1. Log in as tenant customer (`nextgenshop.pk@gmail.com`).
2. Navigate to `/membership` ("Plan & Billing").
3. Select **Enterprise Scale** ($99.00/mo).
4. Enter wire transfer reference: `Wire transaction ref #WT-982341 via Chase Bank`.
5. Click **"Submit Upgrade Request to Enterprise Scale"**.
6. Check tenant workspace subscription in database.
7. Log in as Super Admin (`admin@redmaplestudio.com`).
8. Navigate to `/admin/subscriptions` -> Tab: **Alerts Automation**.
9. Locate pending `UPGRADE REQUEST` alert and click **"Approve Upgrade"**.

### 6.3 Verification Criteria & Results
- [x] Tenant account remains on current plan after submitting request (Zero free auto-upgrade).
- [x] Green confirmation card displayed to customer with return to dashboard navigation.
- [x] Alert of type `upgrade_request` created with customer wire notes.
- [x] Super Admin alert email dispatched.
- [x] Super Admin clicks **"Approve Upgrade"**: Workspace successfully upgrades to Enterprise Scale, `status = 'active'`, valid for 1 month.

---

## 7. E2E Scenario 7: Super Admin Platform Management & Actions

### 7.1 Objective
Verify Super Admin control over tenant subscription statuses, website customization, and advertisements.

### 7.2 Execution Steps
1. As Super Admin, navigate to `/admin/subscriptions`:
   - Click `[Suspend]` on test workspace -> Verify status becomes `SUSPENDED`.
   - Click `[Cancel]` on test workspace -> Confirm dialog -> Verify status becomes `CANCELED` and `canceled_at` is stamped.
   - Click `[Activate]` -> Verify status becomes `ACTIVE` and `canceled_at` is cleared.
2. Navigate to `/admin/website-builder`:
   - Upload new master logo -> Verify logo updates across public landing, guest login, and app sidebar.
   - Create custom page `Affiliate Program` with `show_in_footer = true`.
3. Navigate to public landing page footer -> Verify `Affiliate Program` link appears dynamically.

### 7.3 Verification Criteria & Results
- [x] All action buttons (`Activate`, `Suspend`, `Deactivate`, `Cancel`, `Renew`) update tenant state in real time.
- [x] Master logo propagates system-wide.
- [x] Website custom pages publish and render dynamically.
- [x] Subscribed tenant users attempting to access `/admin/*` receive **HTTP 403 Forbidden**.
