<?php

namespace App\Models;

use App\Models\Traits\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, HasUuids, SoftDeletes, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'name',
        'company_name',
        'email',
        'phone',
        'tax_id',
        'address',
        'city',
        'state',
        'postal_code',
        'country_code',
        'preferred_currency',
        'preferred_language',
        'default_payment_terms',
        'is_tax_exempt',
        'notes',
    ];

    protected $casts = [
        'is_tax_exempt' => 'boolean',
    ];

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

    public function getDisplayNameAttribute(): string
    {
        return $this->company_name ? "{$this->company_name} ({$this->name})" : $this->name;
    }

    /**
     * Compute total outstanding unpaid invoice balance for this client.
     */
    public function getOutstandingBalanceCentsAttribute(): int
    {
        return (int) $this->invoices()
            ->whereIn('status', ['issued', 'sent', 'partially_paid', 'overdue'])
            ->sum('due_cents');
    }
}
