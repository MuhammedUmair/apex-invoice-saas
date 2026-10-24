<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'days_before',
        'recipient_customer_email',
        'recipient_vendor_email',
        'customer_notified_at',
        'vendor_notified_at',
        'status',
        'error_message',
    ];

    protected $casts = [
        'customer_notified_at' => 'datetime',
        'vendor_notified_at' => 'datetime',
        'days_before' => 'integer',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
