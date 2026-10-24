<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMailable extends Mailable
{
    use Queueable, SerializesModels;

    public Invoice $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function envelope(): Envelope
    {
        $workspaceName = $this->invoice->workspace->name ?? 'RedMaple';
        return new Envelope(
            subject: "Invoice #{$this->invoice->invoice_number} from {$workspaceName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice-sent',
            with: [
                'invoice' => $this->invoice,
                'portalUrl' => route('invoices.portal', $this->invoice->public_secure_token),
                'pdfUrl' => route('invoices.pdf.download', $this->invoice->public_secure_token),
            ],
        );
    }
}
