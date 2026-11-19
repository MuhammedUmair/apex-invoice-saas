<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Workspace extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'logo_url',
        'brand_color',
        'secondary_color',
        'currency',
        'language',
        'country_code',
        'tax_id',
        'address',
        'phone',
        'email',
        'website',
        'bank_details',
        'stripe_keys_encrypted',
        'paypal_keys_encrypted',
        'smtp_settings_encrypted',
        'is_whitelabel_enabled',
    ];

    protected $casts = [
        'bank_details' => 'array',
        'stripe_keys_encrypted' => 'encrypted:array',
        'paypal_keys_encrypted' => 'encrypted:array',
        'smtp_settings_encrypted' => 'encrypted:array',
        'is_whitelabel_enabled' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function currentSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function couponRedemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function invoiceSequences(): HasMany
    {
        return $this->hasMany(InvoiceSequence::class);
    }

    /**
     * Check if workspace has active subscription or valid trial.
     */
    public function hasActiveAccess(): bool
    {
        $sub = $this->currentSubscription;
        if (!$sub) {
            return false;
        }

        return $sub->isValid();
    }

    /**
     * Check if workspace is currently on a Trial version.
     */
    public function isTrial(): bool
    {
        if (auth()->check() && auth()->user()->isSuperAdmin()) {
            return false;
        }

        $sub = $this->currentSubscription;
        if (!$sub) {
            return true;
        }

        // Subscriptions that are trialing or not active (canceled, expired, past_due) are treated as restricted/trial
        if (!in_array($sub->status, ['active'])) {
            return true;
        }

        // If subscription has expired past its period
        if ($sub->current_period_ends_at && $sub->current_period_ends_at->isPast()) {
            return true;
        }

        return (bool) ($sub->plan?->is_trial || $sub->plan?->code === 'trial');
    }

    /**
     * Check if workspace has properly configured custom outgoing SMTP credentials.
     */
    public function hasConfiguredSmtp(): bool
    {
        $smtp = $this->smtp_settings_encrypted;
        if (empty($smtp) || !is_array($smtp)) {
            return false;
        }

        return !empty($smtp['host']) &&
               !empty($smtp['username']) &&
               !empty($smtp['password']) &&
               !empty($smtp['from_address']);
    }

    /**
     * Return daily email send quota based on plan tier.
     */
    public function dailyEmailCap(): int
    {
        if ($this->isTrial()) {
            return 5; // Trial accounts capped at 5 sends/day to prevent spam relays
        }

        $planCode = $this->currentSubscription?->plan?->code;
        return match ($planCode) {
            'starter' => 50,
            'pro' => 250,
            'enterprise' => 1000,
            default => 25,
        };
    }

    /**
     * Check if workspace has reached daily email dispatch cap.
     */
    public function hasReachedDailyEmailCap(): bool
    {
        $today = now()->toDateString();
        if ($this->last_email_sent_date !== $today) {
            return false;
        }

        return ($this->daily_emails_sent_count ?? 0) >= $this->dailyEmailCap();
    }

    /**
     * Record an email send and increment daily counter.
     */
    public function incrementDailyEmailCount(): void
    {
        $today = now()->toDateString();
        if ($this->last_email_sent_date !== $today) {
            $this->last_email_sent_date = $today;
            $this->daily_emails_sent_count = 1;
        } else {
            $this->daily_emails_sent_count = ($this->daily_emails_sent_count ?? 0) + 1;
        }

        $this->save();
    }

    /**
     * Check if custom Stripe payment gateway is configured and active.
     */
    public function hasConfiguredStripe(): bool
    {
        $keys = $this->stripe_keys_encrypted;
        if (empty($keys) || !is_array($keys)) {
            return false;
        }
        return !empty($keys['publishable_key']) && !empty($keys['secret_key']);
    }

    /**
     * Check if custom PayPal payment gateway is configured and active.
     */
    public function hasConfiguredPaypal(): bool
    {
        $keys = $this->paypal_keys_encrypted;
        if (empty($keys) || !is_array($keys)) {
            return false;
        }
        return !empty($keys['client_id']) && !empty($keys['secret']);
    }

    /**
     * Check if Bank transfer remittance details are configured.
     */
    public function hasConfiguredBankDetails(): bool
    {
        $bank = is_array($this->bank_details) ? $this->bank_details : (json_decode($this->bank_details, true) ?? []);
        return !empty($bank['bank_name']) && !empty($bank['account_number']);
    }

    /**
     * Check if any online payment method (Stripe or PayPal) is configured for this workspace,
     * or if superadmin/system fallback payment gateway is available when owner is superadmin.
     */
    public function hasOnlinePaymentConfigured(): bool
    {
        if ($this->hasConfiguredStripe() || $this->hasConfiguredPaypal()) {
            return true;
        }

        // For SuperAdmin workspace, check if system-level gateway credentials are set in environment
        $owner = $this->users()->first();
        if ($owner && method_exists($owner, 'isSuperAdmin') && $owner->isSuperAdmin()) {
            $systemStripe = !empty(config('services.stripe.key')) && !empty(config('services.stripe.secret'));
            $systemPaypal = !empty(config('services.paypal.client_id')) && !empty(config('services.paypal.secret'));
            return $systemStripe || $systemPaypal;
        }

        return false;
    }

    /**
     * Check if Stripe is available (either workspace or superadmin system fallback).
     */
    public function isStripeAvailable(): bool
    {
        if ($this->hasConfiguredStripe()) {
            return true;
        }
        $owner = $this->users()->first();
        if ($owner && method_exists($owner, 'isSuperAdmin') && $owner->isSuperAdmin()) {
            return !empty(config('services.stripe.key')) && !empty(config('services.stripe.secret'));
        }
        return false;
    }

    /**
     * Check if PayPal is available (either workspace or superadmin system fallback).
     */
    public function isPaypalAvailable(): bool
    {
        if ($this->hasConfiguredPaypal()) {
            return true;
        }
        $owner = $this->users()->first();
        if ($owner && method_exists($owner, 'isSuperAdmin') && $owner->isSuperAdmin()) {
            return !empty(config('services.paypal.client_id')) && !empty(config('services.paypal.secret'));
        }
        return false;
    }
}
