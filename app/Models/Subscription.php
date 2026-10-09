<?php

namespace App\Models;

use App\Models\Traits\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory, HasUuids, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'plan_id',
        'gateway',
        'gateway_subscription_id',
        'gateway_customer_id',
        'status',
        'trial_ends_at',
        'current_period_ends_at',
        'canceled_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'current_period_ends_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function isTrialing(): bool
    {
        return $this->status === 'trialing' && $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ($this->current_period_ends_at === null || $this->current_period_ends_at->isFuture());
    }

    public function isValid(): bool
    {
        return $this->isTrialing() || $this->isActive();
    }
}
