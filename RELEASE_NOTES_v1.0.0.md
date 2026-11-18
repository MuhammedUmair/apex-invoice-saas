# Invoicy by RedMaple — Enterprise Release v1.0.0-stable
**Official Production Release & Restore Point**  
*Release Date: November 18, 2026*  
*Target Environments: `https://invoicy.redmapleagency.com` / `https://redmapleagency.com`*  
*Semantic Version:* `v1.0.0-stable`

---

## 1. Executive Summary
This milestone release marks the enterprise stabilization of **Invoicy by RedMaple** — a commercial multi-tenant SaaS billing, invoice lifecycle management, and financial compliance platform. Since live users are actively issuing commercial documents and processing payments, this version establishes an immutable restore point, release pipeline, and staging environment for future enhancements.

---

## 2. Release Highlights & Capabilities

### 🔐 A. Document Security, QR Verification & Authenticity
- **Authentic Verification 2D QR Code**: Every issued invoice generates a pure-PHP standard QR code encoding a tamper-evident digital verification link (`/verify/invoice/{token}`).
- **Official Authenticity Seal**: Scanning the QR code routes to an uncompromised verification portal verifying the issuer, client, amount, currency, and a unique cryptographic SHA-256 fingerprint.
- **Draft & Empty Document Shield**: Saved draft invoices and invoices with empty line items strictly disable public customer portal URLs and PDF downloads, returning a 403 Forbidden document access notice.

### ↩️ B. Invoice Pre-Dispatch Review & Recall / Pull Back
- **Pre-Dispatch Review Screen**: When clicking "Issue & Publish Invoice", an interactive modal opens with a complete summary of recipient client, dates, itemized lines, taxes, and outgoing email preferences before dispatch.
- **Invoice Recall / Pull Back**: If an error is spotted post-issuance, the sender can instantly pull back the invoice. This immediately revokes the public URL and PDF tokens, reverts the status to Draft for editing, and optionally emails a formal correction notice to the client.

### 👁️ C. Dual-Channel Real-Time Engagement Tracking
- **Email Open Tracking**: Embedded 1x1 GIF tracking beacon records client email opens.
- **Portal & PDF View Tracking**: Records timestamp and counts when the client accesses the payment portal or downloads the PDF.
- **Sender Open Alert**: The invoice creator immediately receives an automated email notification the instant the client views their invoice.

### 🤖 D. Invoicy AI Copilot (Text & Voice Commands)
- Built-in floating assistant powered by the browser Web Speech API (`SpeechRecognition` & `SpeechSynthesisUtterance`).
- Supports hands-free voice commands ("Create new invoice", "Go to settings", "How do I recall an invoice?") as well as conversational text inquiries.

### 📚 E. Comprehensive Help & Knowledge Center
- Accessible via `/help` and the main sidebar navigation.
- Detailed step-by-step documentation, interactive metric previews, and visual walk-throughs across all 10 core platform modules.

### 📧 F. Branded SMTP & Deliverability
- Dedicated branded HTML test email template with the official Canadian Red Maple leaf vector logo, diagnostics table (host, port, encryption, username, from address), and verified deliverability status.

---

## 3. Restore Point & Rollback Instructions

### Restore Point Archive
- **Snapshot File:** `invoicy_production_v1.0.0_stable.zip`
- **Git Tag:** `v1.0.0-stable`
- **Integrity Baseline:** All migrations applied, zero-leakage multi-tenancy verified, isolated dual-guard authentication active.

### Fast Rollback Procedure
If emergency restoration is needed:
```bash
# 1. Checkout stable tag
git checkout v1.0.0-stable

# 2. Re-upload core files or restore from zip:
# Unzip invoicy_production_v1.0.0_stable.zip into /home/u858731124/domains/redmapleagency.com/redmaple_core/

# 3. Clear application caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
