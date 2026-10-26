<?php

namespace App\Models;

use App\Models\Traits\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringInvoice extends Model
{
    use HasFactory, HasUuids, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'client_id',
        'title',
        'currency',
        'amount_cents',
        'frequency',
        'day_of_month',
        'require_vendor_approval',
        'auto_send_email',
        'items',
        'notes',
        'next_run_date',
        'last_run_date',
        'status',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'day_of_month' => 'integer',
        'require_vendor_approval' => 'boolean',
        'auto_send_email' => 'boolean',
        'items' => 'array',
        'next_run_date' => 'date',
        'last_run_date' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount_cents / 100, 2) . ' ' . $this->currency;
    }
}
