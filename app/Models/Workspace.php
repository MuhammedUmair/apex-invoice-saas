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
}
