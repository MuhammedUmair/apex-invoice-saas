<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\PaymentReconciliationService;
use Illuminate\Http\Request;
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
        $invoice = Invoice::withoutGlobalScopes()
            ->where('public_secure_token', $token)
            ->firstOrFail();

        $gateway = $request->input('gateway', 'stripe'); // stripe or paypal
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
}
