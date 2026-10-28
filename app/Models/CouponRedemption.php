<?php

namespace App\Models;

use App\Models\Traits\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponRedemption extends Model
{
    use HasFactory, HasUuids, BelongsToWorkspace;

    protected $fillable = [
        'coupon_id',
        'workspace_id',
        'user_id',
        'invoice_id',
        'client_id',
        'discount_applied_cents',
        'currency',
        'service_granted',
        'subscription_id',
        'gateway',
        'redeemed_at',
    ];

    protected $casts = [
        'discount_applied_cents' => 'integer',
        'redeemed_at' => 'datetime',
    ];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }
}
