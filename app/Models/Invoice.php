<?php

namespace App\Models;

use App\Models\Traits\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory, HasUuids, SoftDeletes, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'client_id',
        'invoice_number',
        'status',
        'issue_date',
        'due_date',
        'currency',
        'subtotal_cents',
        'tax_name',
        'tax_rate',
        'tax_cents',
        'discount_cents',
        'total_cents',
        'paid_cents',
        'due_cents',
        'notes',
        'terms',
        'is_pdf_password_protected',
        'pdf_password_hash',
        'public_secure_token',
        'client_snapshot',
        'workspace_snapshot',
        'language',
        'created_by_user_id',
        'sent_at',
        'paid_at',
        'scheduled_publish_date',
        'is_scheduled',
        'scheduled_send_email',
        'scheduled_alert_vendor',
        'has_late_surcharge',
        'late_surcharge_type',
        'late_surcharge_value',
        'late_surcharge_days',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'scheduled_publish_date' => 'date',
        'is_scheduled' => 'boolean',
        'scheduled_send_email' => 'boolean',
        'scheduled_alert_vendor' => 'boolean',
        'has_late_surcharge' => 'boolean',
        'late_surcharge_value' => 'decimal:2',
        'late_surcharge_days' => 'integer',
        'is_pdf_password_protected' => 'boolean',
        'client_snapshot' => 'array',
        'workspace_snapshot' => 'array',
        'subtotal_cents' => 'integer',
        'tax_cents' => 'integer',
        'discount_cents' => 'integer',
        'total_cents' => 'integer',
        'paid_cents' => 'integer',
        'due_cents' => 'integer',
        'tax_rate' => 'decimal:2',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->public_secure_token)) {
                $invoice->public_secure_token = Str::random(48);
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order', 'asc');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(InvoiceReminder::class)->orderBy('created_at', 'desc');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function verifyPdfPassword(string $password): bool
    {
        if (!$this->is_pdf_password_protected || empty($this->pdf_password_hash)) {
            return true;
        }

        return Hash::check($password, $this->pdf_password_hash);
    }

    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total_cents / 100, 2) . ' ' . $this->currency;
    }

    public function getFormattedDueAttribute(): string
    {
        return number_format($this->due_cents / 100, 2) . ' ' . $this->currency;
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return number_format($this->subtotal_cents / 100, 2) . ' ' . $this->currency;
    }

    public function getFormattedTaxAttribute(): string
    {
        return number_format($this->tax_cents / 100, 2) . ' ' . $this->currency;
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid' || $this->due_cents === 0;
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date && $this->due_date->isPast();
    }

    public function getLateSurchargeCents(): int
    {
        if (!$this->has_late_surcharge || !$this->isOverdue()) {
            return 0;
        }

        $daysOverdue = (int) $this->due_date->diffInDays(now()->startOfDay());
        if ($daysOverdue < ($this->late_surcharge_days ?: 0)) {
            return 0;
        }

        if ($this->late_surcharge_type === 'fixed') {
            return (int) round(((float)$this->late_surcharge_value) * 100);
        }

        // Percentage
        return (int) round(($this->due_cents * ((float)$this->late_surcharge_value)) / 100);
    }

    public function getFormattedLateSurchargeAttribute(): string
    {
        return number_format($this->getLateSurchargeCents() / 100, 2) . ' ' . $this->currency;
    }

    public function getTotalWithSurchargeCentsAttribute(): int
    {
        return $this->due_cents + $this->getLateSurchargeCents();
    }

    public function getFormattedTotalWithSurchargeAttribute(): string
    {
        return number_format($this->total_with_surcharge_cents / 100, 2) . ' ' . $this->currency;
    }
}
