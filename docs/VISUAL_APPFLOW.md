# Visual Application Flow Diagrams (VISUAL_APPFLOW.md)
## Project: Invoicy by RedMaple — Comprehensive Architecture & State Flows

**Document Version:** 2.0.0  
**Status:** Approved & Live in Production  
**Format:** Native Mermaid Architecture Diagrams  

---

## 1. Invoice Transaction Lifecycle State Machine

```mermaid
stateDiagram-v2
    [*] --> Draft : Create in InvoiceBuilder

    Draft --> Issued : Finalize & Lock Sequence (INV-YYYY-XXXX)
    Draft --> Cancelled : Discard Draft

    Issued --> PartiallyPaid : Customer makes partial payment
    Issued --> Paid : Full payment received
    Issued --> Overdue : Due date passes without full settlement
    Issued --> Cancelled : Invoice voided or fully refunded

    PartiallyPaid --> Paid : Remaining due balance paid
    PartiallyPaid --> Overdue : Due date passes with balance > 0
    PartiallyPaid --> Paid : Credit note covers remaining due balance

    Overdue --> PartiallyPaid : Partial settlement received
    Overdue --> Paid : Full settlement received

    Paid --> [*] : Ledger transaction complete
    Cancelled --> [*] : Terminated
```

---

## 2. Tenant Subscription Lifecycle State Machine

```mermaid
stateDiagram-v2
    [*] --> Trialing : User registers (14-day Pro Trial granted)

    Trialing --> Active : Super Admin activates OR Manual Upgrade approved
    Trialing --> Expired : 14 days elapsed without upgrade
    Trialing --> Suspended : Admin suspends workspace
    Trialing --> Canceled : Admin or tenant cancels account

    Active --> Suspended : Admin suspends (e.g. Terms breach)
    Active --> Inactive : Admin deactivates
    Active --> Canceled : Subscription cancelled (canceled_at stamped)
    Active --> Active : Subscription renewed (+1, +3, +6, +12 months)

    Suspended --> Active : Admin clicks [Activate]
    Inactive --> Active : Admin clicks [Activate]
    Canceled --> Active : Admin clicks [Activate] (clears canceled_at)

    Expired --> Active : Upgrade request approved by Admin
```

---

## 3. User Registration & Provisioning Pipeline

```mermaid
flowchart TD
    Start([Visitor submits /register]) --> V{Validation Valid?}
    V -- No --> Err[Display Validation Errors]
    V -- Yes --> TX[Start DB Transaction]

    TX --> CW[Create Workspace Record with Unique Slug]
    CW --> CU[Create User Record role=owner, is_super_admin=false]
    CU --> CS[Create Subscription status=trialing, trial_ends_at=+14d]
    CS --> CSEQ[Seed InvoiceSequence for Current Year]
    CSEQ --> Commit[Commit Transaction]

    Commit --> Alert[Dispatch SubscriptionAlert type=signup]
    Alert --> Mail[Send Notification Email to Super Admin]
    Commit --> Auth[Authenticate Session via Bcrypt]
    Auth --> Dash([Redirect to /dashboard with Trial Banner])
```

---

## 4. Concurrency-Safe Invoice Creation & Locking Flow

```mermaid
flowchart TD
    User([User clicks 'Issue & Finalize Invoice']) --> Val{Validate Client & Line Items}
    Val -- Invalid --> ReturnErr[Highlight Missing Inputs]
    Val -- Valid --> DBTx[Begin Database Transaction]

    DBTx --> LockSeq["InvoiceSequence::where('workspace_id', ...)->lockForUpdate()"]
    LockSeq --> CheckSeq{Sequence Exists for Year?}
    CheckSeq -- No --> CreateSeq["Create sequence counter = 1"]
    CheckSeq -- Yes --> IncSeq["Increment current_sequence (+1)"]

    CreateSeq --> FormatNum["Format 'INV-{YEAR}-{SEQUENCE:04d}'"]
    IncSeq --> FormatNum

    FormatNum --> FreezeSnapshot[Serialize Client & Workspace Snapshots to JSON]
    FreezeSnapshot --> GenPIN[Generate 4-Digit Security PIN]
    GenPIN --> CalcGAAP[Calculate GAAP Pre-tax Discount & Taxes]
    CalcGAAP --> SaveInv[Save Invoice & Line Items status=issued]

    SaveInv --> CommitTx[Commit DB Transaction & Release Lock]
    CommitTx --> DispatchClient[Optional: Dispatch Email with Portal Link & PIN]
    DispatchClient --> Finish([Invoice Successfully Issued])
```

---

## 5. Customer Portal & PIN Challenge Flow

```mermaid
flowchart TD
    ClientLink([Customer opens /invoices/p/token]) --> CheckExpire{Token Expired?}
    CheckExpire -- Yes --> ExpiredView[Return HTTP 410 Gone]
    CheckExpire -- No --> CheckLock{Token PIN Locked?}

    CheckLock -- Yes --> LockedView[Show 60-Min Lockout Screen]
    CheckLock -- No --> PortalView[Display Customer Portal & Trial Watermark]

    PortalView --> Action1[View Bank Remittance Wire Details]
    PortalView --> Action2[Click 'Pay Online' via Idempotent Webhook]
    PortalView --> Action3[Click 'Download PDF' /pdf-unlock]

    Action3 --> CheckPIN{Invoice Has PIN?}
    CheckPIN -- No --> DownloadStream[Download Vector PDF]
    CheckPIN -- Yes --> AuthPIN{Session Valid?}
    AuthPIN -- Yes --> DownloadStream
    AuthPIN -- No --> ChallengeView[Show PIN Challenge Screen]

    ChallengeView --> InputPIN[Customer enters 4-digit PIN]
    InputPIN --> VerifyPIN{Matches Bcrypt Hash?}
    VerifyPIN -- Yes --> UnlockPDF[Reset Lockout Counter & Download PDF]
    VerifyPIN -- No --> IncFail[Increment Failed Counter]
    IncFail --> CheckMax{Attempts >= 3?}
    CheckMax -- Yes --> LockToken[Lock Token for 60 Minutes]
    CheckMax -- No --> ChallengeView
```

---

## 6. Option B: Manual Upgrade Request & Admin Approval Flow

```mermaid
flowchart TD
    Tenant([Tenant on /membership selects package]) --> EnterNotes[Tenant enters Wire Reference / PO Notes]
    EnterNotes --> ClickSubmit[Tenant clicks 'Submit Upgrade Request']
    
    ClickSubmit --> CheckFree{Is Price $0.00 via Promo Coupon?}
    CheckFree -- Yes --> AutoActivate[Activate Plan Immediately]
    CheckFree -- No --> CreateAlert[Create SubscriptionAlert type=upgrade_request]

    CreateAlert --> EmailAdmin[Send Email to Super Admin with Wire Notes]
    CreateAlert --> ShowScreen[Show 'Upgrade Request Submitted' Screen to Tenant]
    ShowScreen --> HoldState([Tenant remains on Current Plan - No Free Upgrade])

    EmailAdmin --> AdminNotice([Super Admin opens /admin/subscriptions])
    AdminNotice --> ReviewTab[Open Alerts Automation Tab]
    ReviewTab --> VerifyBank{Bank Wire Received?}
    
    VerifyBank -- No --> Pending[Leave Alert in Pending State]
    VerifyBank -- Yes --> ClickApprove[Admin clicks 'Approve Upgrade' Button]

    ClickApprove --> ExecRenew[Update Subscription plan_id, status=active, +1 Month]
    ExecRenew --> MarkRead[Mark Alert Read]
    ExecRenew --> TenantUpgraded([Tenant Account Instantly Activated on New Tier])
```

---

## 7. Automated 3-Stage Dunning Reminder Engine

```mermaid
flowchart LR
    Cron[Scheduled Cron Job: 00:00 UTC] --> Scan["Scan Invoices where due_cents > 0"]

    Scan --> Stage1{"Due Date - 3 Days?"}
    Stage1 -- Yes --> CheckS1{Already Sent Stage 1?}
    CheckS1 -- No --> SendS1[Send Friendly Reminder Email & Log in DB]

    Scan --> Stage2{"Due Date == Today?"}
    Stage2 -- Yes --> CheckS2{Already Sent Stage 2?}
    CheckS2 -- No --> SendS2[Send Urgent Due Today Notice & Log in DB]

    Scan --> Stage3{"Due Date + 7 Days Past?"}
    Stage3 -- Yes --> CheckS3{Already Sent Stage 3?}
    CheckS3 -- No --> SendS3[Send Overdue Final Demand Notice & Log in DB]
```

---

## 8. Webhook Payment Reconciliation Sequence

```mermaid
sequenceDiagram
    autonumber
    actor Customer
    participant Gateway as Stripe / PayPal
    participant Webhook as WebhookController
    participant Service as PaymentReconciliationService
    participant DB as MySQL Database

    Customer->>Gateway: Submits Credit Card / Wallet Payment
    Gateway-->>Customer: Payment Successful
    Gateway->>Webhook: POST /webhooks/stripe (event: invoice.payment_succeeded)
    Webhook->>Webhook: Validate Webhook Signature & Event Payload
    Webhook->>Service: recordInvoicePayment(invoice, amount, idempotency_key)
    
    Service->>DB: Check if idempotency_key exists in payments table
    alt Duplicate Event Detected
        DB-->>Service: Payment already recorded
        Service-->>Webhook: Return existing payment (Ignore Duplicate)
    else First Time Event
        Service->>DB: Lock Invoice row (lockForUpdate)
        Service->>DB: Create Payment Record status=completed
        Service->>DB: Update Invoice paid_cents and due_cents
        alt Balance reaches $0.00
            Service->>DB: Update Invoice status = 'paid', paid_at = now()
        else Balance > $0.00
            Service->>DB: Update Invoice status = 'partially_paid'
        end
        DB-->>Service: Transaction Committed
        Service-->>Webhook: Payment Reconciled
    end

    Webhook-->>Gateway: HTTP 200 OK {"status": "success"}
```

---

## 9. Atomic Coupon Validation & Checkout Sequence

```mermaid
sequenceDiagram
    autonumber
    actor User
    participant UI as MembershipCheckout (Livewire)
    participant Service as CouponService
    participant DB as MySQL Database

    User->>UI: Enters Coupon Code "HOSTINGER50"
    UI->>Service: validateCode(code, workspace, plan_price)
    Service->>DB: Query Coupon where code = 'HOSTINGER50' and is_active = 1
    DB-->>Service: Coupon row found (discount: 50%)
    Service->>DB: Check if workspace has already redeemed coupon
    DB-->>Service: 0 previous redemptions
    Service->>Service: Calculate Discount ($24.50 off $49.00)
    Service-->>UI: Return {valid: true, discount_cents: 2450}
    UI-->>User: Display Updated Price "$24.50/mo" in Emerald Green

    User->>UI: Submits Upgrade
    UI->>DB: Begin DB Transaction with lockForUpdate() on Coupon
    DB-->>UI: Lock Acquired
    UI->>DB: Increment Coupon redemptions_count
    UI->>DB: Insert CouponRedemption record
    UI->>DB: Commit DB Transaction
```
