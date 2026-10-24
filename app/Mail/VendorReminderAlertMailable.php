<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VendorReminderAlertMailable extends Mailable
{
    use Queueable, SerializesModels;

    public Invoice $invoice;
    public int $daysBefore;

    public function __construct(Invoice $invoice, int $daysBefore)
    {
        $this->invoice = $invoice;
        $this->daysBefore = $daysBefore;
    }

    public function envelope(): Envelope
    {
        $clientName = $this->invoice->client->name ?? 'Customer';
        return new Envelope(
            subject: "Vendor Alert: Reminder Sent to {$clientName} for Invoice #{$this->invoice->invoice_number} ({$this->daysBefore}d before due)",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.vendor-reminder-alert',
            with: [
                'invoice' => $this->invoice,
                'daysBefore' => $this->daysBefore,
                'portalUrl' => route('invoices.portal', $this->invoice->public_secure_token),
                'adminUrl' => route('invoices.show', $this->invoice->id),
            ],
        );
    }
}
