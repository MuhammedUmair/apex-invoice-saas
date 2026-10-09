<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\PaymentReconciliationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected PaymentReconciliationService $reconciliationService;

    public function __construct(PaymentReconciliationService $reconciliationService)
    {
        $this->reconciliationService = $reconciliationService;
    }

    /**
     * Handle incoming Stripe webhooks (both subscription renewals & invoice payments).
     */
    public function handleStripe(Request $request)
    {
        $payload = $request->all();
        $eventType = $payload['type'] ?? null;
        $eventId = $payload['id'] ?? null;

        Log::info("Received Stripe Webhook [{$eventType}] - Event ID: {$eventId}");

        switch ($eventType) {
            case 'invoice.payment_succeeded':
                $data = $payload['data']['object'] ?? [];
                $metadata = $data['metadata'] ?? [];
                $invoiceId = $metadata['invoice_id'] ?? null;

                if ($invoiceId) {
                    $invoice = Invoice::withoutGlobalScopes()->find($invoiceId);
                    if ($invoice) {
                        $this->reconciliationService->recordInvoicePayment(
                            $invoice,
                            $data['amount_paid'] ?? 0,
                            'stripe',
                            $data['payment_intent'] ?? $data['id'],
                            "stripe_evt_{$eventId}"
                        );
                    }
                }
                break;

            case 'customer.subscription.updated':
            case 'customer.subscription.deleted':
                $subData = $payload['data']['object'] ?? [];
                $subId = $subData['id'] ?? null;
                $status = $subData['status'] ?? 'canceled';
                $periodEnd = isset($subData['current_period_end']) ? date('Y-m-d H:i:s', $subData['current_period_end']) : null;

                if ($subId) {
                    $this->reconciliationService->updateSubscriptionStatus(
                        $subId,
                        $status === 'active' ? 'active' : 'canceled',
                        $periodEnd
                    );
                }
                break;
        }

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Handle incoming PayPal webhooks (IPN / Webhooks API).
     */
    public function handlePayPal(Request $request)
    {
        $payload = $request->all();
        $eventType = $payload['event_type'] ?? null;
        $eventId = $payload['id'] ?? null;

        Log::info("Received PayPal Webhook [{$eventType}] - Event ID: {$eventId}");

        switch ($eventType) {
            case 'PAYMENT.SALE.COMPLETED':
                $resource = $payload['resource'] ?? [];
                $customId = $resource['custom_id'] ?? null; // maps to invoice_id
                $saleId = $resource['id'] ?? null;
                $amountValue = (float) ($resource['amount']['total'] ?? 0);
                $amountCents = (int) round($amountValue * 100);

                if ($customId) {
                    $invoice = Invoice::withoutGlobalScopes()->find($customId);
                    if ($invoice) {
                        $this->reconciliationService->recordInvoicePayment(
                            $invoice,
                            $amountCents,
                            'paypal',
                            $saleId,
                            "paypal_evt_{$eventId}"
                        );
                    }
                }
                break;

            case 'BILLING.SUBSCRIPTION.CANCELLED':
            case 'BILLING.SUBSCRIPTION.SUSPENDED':
                $resource = $payload['resource'] ?? [];
                $subId = $resource['id'] ?? null;

                if ($subId) {
                    $this->reconciliationService->updateSubscriptionStatus(
                        $subId,
                        'canceled'
                    );
                }
                break;
        }

        return response()->json(['status' => 'success'], 200);
    }
}
