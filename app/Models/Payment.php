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
        'payment_method',
        'reference_number',
        'amount_cents',
        'fee_cents',
        'net_amount_cents',
        'currency',
        'gateway_transaction_id',
        'idempotency_key',
        'status',
        'payment_method_details',
        'payment_date',
        'notes',
        'recorded_by_user_id',
        'voided_at',
        'void_reason',
        'voided_by_user_id',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'fee_cents' => 'integer',
        'net_amount_cents' => 'integer',
        'payment_method_details' => 'array',
        'payment_date' => 'datetime',
        'voided_at' => 'datetime',
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

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by_user_id');
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount_cents / 100, 2) . ' ' . $this->currency;
    }

    public function getFormattedFeeAttribute(): string
    {
        return number_format(($this->fee_cents ?? 0) / 100, 2) . ' ' . $this->currency;
    }

    public function getFormattedNetAmountAttribute(): string
    {
        return number_format(($this->net_amount_cents ?? $this->amount_cents) / 100, 2) . ' ' . $this->currency;
    }

    public function getMethodDisplayAttribute(): string
    {
        return match ($this->payment_method) {
            'interac_etransfer' => 'Interac e-Transfer',
            'bank_transfer' => 'Bank Wire / EFT',
            'cheque' => 'Cheque',
            'cash' => 'Cash',
            'other' => 'Other / Offline',
            default => ucfirst(str_replace('_', ' ', $this->payment_method ?: $this->gateway)),
        };
    }
}
