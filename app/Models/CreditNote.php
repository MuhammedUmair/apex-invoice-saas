<?php

namespace App\Models;

use App\Models\Traits\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditNote extends Model
{
    use HasFactory, HasUuids, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'invoice_id',
        'client_id',
        'credit_note_number',
        'reason',
        'amount_cents',
        'tax_cents',
        'currency',
        'status',
        'credit_date',
        'notes',
        'created_by_user_id',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'tax_cents' => 'integer',
        'credit_date' => 'date',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount_cents / 100, 2) . ' ' . $this->currency;
    }
}
