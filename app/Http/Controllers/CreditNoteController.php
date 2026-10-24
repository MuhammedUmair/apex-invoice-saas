<?php

namespace App\Http\Controllers;

use App\Models\CreditNote;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreditNoteController extends Controller
{
    public function index(Request $request)
    {
        $creditNotes = CreditNote::with(['invoice', 'client'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $totalCreditedCents = CreditNote::sum('amount_cents');

        return view('credit-notes.index', [
            'creditNotes' => $creditNotes,
            'totalCreditedCents' => $totalCreditedCents,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|uuid|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $invoice = Invoice::findOrFail($validated['invoice_id']);
        $amountCents = (int)round($validated['amount'] * 100);

        if ($amountCents > $invoice->total_cents) {
            return back()->with('error', 'Credit note amount cannot exceed the total invoice amount (' . $invoice->formatted_total . ').');
        }

        // Generate Credit Note Number CN-YYYY-XXXX
        $year = date('Y');
        $count = CreditNote::whereYear('created_at', $year)->count() + 1;
        $cnNumber = sprintf('CN-%s-%04d', $year, $count);

        DB::transaction(function () use ($invoice, $amountCents, $validated, $cnNumber) {
            CreditNote::create([
                'invoice_id' => $invoice->id,
                'client_id' => $invoice->client_id,
                'credit_note_number' => $cnNumber,
                'reason' => $validated['reason'],
                'amount_cents' => $amountCents,
                'tax_cents' => 0,
                'currency' => $invoice->currency,
                'status' => 'issued',
                'credit_date' => now(),
                'notes' => $validated['notes'] ?? null,
                'created_by_user_id' => Auth::id(),
            ]);

            // If the credit note covers the entire invoice, mark as credited
            $totalCredited = $invoice->creditNotes()->sum('amount_cents') + $amountCents;
            if ($totalCredited >= $invoice->total_cents) {
                $invoice->status = 'credited';
                $invoice->save();
            }
        });

        return back()->with('success', "Credit Note {$cnNumber} successfully issued for {$invoice->invoice_number}.");
    }
}
