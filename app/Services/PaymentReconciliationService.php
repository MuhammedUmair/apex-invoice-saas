<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Workspace;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentReconciliationService
{
    protected InvoiceNumberService $numberService;

    public function __construct(InvoiceNumberService $numberService)
    {
        $this->numberService = $numberService;
    }

    /**
     * Record an invoice payment idempotently.
     * Guarantees duplicate webhooks or multiple retries do not record duplicate ledger entries.
     */
    public function recordInvoicePayment(
        Invoice $invoice,
        int $amountCents,
        string $gateway,
        string $transactionId,
        string $idempotencyKey,
        array $paymentMethodDetails = [],
        ?int $recordedByUserId = null
    ): Payment {
        return DB::transaction(function () use (
            $invoice,
            $amountCents,
            $gateway,
            $transactionId,
            $idempotencyKey,
            $paymentMethodDetails,
            $recordedByUserId
        ) {
            // Check for existing payment with this idempotency key
            $existing = Payment::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }

            // Lock the invoice row for update
            $lockedInvoice = Invoice::where('id', $invoice->id)->lockForUpdate()->first();

            $paymentNumber = $this->numberService->generateNextPaymentNumber($lockedInvoice->workspace);

            $payment = Payment::create([
                'workspace_id' => $lockedInvoice->workspace_id,
                'invoice_id' => $lockedInvoice->id,
                'client_id' => $lockedInvoice->client_id,
                'payment_number' => $paymentNumber,
                'gateway' => $gateway,
                'amount_cents' => $amountCents,
                'currency' => $lockedInvoice->currency,
                'gateway_transaction_id' => $transactionId,
                'idempotency_key' => $idempotencyKey,
                'status' => 'completed',
                'payment_method_details' => $paymentMethodDetails,
                'payment_date' => now(),
                'recorded_by_user_id' => $recordedByUserId,
            ]);

            // Update invoice balances
            $newPaidCents = $lockedInvoice->paid_cents + $amountCents;
            $newDueCents = max(0, $lockedInvoice->total_cents - $newPaidCents);

            $lockedInvoice->paid_cents = $newPaidCents;
            $lockedInvoice->due_cents = $newDueCents;

            if ($newDueCents === 0) {
                $lockedInvoice->status = 'paid';
                $lockedInvoice->paid_at = now();
            } else {
                $lockedInvoice->status = 'partially_paid';
            }

            $lockedInvoice->save();

            return $payment;
        });
    }

    /**
     * Handle recurring subscription status reconciliation from webhooks.
     */
    public function updateSubscriptionStatus(
        string $gatewaySubscriptionId,
        string $newStatus,
        ?string $currentPeriodEndsAt = null
    ): ?Subscription {
        $subscription = Subscription::where('gateway_subscription_id', $gatewaySubscriptionId)->first();

        if (!$subscription) {
            return null;
        }

        $subscription->status = $newStatus;
        if ($currentPeriodEndsAt) {
            $subscription->current_period_ends_at = $currentPeriodEndsAt;
        }

        if ($newStatus === 'canceled') {
            $subscription->canceled_at = now();
        }

        $subscription->save();

        return $subscription;
    }
}
