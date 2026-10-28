<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Workspace;
use Exception;
use Illuminate\Support\Facades\DB;

class CouponService
{
    /**
     * Validate a coupon code for a given context (signup, invoice, etc.).
     */
    public function validate(
        string $code,
        string $target = 'all',
        int $amountCents = 0,
        string $currency = 'USD',
        ?Workspace $workspace = null
    ): array {
        $cleanCode = strtoupper(trim($code));

        $coupon = Coupon::whereRaw('UPPER(code) = ?', [$cleanCode])->first();

        if (!$coupon) {
            return [
                'valid' => false,
                'message' => 'Invalid or unrecognized promo coupon code.',
                'coupon' => null,
                'discount_cents' => 0,
            ];
        }

        if (!$coupon->is_active) {
            return [
                'valid' => false,
                'message' => 'This promo code is no longer active.',
                'coupon' => $coupon,
                'discount_cents' => 0,
            ];
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return [
                'valid' => false,
                'message' => 'This promo code has expired.',
                'coupon' => $coupon,
                'discount_cents' => 0,
            ];
        }

        if ($coupon->max_redemptions !== null && $coupon->times_redeemed >= $coupon->max_redemptions) {
            return [
                'valid' => false,
                'message' => 'This coupon has reached its maximum usage limit.',
                'coupon' => $coupon,
                'discount_cents' => 0,
            ];
        }

        // Validate target
        if ($coupon->target && $coupon->target !== 'all' && $target !== 'all' && $coupon->target !== $target) {
            $forText = $coupon->target === 'signup' ? 'new user signup' : 'invoice checkout';
            return [
                'valid' => false,
                'message' => "This promo code is only applicable for {$forText}.",
                'coupon' => $coupon,
                'discount_cents' => 0,
            ];
        }

        // Currency restriction
        if ($coupon->currency_restriction && strtoupper($coupon->currency_restriction) !== strtoupper($currency)) {
            return [
                'valid' => false,
                'message' => "This coupon is only valid for {$coupon->currency_restriction} currency.",
                'coupon' => $coupon,
                'discount_cents' => 0,
            ];
        }

        // Minimum purchase requirement
        if ($coupon->min_purchase_cents > 0 && $amountCents > 0 && $amountCents < $coupon->min_purchase_cents) {
            $minFormatted = number_format($coupon->min_purchase_cents / 100, 2);
            return [
                'valid' => false,
                'message' => "Minimum purchase of {$currency} {$minFormatted} required.",
                'coupon' => $coupon,
                'discount_cents' => 0,
            ];
        }

        // Calculate discount or offer details
        $discountCents = 0;
        if (($coupon->offer_type === 'discount' || empty($coupon->offer_type)) && $amountCents > 0) {
            $discountCents = $coupon->calculateDiscountCents($amountCents);
        }

        return [
            'valid' => true,
            'message' => 'Promo code applied successfully!',
            'coupon' => $coupon,
            'discount_cents' => $discountCents,
            'discount_amount' => round($discountCents / 100, 2),
            'offer_type' => $coupon->offer_type ?: 'discount',
            'service_name' => $coupon->service_name,
        ];
    }

    /**
     * Backward-compatible validateCode method for existing workspace subscriptions.
     */
    public function validateCode(string $code, Workspace $workspace, int $amountCents, string $currency): array
    {
        return $this->validate($code, 'all', $amountCents, $currency, $workspace);
    }

    /**
     * Apply coupon during user registration (signup).
     */
    public function applyToSignup(Coupon $coupon, Workspace $workspace, User $user): bool
    {
        $subscription = $workspace->subscriptions()->first();

        // 1. Extended trial days
        if ($coupon->discount_type === 'trial_days' || $coupon->offer_type === 'trial') {
            $extraDays = (int) ($coupon->discount_value ?: 30);
            if ($subscription) {
                $base = $subscription->trial_ends_at ?: now();
                $subscription->update([
                    'trial_ends_at' => $base->addDays($extraDays),
                ]);
            }
        }

        // 2. Record redemption
        CouponRedemption::create([
            'coupon_id' => $coupon->id,
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'discount_applied_cents' => (int) round($coupon->discount_value * 100),
            'currency' => $workspace->currency ?? 'USD',
            'service_granted' => $coupon->service_name,
            'subscription_id' => $subscription?->id,
            'gateway' => 'signup_promo',
            'redeemed_at' => now(),
        ]);

        $coupon->increment('times_redeemed');

        return true;
    }

    /**
     * Apply coupon directly to an invoice (or via customer portal).
     */
    public function applyToInvoice(Coupon $coupon, Invoice $invoice, ?string $userId = null): array
    {
        $subtotalCents = $invoice->subtotal_cents;
        $discountCents = 0;
        $serviceGranted = null;

        if ($coupon->offer_type === 'service' || !empty($coupon->service_name)) {
            // Free Service Offer: Add line item at 0 rate
            $serviceGranted = $coupon->service_name ?: 'Complimentary Promotional Service';
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => $coupon->service_id,
                'description' => "[Promo Offer: {$coupon->code}] " . $serviceGranted,
                'category' => 'Promotion',
                'quantity' => 1,
                'unit' => 'Package',
                'rate_cents' => 0,
                'tax_rate' => 0,
                'tax_cents' => 0,
                'amount_cents' => 0,
                'sort_order' => 999,
            ]);
        } else {
            // Percentage or fixed monetary discount
            $discountCents = $coupon->calculateDiscountCents($subtotalCents);
            $newDiscountCents = $invoice->discount_cents + $discountCents;
            $newDueCents = max(0, $invoice->total_cents - $discountCents);

            $invoice->update([
                'discount_cents' => $newDiscountCents,
                'due_cents' => $newDueCents,
            ]);
        }

        // Record redemption
        CouponRedemption::create([
            'coupon_id' => $coupon->id,
            'workspace_id' => $invoice->workspace_id,
            'user_id' => $userId,
            'invoice_id' => $invoice->id,
            'client_id' => $invoice->client_id,
            'discount_applied_cents' => $discountCents,
            'currency' => $invoice->currency,
            'service_granted' => $serviceGranted,
            'gateway' => 'portal_coupon',
            'redeemed_at' => now(),
        ]);

        $coupon->increment('times_redeemed');

        return [
            'success' => true,
            'discount_cents' => $discountCents,
            'service_granted' => $serviceGranted,
        ];
    }

    /**
     * Atomically redeem a coupon for a workspace during checkout.
     */
    public function redeemCoupon(
        Coupon $coupon,
        Workspace $workspace,
        int $amountCents,
        string $currency,
        ?string $subscriptionId = null,
        ?string $gateway = null
    ): CouponRedemption {
        return DB::transaction(function () use ($coupon, $workspace, $amountCents, $currency, $subscriptionId, $gateway) {
            $check = $coupon->validateFor($workspace, $amountCents, $currency);
            if (!$check['valid']) {
                throw new Exception($check['message']);
            }

            $updated = DB::table('coupons')
                ->where('id', $coupon->id)
                ->where(function ($q) {
                    $q->whereNull('max_redemptions')
                      ->orWhereRaw('times_redeemed < max_redemptions');
                })
                ->increment('times_redeemed');

            if ($updated === 0) {
                throw new Exception('Coupon usage limit reached during concurrent checkout.');
            }

            $discountCents = $coupon->calculateDiscountCents($amountCents);

            return CouponRedemption::create([
                'coupon_id' => $coupon->id,
                'workspace_id' => $workspace->id,
                'discount_applied_cents' => $discountCents,
                'currency' => $currency,
                'subscription_id' => $subscriptionId,
                'gateway' => $gateway,
                'redeemed_at' => now(),
            ]);
        });
    }
}
