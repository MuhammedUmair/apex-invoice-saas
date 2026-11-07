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
    public string $targetLocale;

    public function __construct(Invoice $invoice, ?string $targetLocale = null)
    {
        $this->invoice = $invoice;
        $loc = $targetLocale ?? $invoice->language ?? $invoice->client?->preferred_language ?? 'en';
        $this->targetLocale = in_array($loc, ['en', 'ur', 'hi', 'tr', 'ar', 'es', 'fr', 'de']) ? $loc : 'en';
    }

    public function envelope(): Envelope
    {
        app()->setLocale($this->targetLocale);
        $workspaceName = $this->invoice->workspace->name ?? $this->invoice->workspace_snapshot['name'] ?? 'RedMaple';
        $subject = __("Invoice #:number from :workspace", [
            'number' => $this->invoice->invoice_number,
            'workspace' => $workspaceName,
        ], $this->targetLocale);

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        app()->setLocale($this->targetLocale);
        return new Content(
            view: 'emails.invoice-sent',
            with: [
                'invoice' => $this->invoice,
                'locale' => $this->targetLocale,
                'isRtl' => in_array($this->targetLocale, ['ur', 'ar']),
                'portalUrl' => route('invoices.portal', ['token' => $this->invoice->public_secure_token, 'lang' => $this->targetLocale]),
                'pdfUrl' => route('invoices.pdf.download', ['token' => $this->invoice->public_secure_token, 'lang' => $this->targetLocale]),
            ],
        );
    }
}
