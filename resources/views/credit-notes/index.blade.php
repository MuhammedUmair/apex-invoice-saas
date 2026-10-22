@extends('layouts.app', ['title' => 'Credit Notes - RedMaple', 'header' => 'Credit Notes & Refunds'])

@section('content')
<div class="space-y-6">

    <!-- Top Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Credit Notes Issued</span>
            <span class="text-2xl font-black text-slate-900 mt-1 block">{{ $creditNotes->total() }}</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Credited Value</span>
            <span class="text-2xl font-black text-rose-600 mt-1 block">${{ number_format($totalCreditedCents / 100, 2) }}</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Accounting Ledger</span>
            <span class="text-xs text-slate-500 mt-1.5 block">Audit-compliant financial adjustments & reversals</span>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-sm">Credit Notes Ledger</h3>
            <span class="text-xs text-slate-400">Issue new credit notes from any Invoice details page</span>
        </div>

        @if($creditNotes->isEmpty())
            <div class="p-12 text-center text-slate-400">
                <i class="ph ph-arrow-u-up-left text-4xl mb-2 inline-block"></i>
                <p class="text-sm font-medium">No credit notes issued yet.</p>
                <p class="text-xs text-slate-400 mt-1">To issue a credit note or refund, open an invoice and click "Issue Credit Note".</p>
            </div>
        @else
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5 font-bold">Credit Note #</th>
                        <th class="px-4 py-3.5 font-bold">Original Invoice</th>
                        <th class="px-4 py-3.5 font-bold">Client</th>
                        <th class="px-4 py-3.5 font-bold">Reason</th>
                        <th class="px-4 py-3.5 font-bold">Issue Date</th>
                        <th class="px-6 py-3.5 font-bold text-right">Credited Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($creditNotes as $cn)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-4">
                                <span class="font-mono font-bold text-rose-600">{{ $cn->credit_note_number }}</span>
                            </td>
                            <td class="px-4 py-4">
                                @if($cn->invoice)
                                    <a href="{{ route('invoices.show', $cn->invoice) }}" class="font-mono font-semibold text-slate-800 hover:text-red-600 transition">
                                        {{ $cn->invoice->invoice_number }}
                                    </a>
                                @else
                                    <span class="text-slate-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="font-medium text-slate-900">{{ $cn->client->name ?? 'Client' }}</div>
                                @if($cn->client && $cn->client->company_name)
                                    <div class="text-xs text-slate-400">{{ $cn->client->company_name }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-xs text-slate-600 max-w-xs truncate">
                                {{ $cn->reason }}
                            </td>
                            <td class="px-4 py-4 text-xs text-slate-500">
                                {{ $cn->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-mono font-bold text-rose-600">-{{ $cn->formatted_amount }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="p-4 border-t border-slate-100">
                {{ $creditNotes->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
