<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\PdfSecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    protected PdfSecurityService $pdfService;

    public function __construct(PdfSecurityService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function index(Request $request)
    {
        $status = $request->query('status');

        $invoices = Invoice::with('client')
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('invoices.index', [
            'invoices' => $invoices,
            'currentStatus' => $status,
        ]);
    }

    public function create()
    {
        return view('invoices.create');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['client', 'items', 'payments', 'creditNotes']);
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Public Customer Portal for Viewing & Paying Invoices.
     * Does NOT require user authentication - secured by cryptographic token.
     */
    public function customerPortal(string $token)
    {
        $invoice = Invoice::withoutGlobalScopes()
            ->where('public_secure_token', $token)
            ->with(['client', 'items', 'workspace'])
            ->firstOrFail();

        return view('invoices.customer-portal', compact('invoice'));
    }

    /**
     * Password challenge page for protected customer PDFs.
     */
    public function pdfChallenge(string $token)
    {
        $invoice = Invoice::withoutGlobalScopes()
            ->where('public_secure_token', $token)
            ->firstOrFail();

        return view('invoices.pdf-challenge', compact('invoice'));
    }

    /**
     * Download or stream PDF invoice with optional password encryption.
     */
    public function downloadPdf(Request $request, string $token)
    {
        $invoice = Invoice::withoutGlobalScopes()
            ->where('public_secure_token', $token)
            ->with(['client', 'items', 'workspace'])
            ->firstOrFail();

        if ($invoice->is_pdf_password_protected) {
            $password = $request->input('password') ?? session("pdf_auth_{$invoice->id}");
            if (!$this->pdfService->verifyPassword($invoice, $password)) {
                return redirect()->route('invoices.pdf.challenge', $token)
                    ->withErrors(['password' => 'Incorrect password entered. This invoice document is protected.']);
            }
            // Store successful password check in session for duration of viewing
            session(["pdf_auth_{$invoice->id}" => $password]);
        }

        // Return clean printable PDF view (which supports standard browser PDF save or mPDF)
        return view('invoices.pdf-template', compact('invoice'));
    }
}
