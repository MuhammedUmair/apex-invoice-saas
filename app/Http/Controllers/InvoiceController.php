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

            Mail::to($invoice->client->email)->send(new InvoiceMailable($invoice));
            
            $invoice->update([
                'status' => in_array($invoice->status, ['draft', 'issued']) ? 'sent' : $invoice->status,
                'sent_at' => now(),
            ]);

            return back()->with('message', "Invoice #{$invoice->invoice_number} successfully dispatched to {$invoice->client->email}!");
        } catch (\Throwable $e) {
            \Log::error("Failed to email invoice: " . $e->getMessage());
            return back()->with('error', "Could not send email: " . $e->getMessage());
        }
    }

    public function sendReminder(Invoice $invoice, InvoiceReminderService $reminderService)
    {
        try {
            $res = $reminderService->sendManualReminder($invoice);

            if (!empty($res['success'])) {
                return back()->with('message', "Payment reminder successfully dispatched to {$invoice->client->email} and alert sent to vendor!");
            }

            return back()->with('error', $res['error'] ?? "Could not send reminder.");
        } catch (\Throwable $e) {
            \Log::error("Failed to send reminder for invoice {$invoice->id}: " . $e->getMessage());
            return back()->with('error', "Could not send reminder: " . $e->getMessage());
        }
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['client', 'items', 'payments', 'creditNotes', 'reminders']);
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Public Customer Portal for Viewing & Paying Invoices.
     * Does NOT require user authentication - secured by cryptographic token.
     */
    public function customerPortal(string $token)
    {
        $invoice = Invoice::withoutGlobalScopes()
            ->where('public_secure_token', $token)
            ->with(['client', 'items', 'workspace'])
            ->firstOrFail();

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

        return view('invoices.pdf-challenge', compact('invoice'));
    }

    /**
     * Download or stream PDF invoice with optional password encryption.
     */
    public function downloadPdf(Request $request, string $token)
    {
        $invoice = Invoice::withoutGlobalScopes()
            ->where('public_secure_token', $token)
            ->with(['client', 'items', 'workspace'])
            ->firstOrFail();

        if ($invoice->is_pdf_password_protected) {
            $password = $request->input('password') ?? session("pdf_auth_{$invoice->id}");
            if (!$this->pdfService->verifyPassword($invoice, $password)) {
                return redirect()->route('invoices.pdf.challenge', $token)
                    ->withErrors(['password' => 'Incorrect password entered. This invoice document is protected.']);
            }
            // Store successful password check in session for duration of viewing
            session(["pdf_auth_{$invoice->id}" => $password]);
        }

        // Return clean printable PDF view (which supports standard browser PDF save or mPDF)
        return view('invoices.pdf-template', compact('invoice'));
    }
}
