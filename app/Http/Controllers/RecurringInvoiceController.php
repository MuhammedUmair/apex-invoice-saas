<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceMailable;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\RecurringInvoice;
use App\Services\InvoiceNumberService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RecurringInvoiceController extends Controller
{
    public function index()
    {
        $recurring = RecurringInvoice::with('client')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $clients = Client::orderBy('company_name')->get();

        return view('recurring.index', compact('recurring', 'clients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'frequency' => 'required|in:monthly,quarterly,yearly',
            'day_of_month' => 'required|integer|min:1|max:28',
            'require_vendor_approval' => 'nullable|boolean',
            'auto_send_email' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $workspace = Auth::user()->workspace;
        $amountCents = (int) round($data['amount'] * 100);

        // Next run date calculation (e.g. 1st of upcoming month)
        $nextRun = Carbon::now()->startOfMonth()->day($data['day_of_month']);
        if ($nextRun->isPast()) {
            $nextRun->addMonth();
        }

        $items = [
            [
                'description' => $data['title'],
                'category' => 'Retainer',
                'quantity' => 1,
                'unit' => 'month',
                'rate_cents' => $amountCents,
                'amount_cents' => $amountCents,
            ]
        ];

        RecurringInvoice::create([
            'workspace_id' => $workspace->id,
            'client_id' => $data['client_id'],
            'title' => $data['title'],
            'currency' => $data['currency'],
            'amount_cents' => $amountCents,
            'frequency' => $data['frequency'],
            'day_of_month' => $data['day_of_month'],
            'require_vendor_approval' => $request->boolean('require_vendor_approval', true),
            'auto_send_email' => $request->boolean('auto_send_email', true),
            'items' => $items,
            'notes' => $data['notes'] ?? '',
            'next_run_date' => $nextRun,
            'status' => 'active',
        ]);

        return back()->with('success', "Recurring schedule '{$data['title']}' created! Next cycle runs on {$nextRun->format('M d, Y')}.");
    }

    /**
     * Vendor review & approval action: generate invoice and email client.
     */
    public function approveAndSend(RecurringInvoice $recurring, InvoiceNumberService $numberService)
    {
        $recurring->load(['client', 'workspace']);
        $workspace = $recurring->workspace;
        $client = $recurring->client;

        $invoiceNumber = $numberService->generateNextNumber($workspace);

        $invoice = Invoice::create([
            'workspace_id' => $workspace->id,
            'client_id' => $client->id,
            'invoice_number' => $invoiceNumber,
            'status' => 'sent',
            'issue_date' => now(),
            'due_date' => now()->addDays(14),
            'currency' => $recurring->currency,
            'subtotal_cents' => $recurring->amount_cents,
            'total_cents' => $recurring->amount_cents,
            'due_cents' => $recurring->amount_cents,
            'notes' => $recurring->notes ?: "Monthly recurring billing for: {$recurring->title}",
            'created_by_user_id' => Auth::id(),
            'sent_at' => now(),
        ]);

        foreach ($recurring->items as $sort => $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'category' => $item['category'] ?? 'Retainer',
                'quantity' => 1,
                'unit' => $item['unit'] ?? 'month',
                'rate_cents' => $item['rate_cents'],
                'amount_cents' => $item['amount_cents'],
                'sort_order' => $sort,
            ]);
        }

        // Send email to customer
        try {
            Mail::to($client->email)->send(new InvoiceMailable($invoice));
        } catch (\Throwable $e) {
            Log::error("Failed to email recurring invoice: " . $e->getMessage());
        }

        // Advance next run date
        $recurring->last_run_date = now();
        $recurring->next_run_date = Carbon::parse($recurring->next_run_date)->addMonth();
        $recurring->status = 'active';
        $recurring->save();

        return redirect()->route('invoices.show', $invoice->id)->with('message', "Recurring invoice {$invoiceNumber} approved & dispatched to customer!");
    }

    public function toggleStatus(RecurringInvoice $recurring)
    {
        $recurring->status = $recurring->status === 'active' ? 'paused' : 'active';
        $recurring->save();

        return back()->with('success', "Recurring schedule status changed to {$recurring->status}.");
    }
}
