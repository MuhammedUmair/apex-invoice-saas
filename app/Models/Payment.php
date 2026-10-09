<?php

namespace App\Models;

use App\Models\Traits\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, HasUuids, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'invoice_id',
        'client_id',
        'payment_number',
        'gateway',
        'amount_cents',
        'currency',
        'gateway_transaction_id',
        'idempotency_key',
        'status',
        'payment_method_details',
        'payment_date',
        'notes',
        'recorded_by_user_id',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'payment_method_details' => 'array',
        'payment_date' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount_cents / 100, 2) . ' ' . $this->currency;
    }
}
