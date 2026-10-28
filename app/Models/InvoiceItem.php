<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'invoice_id',
        'service_id',
        'description',
        'category',
        'quantity',
        'unit',
        'rate_cents',
        'tax_rate',
        'tax_cents',
        'amount_cents',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'rate_cents' => 'integer',
        'tax_rate' => 'decimal:2',
        'tax_cents' => 'integer',
        'amount_cents' => 'integer',
        'sort_order' => 'integer',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }

    public function getFormattedRateAttribute(): string
    {
        return number_format($this->rate_cents / 100, 2);
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount_cents / 100, 2);
    }

    public function getTitleAttribute(): string
    {
        return $this->description ?: 'Line Item';
    }
}
