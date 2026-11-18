<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceMailable;
use App\Models\Invoice;
use App\Services\InvoiceReminderService;
use App\Services\PdfSecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{
    protected PdfSecurityService $pdfService;

    public function __construct(PdfSecurityService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function index(Request $request)
    {
        $status = $request->query('status');

        $invoices = Invoice::with('client')
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('invoices.index', [
            'invoices' => $invoices,
            'currentStatus' => $status,
        ]);
    }

    public function create()
    {
        $workspace = Auth::user()->workspace;
        $sub = $workspace->currentSubscription;

        // 1. Check if trial or subscription is expired
        if ($sub && $sub->isExpired()) {
            if ($sub->status === 'trialing') {
                $sub->update(['status' => 'expired']);
                app(\App\Services\SubscriptionAlertService::class)->notifyTrialExpired($workspace, $sub);
            }
            return redirect()->route('membership.index')->with('error', 'Your free trial / subscription has expired. Please upgrade your package to continue creating invoices.');
        }

        // 2. Check if monthly invoice quota is reached for plan
        $plan = $sub?->plan;
        if ($plan && !$plan->canCreateInvoice($workspace)) {
            return redirect()->route('membership.index')->with('error', "You have reached your {$plan->name} quota of {$plan->invoice_limit} invoices for this monthly cycle. Upgrade to Pro Business or Enterprise Scale for unlimited invoicing.");
        }

        return view('invoices.create');
    }

    public function edit(Invoice $invoice)
    {
        return view('invoices.edit', compact('invoice'));
    }

    public function sendEmail(Invoice $invoice)
    {
        $invoice->load(['client', 'workspace']);

        // 1. Phishing & Spam Relay Prevention: Require Verified Email (Item 4)
        if (!Auth::user()->hasVerifiedEmail()) {
            return back()->with('warning', 'You must verify your account email address before dispatching invoices to clients. Please check your inbox or click resend.');
        }

        // 2. Anti-Abuse Send Cap per Workspace (Item 4)
        if ($invoice->workspace->hasReachedDailyEmailCap()) {
            $cap = $invoice->workspace->dailyEmailCap();
            return back()->with('error', "Daily dispatch cap reached: Your account is limited to {$cap} outbound emails per day. Upgrade your subscription to increase dispatch limits.");
        }

        // 3. Trial Expiration Restriction
        if ($invoice->workspace->isTrial()) {
            $sub = $invoice->workspace->currentSubscription;
            if ($sub && $sub->isExpired()) {
                return back()->with('error', 'Trial Expired: Your free trial has ended. Please upgrade your package to dispatch invoices.');
            }
        }

        // 4. Subscribed Accounts Mandatory SMTP Configuration
        if (!$invoice->workspace->isTrial() && !Auth::user()->isSuperAdmin()) {
            if (!$invoice->workspace->hasConfiguredSmtp()) {
                return back()->with('error', 'Dispatch Blocked: Subscribed agencies are required to configure their authenticated Outgoing SMTP mail server in Agency Settings before dispatching invoices to clients.');
            }
        }

        if (!$invoice->client || empty($invoice->client->email)) {
            return back()->with('error', 'This client does not have a registered email address in CRM.');
        }

        try {
            if ($invoice->workspace && !empty($invoice->workspace->smtp_settings_encrypted)) {
                $smtp = $invoice->workspace->smtp_settings_encrypted;
                if (!empty($smtp['host']) && !empty($smtp['username'])) {
                    config([
                        'mail.mailers.smtp.host' => $smtp['host'],
                        'mail.mailers.smtp.port' => $smtp['port'] ?? 465,
                        'mail.mailers.smtp.encryption' => $smtp['encryption'] ?? 'ssl',
                        'mail.mailers.smtp.username' => $smtp['username'],
                        'mail.mailers.smtp.password' => $smtp['password'] ?? '',
                        'mail.from.address' => $smtp['from_address'] ?? $smtp['username'],
                        'mail.from.name' => $smtp['from_name'] ?? ($invoice->workspace->name ?: 'Invoicy by RedMaple'),
                    ]);
                }
            }

            $targetLocale = $invoice->language ?? $invoice->client?->preferred_language ?? $invoice->workspace?->language ?? 'en';
            if (!in_array($targetLocale, ['en', 'ur', 'hi', 'tr', 'ar', 'es', 'fr', 'de'])) {
                $targetLocale = 'en';
            }
            Mail::to($invoice->client->email)->send(new InvoiceMailable($invoice, $targetLocale));
            
            $invoice->update([
                'status' => in_array($invoice->status, ['draft', 'issued']) ? 'sent' : $invoice->status,
                'sent_at' => now(),
            ]);

            // Track daily send count to enforce send caps
            $invoice->workspace->incrementDailyEmailCount();

            \App\Services\AuditService::log('invoice.dispatched_email', $invoice, null, [
                'recipient' => $invoice->client->email,
                'invoice_number' => $invoice->invoice_number,
            ]);

            return back()->with('message', "Invoice #{$invoice->invoice_number} successfully dispatched to {$invoice->client->email}!");
        } catch (\Throwable $e) {
            \Log::error("Failed to email invoice: " . $e->getMessage());
            return back()->with('error', "Could not send email: " . $e->getMessage());
        }
    }

    public function sendReminder(Invoice $invoice, InvoiceReminderService $reminderService)
    {
        // Require verified email and check send cap for manual reminders too
        if (!Auth::user()->hasVerifiedEmail()) {
            return back()->with('warning', 'You must verify your account email address before sending payment reminders. Please check your inbox or click resend.');
        }

        if ($invoice->workspace->hasReachedDailyEmailCap()) {
            $cap = $invoice->workspace->dailyEmailCap();
            return back()->with('error', "Daily dispatch cap reached: Your account is limited to {$cap} outbound emails per day.");
        }

        try {
            $res = $reminderService->sendManualReminder($invoice);

            if (!empty($res['success'])) {
                $invoice->workspace->incrementDailyEmailCount();

                \App\Services\AuditService::log('invoice.manual_reminder', $invoice, null, [
                    'recipient' => $invoice->client->email,
                ]);

                return back()->with('message', "Payment reminder successfully dispatched to {$invoice->client->email} and alert sent to vendor!");
            }

            return back()->with('error', $res['error'] ?? "Could not send reminder.");
        } catch (\Throwable $e) {
            \Log::error("Failed to send reminder for invoice {$invoice->id}: " . $e->getMessage());
            return back()->with('error', "Could not send reminder: " . $e->getMessage());
        }
    }

    public function show(Invoice $invoice, Request $request)
    {
        $invoice->load(['client', 'items', 'payments', 'creditNotes', 'reminders']);
        $locale = $request->query('lang') ?? session('locale') ?? $invoice->language ?? 'en';
        if (in_array($locale, ['en', 'ur', 'hi', 'tr', 'ar', 'es', 'fr', 'de'])) {
            app()->setLocale($locale);
        }
        return view('invoices.show', compact('invoice'));
    }

    public function updateLanguage(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'language' => 'required|string|in:en,ur,hi,tr,ar,es,fr,de',
        ]);

        $invoice->update(['language' => $validated['language']]);
        session(['locale' => $validated['language']]);
        app()->setLocale($validated['language']);

        return back()->with('message', __('Invoice language updated to :lang successfully!', [
            'lang' => strtoupper($validated['language'])
        ]));
    }

    /**
     * Public Customer Portal for Viewing & Paying Invoices.
     * Does NOT require user authentication - secured by cryptographic token.
     */
    public function customerPortal(Request $request, string $token)
    {
        $invoice = Invoice::withoutGlobalScopes()
            ->where('public_secure_token', $token)
            ->with(['client', 'items', 'workspace'])
            ->firstOrFail();

        $targetLocale = $request->query('lang') ?? session('locale') ?? $invoice->language ?? $invoice->client?->preferred_language ?? $invoice->workspace?->language ?? 'en';
        if (in_array($targetLocale, ['en', 'ur', 'hi', 'tr', 'ar', 'es', 'fr', 'de'])) {
            app()->setLocale($targetLocale);
        }

        // 1. Expiring Token Verification (Item 6)
        if ($invoice->isTokenExpired()) {
            abort(410, 'Security Notice: This invoice portal access token has expired. Please contact the vendor for a refreshed link.');
        }

        // 2. Disable Public Portal for Draft Invoices (Item 2)
        if ($invoice->status === 'draft') {
            abort(403, 'Document Access Restricted: This invoice is currently in saved draft status and has not yet been published or issued by the agency.');
        }

        // 3. Disable Public Portal for Empty Invoices (Item 2)
        if ($invoice->items->isEmpty()) {
            abort(403, 'Document Access Restricted: This invoice contains no line items and cannot be viewed online.');
        }

        // Track customer view & engagement (Item 13 & Item 5)
        $isFirstView = empty($invoice->viewed_at);
        if ($isFirstView) {
            $invoice->viewed_at = now();
        }
        $invoice->view_count = ($invoice->view_count ?? 0) + 1;
        $invoice->save();

        if ($isFirstView) {
            $this->notifySenderOfInvoiceOpen($invoice, 'portal');
        }

        return view('invoices.customer-portal', compact('invoice'));
    }

    /**
     * Apply promotional coupon code directly on the public invoice portal.
     */
    public function applyPortalCoupon(Request $request, string $token, \App\Services\CouponService $couponService)
    {
        $invoice = Invoice::withoutGlobalScopes()
            ->where('public_secure_token', $token)
            ->with(['client', 'items', 'workspace'])
            ->firstOrFail();

        if ($invoice->isTokenExpired()) {
            return back()->with('coupon_error', 'Invoice portal token expired.');
        }

        $code = trim($request->input('coupon_code', ''));
        if (empty($code)) {
            return back()->with('coupon_error', 'Please enter a valid coupon or promo code.');
        }

        $check = $couponService->validate(
            $code,
            'invoice',
            $invoice->subtotal_cents,
            $invoice->currency,
            $invoice->workspace
        );

        if (!$check['valid']) {
            return back()->with('coupon_error', $check['message']);
        }

        $res = $couponService->applyToInvoice($check['coupon'], $invoice);

        $msg = "Promo code '{$check['coupon']->code}' applied successfully!";
        if (!empty($res['service_granted'])) {
            $msg .= " Added complimentary service: {$res['service_granted']}.";
        } elseif (!empty($res['discount_cents'])) {
            $formatted = number_format($res['discount_cents'] / 100, 2);
            $msg .= " Discount of {$invoice->currency} {$formatted} applied.";
        }

        return back()->with('coupon_success', $msg);
    }

    /**
     * Password challenge page for protected customer PDFs.
     */
    public function pdfChallenge(string $token)
    {
        $invoice = Invoice::withoutGlobalScopes()
            ->where('public_secure_token', $token)
            ->firstOrFail();

        if ($invoice->isTokenExpired()) {
            abort(410, 'Security Notice: This document download link has expired.');
        }

        return view('invoices.pdf-challenge', compact('invoice'));
    }

    /**
     * Download or stream PDF invoice with optional password encryption and PIN lockout.
     */
    public function downloadPdf(Request $request, string $token)
    {
        $invoice = Invoice::withoutGlobalScopes()
            ->where('public_secure_token', $token)
            ->with(['client', 'items', 'workspace'])
            ->firstOrFail();

        if ($invoice->isTokenExpired()) {
            abort(410, 'Security Notice: This PDF download token has expired.');
        }

        // Disable PDF View for Draft Invoices (Item 2)
        if ($invoice->status === 'draft') {
            abort(403, 'Document Access Restricted: PDF generation is disabled for saved draft invoices until issued.');
        }

        // Disable PDF View for Empty Invoices (Item 2)
        if ($invoice->items->isEmpty()) {
            abort(403, 'Document Access Restricted: This invoice contains no line items and cannot be downloaded as PDF.');
        }

        if ($invoice->is_pdf_password_protected) {
            // Check if token is currently locked out from too many failed PIN attempts (Item 6)
            if ($invoice->isPinLocked()) {
                $lockedUntil = is_string($invoice->pin_locked_until) ? \Carbon\Carbon::parse($invoice->pin_locked_until) : $invoice->pin_locked_until;
                $rem = max(1, (int) ceil(now()->diffInSeconds($lockedUntil) / 60));
                return redirect()->route('invoices.pdf.challenge', $token)
                    ->withErrors(['password' => "Access locked: Maximum PIN attempts exceeded. Document download is blocked for {$rem} minute(s)."]);
            }

            $password = $request->input('password') ?? session("pdf_auth_{$invoice->id}");
            if (!$this->pdfService->verifyPassword($invoice, $password)) {
                $remAttempts = $invoice->recordFailedPinAttempt();
                $err = $remAttempts > 0
                    ? "Incorrect PIN entered. ({$remAttempts} attempt(s) remaining before 60-minute document lockout)."
                    : "Access locked: 3 incorrect PIN attempts reached. This document link is blocked for 60 minutes.";

                return redirect()->route('invoices.pdf.challenge', $token)
                    ->withErrors(['password' => $err]);
            }

            // Successful PIN check: reset lockout counter & store in session
            $invoice->resetPinLockout();
            session(["pdf_auth_{$invoice->id}" => $password]);
        }

        // Track customer download & view (Item 13)
        if (!$invoice->viewed_at) {
            $invoice->viewed_at = now();
        }
        $invoice->view_count = ($invoice->view_count ?? 0) + 1;
        $invoice->save();

        $targetLocale = $request->query('lang') ?? $invoice->language ?? $invoice->client?->preferred_language ?? $invoice->workspace?->language ?? 'en';
        if (in_array($targetLocale, ['en', 'ur', 'hi', 'tr', 'ar', 'es', 'fr', 'de'])) {
            app()->setLocale($targetLocale);
        }

        // Return clean printable PDF view (which supports standard browser PDF save or mPDF)
        return view('invoices.pdf-template', compact('invoice'));
    }

    /**
     * Invisible 1x1 GIF tracking beacon to track when client opens the email (Item 13).
     */
    public function trackEmailOpen(string $token)
    {
        $invoice = Invoice::withoutGlobalScopes()
            ->where('public_secure_token', $token)
            ->first();

        if ($invoice) {
            $isFirstOpen = empty($invoice->email_opened_at);
            if ($isFirstOpen) {
                $invoice->email_opened_at = now();
            }
            $invoice->email_opens_count = ($invoice->email_opens_count ?? 0) + 1;
            $invoice->save();

            \App\Services\AuditService::log('invoice.email_opened', $invoice, null, [
                'recipient' => $invoice->client?->email ?? $invoice->client_snapshot['email'] ?? null,
                'invoice_number' => $invoice->invoice_number,
                'ip' => request()->ip(),
            ]);

            if ($isFirstOpen && $invoice->status !== 'draft') {
                $this->notifySenderOfInvoiceOpen($invoice, 'email');
            }
        }

        $gif1x1 = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        return response($gif1x1, 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Notify agency sender when customer views/opens their invoice (Item 5).
     */
    protected function notifySenderOfInvoiceOpen(Invoice $invoice, string $channel = 'portal'): void
    {
        try {
            $creator = $invoice->creator;
            $workspace = $invoice->workspace;
            $recipientEmail = $creator?->email ?? $workspace?->owner?->email ?? $workspace?->users()?->first()?->email;

            if (!$recipientEmail) {
                return;
            }

            \App\Services\AuditService::log('invoice.client_viewed_notification', $invoice, null, [
                'recipient_notified' => $recipientEmail,
                'channel' => $channel,
                'client' => $invoice->client?->name ?? 'Client',
                'viewed_at' => now()->toIso8601String(),
            ]);

            $subject = "👁️ Client Viewed Invoice #{$invoice->invoice_number} ({$invoice->formatted_total})";
            $data = [
                'invoice' => $invoice,
                'channel' => $channel,
                'clientName' => $invoice->client?->name ?? $invoice->client_snapshot['name'] ?? 'Client',
                'clientCompany' => $invoice->client?->company_name ?? $invoice->client_snapshot['company_name'] ?? null,
                'timestamp' => now()->format('M d, Y • h:i A T'),
                'ipAddress' => request()->ip(),
                'userAgent' => request()->userAgent(),
                'invoiceUrl' => route('invoices.show', $invoice->id),
            ];

            \Illuminate\Support\Facades\Mail::send('emails.invoice-viewed-alert', $data, function ($message) use ($recipientEmail, $subject) {
                $message->to($recipientEmail)->subject($subject);
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to notify sender on invoice open: " . $e->getMessage());
        }
    }

    /**
     * Recall / Pull Back an issued invoice if issued with mistakes (Item 4).
     */
    public function recall(Invoice $invoice, Request $request)
    {
        $user = Auth::user();
        if ($invoice->workspace_id !== $user->workspace_id && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        if ($invoice->status === 'paid') {
            return back()->with('error', 'Cannot recall an invoice that has already been paid in full.');
        }

        $reason = trim($request->input('recall_reason', 'Invoice requires corrections and was pulled back by sender.'));
        $notifyClient = $request->boolean('notify_client', true);

        // Invalidate public access by rotating secure token
        $invoice->public_secure_token = \Illuminate\Support\Str::random(64);
        $previousStatus = $invoice->status;
        $invoice->status = 'draft';
        $invoice->save();

        \App\Services\AuditService::log('invoice.recalled', $invoice, null, [
            'recalled_by' => $user->name,
            'reason' => $reason,
            'previous_status' => $previousStatus,
        ]);

        $clientEmail = $invoice->client?->email ?? $invoice->client_snapshot['email'] ?? null;
        if ($notifyClient && !empty($clientEmail)) {
            try {
                $mailData = [
                    'invoice' => $invoice,
                    'reason' => $reason,
                    'workspace' => $invoice->workspace,
                ];
                \Illuminate\Support\Facades\Mail::send('emails.invoice-recalled', $mailData, function ($message) use ($clientEmail, $invoice) {
                    $message->to($clientEmail)->subject("Correction Notice: Invoice #{$invoice->invoice_number} has been recalled by {$invoice->workspace?->name}");
                });
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Could not dispatch recall email: " . $e->getMessage());
            }
        }

        return redirect()->route('invoices.show', $invoice->id)->with('success', "Invoice #{$invoice->invoice_number} has been successfully recalled. The previous customer link is revoked, and the invoice is now in Draft mode for editing.");
    }

    /**
     * Public authentic verification endpoint for QR code scans.
     */
    public function verifyInvoice(string $token)
    {
        $invoice = Invoice::withoutGlobalScopes()
            ->where('public_secure_token', $token)
            ->with(['client', 'items', 'workspace'])
            ->firstOrFail();

        \App\Services\AuditService::log('invoice.qr_verified', $invoice, null, [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return view('invoices.verify', compact('invoice'));
    }

    /**
     * Perform bulk operations on selected invoices (Item 6).
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|string|in:mark_paid,mark_issued,send_emails,delete',
            'invoice_ids' => 'required|array|min:1',
            'invoice_ids.*' => 'required|string',
        ]);

        $workspaceId = Auth::user()->workspace_id;
        $invoices = Invoice::where('workspace_id', $workspaceId)
            ->whereIn('id', $validated['invoice_ids'])
            ->with(['client', 'workspace'])
            ->get();

        if ($invoices->isEmpty()) {
            return back()->with('error', 'No valid invoices selected.');
        }

        $count = 0;
        $action = $validated['action'];

        if ($action === 'mark_paid') {
            foreach ($invoices as $inv) {
                $inv->status = 'paid';
                $inv->paid_cents = $inv->total_cents;
                $inv->due_cents = 0;
                $inv->save();
                \App\Services\AuditService::log('invoice.bulk_marked_paid', $inv);
                $count++;
            }
            return back()->with('message', "Successfully marked {$count} invoice(s) as Paid.");
        } elseif ($action === 'mark_issued') {
            foreach ($invoices as $inv) {
                if ($inv->status !== 'paid') {
                    $inv->status = 'issued';
                    $inv->save();
                    \App\Services\AuditService::log('invoice.bulk_marked_issued', $inv);
                    $count++;
                }
            }
            return back()->with('message', "Successfully marked {$count} invoice(s) as Issued.");
        } elseif ($action === 'send_emails') {
            if (!Auth::user()->hasVerifiedEmail()) {
                return back()->with('warning', 'You must verify your account email address before dispatching invoices to clients.');
            }

            $currentWorkspace = Auth::user()->workspace;
            if ($currentWorkspace && !$currentWorkspace->isTrial() && !Auth::user()->isSuperAdmin()) {
                if (!$currentWorkspace->hasConfiguredSmtp()) {
                    return back()->with('error', 'Dispatch Blocked: Subscribed agencies are required to configure their authenticated Outgoing SMTP mail server in Agency Settings before dispatching invoices to clients.');
                }
            }

            $successCount = 0;
            $failedCount = 0;

            foreach ($invoices as $inv) {
                if (!$inv->client || empty($inv->client->email)) {
                    $failedCount++;
                    continue;
                }

                if ($inv->workspace->hasReachedDailyEmailCap()) {
                    return back()->with('warning', "Daily dispatch limit reached. Dispatched {$successCount} invoices before hitting quota.");
                }

                try {
                    if ($inv->workspace && !empty($inv->workspace->smtp_settings_encrypted)) {
                        $smtp = $inv->workspace->smtp_settings_encrypted;
                        if (!empty($smtp['host']) && !empty($smtp['username'])) {
                            config([
                                'mail.mailers.smtp.host' => $smtp['host'],
                                'mail.mailers.smtp.port' => $smtp['port'] ?? 465,
                                'mail.mailers.smtp.encryption' => $smtp['encryption'] ?? 'ssl',
                                'mail.mailers.smtp.username' => $smtp['username'],
                                'mail.mailers.smtp.password' => $smtp['password'] ?? '',
                                'mail.from.address' => $smtp['from_address'] ?? $smtp['username'],
                                'mail.from.name' => $smtp['from_name'] ?? ($inv->workspace->name ?: 'Invoicy by RedMaple'),
                            ]);
                        }
                    }

                    \Illuminate\Support\Facades\Mail::to($inv->client->email)->send(new \App\Mail\InvoiceMailable($inv));
                    $inv->update([
                        'status' => in_array($inv->status, ['draft', 'issued']) ? 'sent' : $inv->status,
                        'sent_at' => now(),
                    ]);
                    $inv->workspace->incrementDailyEmailCount();
                    \App\Services\AuditService::log('invoice.bulk_dispatched_email', $inv, null, ['recipient' => $inv->client->email]);
                    $successCount++;
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error("Bulk send failed for invoice #{$inv->invoice_number}: " . $e->getMessage());
                    $failedCount++;
                }
            }

            return back()->with('message', "Dispatched {$successCount} invoice email(s) successfully." . ($failedCount > 0 ? " ({$failedCount} skipped due to missing email or delivery issue)" : ""));
        } elseif ($action === 'delete') {
            foreach ($invoices as $inv) {
                $inv->items()->delete();
                $inv->delete();
                $count++;
            }
            return back()->with('message', "Successfully deleted {$count} invoice(s).");
        }

        return back();
    }

    /**
     * Delete an individual invoice.
     */
    public function destroy(Invoice $invoice)
    {
        $workspaceId = Auth::user()->workspace_id;
        if (!Auth::user()->isSuperAdmin() && $invoice->workspace_id !== $workspaceId) {
            abort(403, 'Unauthorized access to this invoice.');
        }

        $invNum = $invoice->invoice_number;
        $invoice->items()->delete();
        $invoice->delete();

        \App\Services\AuditService::log('invoice.deleted', null, null, [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invNum,
            'actor' => Auth::user()->email,
        ]);

        return redirect()->route('invoices.index')->with('message', "Invoice #{$invNum} successfully deleted.");
    }
}
