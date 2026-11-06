<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Workspace;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentReconciliationService
{
    protected InvoiceNumberService $numberService;

    public function __construct(InvoiceNumberService $numberService)
    {
        $this->numberService = $numberService;
    }

    /**
     * Record an invoice payment idempotently.
     * Guarantees duplicate webhooks or multiple concurrent retries do not record duplicate ledger entries.
     * Takes row lock first, catches unique constraint violations on idempotency_key, and returns existing record.
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
        // Fast-path: Check if already processed
        $existing = Payment::withoutGlobalScopes()->where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            return $existing;
        }

        try {
            return DB::transaction(function () use (
                $invoice,
                $amountCents,
                $gateway,
                $transactionId,
                $idempotencyKey,
                $paymentMethodDetails,
                $recordedByUserId
            ) {
                // Lock the invoice row first to serialize balance calculations
                $lockedInvoice = Invoice::withoutGlobalScopes()
                    ->where('id', $invoice->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Re-check after acquiring lock in case concurrent process completed
                $existingLocked = Payment::withoutGlobalScopes()->where('idempotency_key', $idempotencyKey)->first();
                if ($existingLocked) {
                    return $existingLocked;
                }

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

                // Update invoice balances atomically
                $newPaidCents = $lockedInvoice->paid_cents + $amountCents;
                $newDueCents = max(0, $lockedInvoice->total_cents - $newPaidCents);

                $lockedInvoice->paid_cents = $newPaidCents;
                $lockedInvoice->due_cents = $newDueCents;

                if ($newDueCents === 0) {
                    $lockedInvoice->status = 'paid';
                    $lockedInvoice->paid_at = now();
                } elseif ($newPaidCents > 0) {
                    $lockedInvoice->status = 'partially_paid';
                }

                $lockedInvoice->save();

                return $payment;
            }, 5);
        } catch (UniqueConstraintViolationException | QueryException $e) {
            // Catch concurrent duplicate insert on unique idempotency_key
            if (str_contains($e->getMessage(), 'idempotency_key') || $e->getCode() == 23000) {
                Log::info("Idempotent payment collision caught for key [{$idempotencyKey}]. Returning existing record.");
                $existing = Payment::withoutGlobalScopes()->where('idempotency_key', $idempotencyKey)->first();
                if ($existing) {
                    return $existing;
                }
            }
            throw $e;
        }
    }

    /**
     * Update subscription status from webhook events.
     */
    public function updateSubscriptionStatus(string $gatewaySubscriptionId, string $status, ?string $periodEndsAt = null): ?Subscription
    {
        $sub = Subscription::where('gateway_subscription_id', $gatewaySubscriptionId)->first();
        if (!$sub) {
            return null;
        }

        $data = ['status' => $status];
        if ($periodEndsAt) {
            $data['current_period_ends_at'] = $periodEndsAt;
        }
        if ($status === 'canceled') {
            $data['canceled_at'] = now();
        }

        $sub->update($data);
        return $sub;
    }
}
