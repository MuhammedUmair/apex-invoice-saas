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
        'business_category',
        'slug',
        'logo_url',
        'brand_color',
        'secondary_color',
        'currency',
        'language',
        'default_language',
        'country_code',
        'country',
        'province',
        'tax_id',
        'default_tax_rate',
        'default_tax_name',
        'cra_bn_number',
        'qst_number',
        'small_supplier_exempt',
        'collect_qst',
        'collect_pst',
        'address',
        'phone',
        'email',
        'website',
        'bank_details',
        'bank_transfer_instructions',
        'enable_paypal',
        'enable_bank_transfer',
        'stripe_keys_encrypted',
        'paypal_keys_encrypted',
        'smtp_settings_encrypted',
        'is_whitelabel_enabled',
    ];

    protected $casts = [
        'bank_details' => 'array',
        'enable_paypal' => 'boolean',
        'enable_bank_transfer' => 'boolean',
        'small_supplier_exempt' => 'boolean',
        'collect_qst' => 'boolean',
        'collect_pst' => 'boolean',
        'default_tax_rate' => 'decimal:3',
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
        if (isset($this->enable_paypal) && !$this->enable_paypal) {
            return false;
        }

        $keys = $this->paypal_keys_encrypted;
        if (empty($keys) || !is_array($keys)) {
            return false;
        }
        return !empty($keys['client_id']) && !empty($keys['secret']);
    }

    /**
     * Check if Bank transfer / Interac e-Transfer is enabled and configured.
     */
    public function hasBankTransferAvailable(): bool
    {
        $enabled = $this->enable_bank_transfer ?? true;
        return $enabled && (!empty($this->bank_transfer_instructions) || $this->hasConfiguredBankDetails());
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
            $systemPaypal = (!isset($this->enable_paypal) || $this->enable_paypal) && !empty(config('services.paypal.client_id')) && !empty(config('services.paypal.secret'));
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
        if (isset($this->enable_paypal) && !$this->enable_paypal) {
            return false;
        }

        if ($this->hasConfiguredPaypal()) {
            return true;
        }
        $owner = $this->users()->first();
        if ($owner && method_exists($owner, 'isSuperAdmin') && $owner->isSuperAdmin()) {
            return !empty(config('services.paypal.client_id')) && !empty(config('services.paypal.secret'));
        }
        return false;
    }

    /**
     * Virtual mapping for default_language -> language.
     */
    public function getDefaultLanguageAttribute(): ?string
    {
        return $this->language;
    }

    public function setDefaultLanguageAttribute(?string $value): void
    {
        $this->attributes['language'] = $value;
        if (\Illuminate\Support\Facades\Schema::hasColumn('workspaces', 'default_language')) {
            $this->attributes['default_language'] = $value;
        }
    }

    /**
     * Virtual mapping for country -> country_code.
     */
    public function getCountryAttribute(): ?string
    {
        return $this->country_code;
    }

    public function setCountryAttribute(?string $value): void
    {
        $code = $value ? strtoupper(trim($value)) : null;
        $this->attributes['country_code'] = $code;
        if (\Illuminate\Support\Facades\Schema::hasColumn('workspaces', 'country')) {
            $this->attributes['country'] = $code;
        }
    }

    /**
     * Default tax name accessor / mutator with country preset defaults.
     */
    public function getDefaultTaxNameAttribute(): ?string
    {
        if (!empty($this->attributes['default_tax_name'])) {
            return $this->attributes['default_tax_name'];
        }
        return match(strtoupper($this->country_code ?? 'US')) {
            'CA' => 'GST/HST/QST',
            'AE' => 'VAT',
            'SA' => 'VAT (ZATCA)',
            'GB', 'DE', 'FR', 'ES', 'IT' => 'VAT',
            'TR' => 'KDV',
            'AU', 'IN' => 'GST',
            default => 'Sales Tax',
        };
    }

    public function setDefaultTaxNameAttribute(?string $value): void
    {
        if (\Illuminate\Support\Facades\Schema::hasColumn('workspaces', 'default_tax_name')) {
            $this->attributes['default_tax_name'] = $value;
        }
    }

    /**
     * Check if workspace operates under Canadian taxation jurisdiction.
     */
    public function isCanadianWorkspace(): bool
    {
        return strtoupper($this->country_code ?? '') === 'CA';
    }
}
