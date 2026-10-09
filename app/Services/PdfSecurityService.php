<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class PdfSecurityService
{
    /**
     * Generate an expiring signed URL for invoice PDF download.
     * Prevents sequential enumeration attacks and ensures unauthorized users cannot access files.
     */
    public function generateSignedDownloadUrl(Invoice $invoice, int $expirationMinutes = 60): string
    {
        return URL::temporarySignedRoute(
            'invoices.pdf.download',
            now()->addMinutes($expirationMinutes),
            ['token' => $invoice->public_secure_token]
        );
    }

    /**
     * Configure password protection on an invoice.
     */
    public function configurePassword(Invoice $invoice, ?string $rawPassword): void
    {
        if (!empty($rawPassword)) {
            $invoice->is_pdf_password_protected = true;
            $invoice->pdf_password_hash = Hash::make($rawPassword);
        } else {
            $invoice->is_pdf_password_protected = false;
            $invoice->pdf_password_hash = null;
        }

        $invoice->save();
    }

    /**
     * Verify client password before granting access to PDF stream.
     */
    public function verifyPassword(Invoice $invoice, ?string $rawPassword): bool
    {
        if (!$invoice->is_pdf_password_protected) {
            return true;
        }

        if (empty($rawPassword) || empty($invoice->pdf_password_hash)) {
            return false;
        }

        return Hash::check($rawPassword, $invoice->pdf_password_hash);
    }

    /**
     * Determine storage path inside private storage disk (outside public_html).
     */
    public function getPrivatePdfPath(Invoice $invoice): string
    {
        return "invoices/{$invoice->workspace_id}/{$invoice->invoice_number}.pdf";
    }

    /**
     * Save compiled PDF to private disk.
     */
    public function storePrivatePdf(Invoice $invoice, string $pdfContent): string
    {
        $path = $this->getPrivatePdfPath($invoice);
        Storage::disk('local')->put($path, $pdfContent);
        return $path;
    }
}
