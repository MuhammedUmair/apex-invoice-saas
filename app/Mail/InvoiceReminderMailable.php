<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceReminderMailable extends Mailable
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
        $workspaceName = $this->invoice->workspace->name ?? 'RedMaple';
        $dayText = $this->daysBefore > 0 
            ? "is due in {$this->daysBefore} days" 
            : ($this->daysBefore === 0 ? "is due today" : "is overdue");

        return new Envelope(
            subject: "Payment Reminder: Invoice #{$this->invoice->invoice_number} {$dayText} - {$workspaceName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-reminder',
            with: [
                'invoice' => $this->invoice,
                'daysBefore' => $this->daysBefore,
                'portalUrl' => route('invoices.portal', $this->invoice->public_secure_token),
                'pdfUrl' => route('invoices.pdf.download', $this->invoice->public_secure_token),
            ],
        );
    }
}
