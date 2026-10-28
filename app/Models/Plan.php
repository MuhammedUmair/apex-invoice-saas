<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'code',
        'price_cents',
        'currency',
        'billing_interval',
        'invoice_limit',
        'client_limit',
        'has_whitelabel',
        'has_client_portal',
        'has_recurring',
        'team_seats',
        'stripe_price_id',
        'paypal_plan_id',
        'features_json',
        'is_active',
    ];

    protected $casts = [
        'features_json' => 'array',
        'is_active' => 'boolean',
        'has_whitelabel' => 'boolean',
        'has_client_portal' => 'boolean',
        'has_recurring' => 'boolean',
        'price_cents' => 'integer',
        'invoice_limit' => 'integer',
        'client_limit' => 'integer',
        'team_seats' => 'integer',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price_cents / 100, 2) . ' ' . strtoupper($this->currency);
    }

    public function getPriceDollarsAttribute(): float
    {
        return round($this->price_cents / 100, 2);
    }

    public function isUnlimitedInvoices(): bool
    {
        return $this->invoice_limit === -1 || $this->invoice_limit === null;
    }

    public function isUnlimitedClients(): bool
    {
        return $this->client_limit === -1 || $this->client_limit === null;
    }

    public function getFeaturesListTextAttribute(): string
    {
        if (is_array($this->features_json)) {
            return implode("\n", $this->features_json);
        }
        return '';
    }

    /**
     * Check if workspace can create invoice within this billing cycle
     */
    public function canCreateInvoice(Workspace $workspace): bool
    {
        if ($this->isUnlimitedInvoices()) {
            return true;
        }

        $currentMonthCount = $workspace->invoices()
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        return $currentMonthCount < $this->invoice_limit;
    }

    /**
     * Check if workspace can create client
     */
    public function canCreateClient(Workspace $workspace): bool
    {
        if ($this->isUnlimitedClients()) {
            return true;
        }

        return $workspace->clients()->count() < $this->client_limit;
    }
}
