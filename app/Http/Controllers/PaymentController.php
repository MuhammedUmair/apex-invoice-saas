<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\AuditService;
use App\Services\PaymentReconciliationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    protected PaymentReconciliationService $reconciliationService;

    public function __construct(PaymentReconciliationService $reconciliationService)
    {
        $this->reconciliationService = $reconciliationService;
    }

    /**
     * Process online payment submitted by the client on the public portal.
     */
    public function processPortalPayment(Request $request, string $token)
    {
        if ($request->isMethod('get')) {
            return redirect()->route('invoices.portal', $token);
        }

        $invoice = Invoice::withoutGlobalScopes()
            ->where('public_secure_token', $token)
            ->firstOrFail();

        // Enforce Trial Protection: Block online checkout for trial evaluation invoices (Item 7)
        if ($invoice->isTrialInvoice()) {
            return redirect()->route('invoices.portal', $token)
                ->withErrors(['payment' => 'Online payment processing is disabled for invoices issued during the Free Trial evaluation period.']);
        }

        $gateway = $request->input('gateway', 'stripe'); // stripe or paypal

        // Prevent portal visitors from self-marking manual/bank transfer payments
        if (in_array($gateway, ['manual', 'bank_transfer', 'interac', 'interac_etransfer', 'cash', 'cheque', 'other'])) {
            return redirect()->route('invoices.portal', $token)
                ->withErrors(['payment' => 'Manual or bank transfer payments cannot be self-marked by portal visitors. Please send the transfer to the issuer; they will verify and record the payment.']);
        }

        // Enforce Payment Gateway Configuration (Request 13): Both SuperAdmin and Tenant
        if ($gateway === 'stripe' && !$invoice->isStripeAvailable()) {
            return redirect()->route('invoices.portal', $token)
                ->withErrors(['payment' => 'Stripe credit card processing is not configured for this invoice issuer.']);
        }

        if ($gateway === 'paypal' && !$invoice->isPaypalAvailable()) {
            return redirect()->route('invoices.portal', $token)
                ->withErrors(['payment' => 'PayPal processing is not configured for this invoice issuer.']);
        }

        if (!$invoice->hasPaymentConfigured()) {
            return redirect()->route('invoices.portal', $token)
                ->withErrors(['payment' => 'Online payment processing is not configured for this invoice.']);
        }

        $amountCents = (int) $request->input('amount_cents', $invoice->due_cents);

        // Generate or retrieve idempotency key
        $idempotencyKey = $request->input('idempotency_key', 'portal_' . $invoice->id . '_' . Str::random(16));
        $transactionId = 'txn_' . strtolower($gateway) . '_' . Str::random(18);

        $payment = $this->reconciliationService->recordInvoicePayment(
            $invoice,
            $amountCents,
            $gateway,
            $transactionId,
            $idempotencyKey,
            [
                'payment_source' => 'customer_portal',
                'gateway' => $gateway,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return redirect()->route('invoices.portal', $token)
            ->with('payment_success', "Payment of {$payment->formatted_amount} processed successfully. Thank you!");
    }

    /**
     * Authenticated Manual "Record Payment" Endpoint.
     * Route: POST /invoices/{invoice}/payments/manual
     */
    public function recordManualPayment(Request $request, Invoice $invoice)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        // Workspace Tenant Authorization Guard
        if (!$user->isSuperAdmin() && $invoice->workspace_id !== $user->workspace_id) {
            abort(403, 'Unauthorized access to this workspace invoice.');
        }

        // Balance Check
        $maxDue = $invoice->due_cents;
        if ($maxDue <= 0) {
            return back()->withErrors(['amount_cents' => 'This invoice is already fully paid.']);
        }

        // Allow amount in dollars or amount_cents directly
        $rawAmount = $request->input('amount');
        if ($request->has('amount') && !$request->has('amount_cents')) {
            $request->merge(['amount_cents' => (int) round(((float) $rawAmount) * 100)]);
        }

        $validated = $request->validate([
            'amount_cents' => ['required', 'integer', 'min:1', "max:{$maxDue}"],
            'payment_method' => ['required', 'string', 'in:bank_transfer,interac_etransfer,cheque,cash,other'],
            'paid_at' => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $idempotencyKey = 'manual_' . $invoice->id . '_' . Str::random(16);
        $transactionId = 'man_' . Str::random(18);

        $payment = $this->reconciliationService->recordInvoicePayment(
            invoice: $invoice,
            amountCents: (int) $validated['amount_cents'],
            gateway: 'manual',
            transactionId: $transactionId,
            idempotencyKey: $idempotencyKey,
            paymentMethodDetails: [
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'paid_at' => $validated['paid_at'],
                'recorded_by' => $user->email,
            ],
            recordedByUserId: $user->id,
            feeCents: 0,
            netAmountCents: (int) $validated['amount_cents'],
            paymentDate: $validated['paid_at']
        );

        AuditService::log('payment.recorded_manually', null, null, [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'payment_id' => $payment->id,
            'payment_number' => $payment->payment_number,
            'amount_cents' => $payment->amount_cents,
            'payment_method' => $validated['payment_method'],
            'reference_number' => $validated['reference_number'] ?? null,
            'actor' => $user->email,
        ]);

        return redirect()->route('invoices.show', $invoice->id)
            ->with('message', "Manual payment of {$payment->formatted_amount} via {$payment->method_display} recorded successfully.");
    }

    /**
     * Audit Safe Payment Reversal / Voiding Endpoint.
     * Route: POST /invoices/{invoice}/payments/{payment}/void
     */
    public function voidPayment(Request $request, Invoice $invoice, Payment $payment)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        // Workspace Tenant Authorization Guard
        if (!$user->isSuperAdmin() && ($invoice->workspace_id !== $user->workspace_id || $payment->workspace_id !== $user->workspace_id)) {
            abort(403, 'Unauthorized access to this workspace payment record.');
        }

        if ($payment->invoice_id !== $invoice->id) {
            abort(404, 'Payment does not belong to this invoice.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|min:5|max:500',
        ]);

        $this->reconciliationService->voidInvoicePayment($payment, $validated['reason'], $user->id);

        AuditService::log('payment.voided', null, null, [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'payment_id' => $payment->id,
            'payment_number' => $payment->payment_number,
            'amount_cents' => $payment->amount_cents,
            'reason' => $validated['reason'],
            'actor' => $user->email,
        ]);

        return redirect()->route('invoices.show', $invoice->id)
            ->with('message', "Payment #{$payment->payment_number} has been reversed/voided for CRA compliance.");
    }
}

