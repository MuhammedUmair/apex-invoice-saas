<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Workspace;
use Exception;
use Illuminate\Support\Facades\DB;

class CouponService
{
    /**
     * Validate a coupon code for a workspace.
     */
    public function validateCode(string $code, Workspace $workspace, int $amountCents, string $currency): array
    {
        $coupon = Coupon::where('code', strtoupper(trim($code)))->first();

        if (!$coupon) {
            return [
                'valid' => false,
                'message' => 'Invalid or unrecognized coupon code.',
                'coupon' => null,
                'discount_cents' => 0,
            ];
        }

        $check = $coupon->validateFor($workspace, $amountCents, $currency);

        if (!$check['valid']) {
            return [
                'valid' => false,
                'message' => $check['message'],
                'coupon' => $coupon,
                'discount_cents' => 0,
            ];
        }

        $discountCents = $coupon->calculateDiscountCents($amountCents);

        return [
            'valid' => true,
            'message' => 'Coupon code is valid!',
            'coupon' => $coupon,
            'discount_cents' => $discountCents,
        ];
    }

    /**
     * Atomically redeem a coupon for a workspace.
     * Prevents race conditions during simultaneous checkouts and enforces the unique constraint.
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
            // Re-validate inside transaction with lock
            $check = $coupon->validateFor($workspace, $amountCents, $currency);
            if (!$check['valid']) {
                throw new Exception($check['message']);
            }

            // Atomic conditional increment: ensures times_redeemed cannot exceed max_redemptions
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

            // Record redemption (protected by unique index on coupon_id + workspace_id)
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
