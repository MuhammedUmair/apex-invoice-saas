<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'currency_restriction',
        'min_purchase_cents',
        'duration',
        'duration_months',
        'max_redemptions',
        'times_redeemed',
        'expires_at',
        'is_active',
        'stripe_coupon_id',
        'paypal_discount_id',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_purchase_cents' => 'integer',
        'max_redemptions' => 'integer',
        'times_redeemed' => 'integer',
        'duration_months' => 'integer',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    /**
     * Check if the coupon is valid for a given workspace and purchase amount.
     */
    public function validateFor(Workspace $workspace, int $amountCents, string $currency): array
    {
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'Coupon code is no longer active.'];
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return ['valid' => false, 'message' => 'Coupon code has expired.'];
        }

        if ($this->max_redemptions !== null && $this->times_redeemed >= $this->max_redemptions) {
            return ['valid' => false, 'message' => 'Coupon usage limit has been reached.'];
        }

        if ($this->currency_restriction && strtoupper($this->currency_restriction) !== strtoupper($currency)) {
            return ['valid' => false, 'message' => "Coupon is only applicable for {$this->currency_restriction} currency."];
        }

        if ($this->min_purchase_cents > 0 && $amountCents < $this->min_purchase_cents) {
            $minFormatted = number_format($this->min_purchase_cents / 100, 2);
            return ['valid' => false, 'message' => "Minimum purchase of {$currency} {$minFormatted} required."];
        }

        // Check if workspace already redeemed this coupon
        $alreadyRedeemed = $this->redemptions()->where('workspace_id', $workspace->id)->exists();
        if ($alreadyRedeemed) {
            return ['valid' => false, 'message' => 'Your workspace has already redeemed this coupon.'];
        }

        return ['valid' => true, 'message' => 'Coupon applied successfully.'];
    }

    /**
     * Calculate discount amount in cents based on discount_type.
     */
    public function calculateDiscountCents(int $amountCents): int
    {
        if ($this->discount_type === 'percent') {
            $discount = (int) round(($amountCents * (float)$this->discount_value) / 100);
            return min($discount, $amountCents);
        }

        if ($this->discount_type === 'fixed') {
            $fixedCents = (int) round((float)$this->discount_value * 100);
            return min($fixedCents, $amountCents);
        }

        return 0; // trial_days does not reduce current subtotal directly
    }
}
