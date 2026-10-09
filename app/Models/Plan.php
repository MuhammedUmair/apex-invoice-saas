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
}
