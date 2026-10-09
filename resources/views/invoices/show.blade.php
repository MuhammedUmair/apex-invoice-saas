@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-200 gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black text-slate-900">{{ $invoice->invoice_number }}</h1>
                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold uppercase
                    @if($invoice->status === 'paid') bg-emerald-100 text-emerald-800
                    @elseif($invoice->status === 'partially_paid') bg-amber-100 text-amber-800
                    @elseif($invoice->status === 'issued') bg-blue-100 text-blue-800
                    @else bg-slate-100 text-slate-800 @endif">
                    {{ $invoice->status }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Issued {{ $invoice->issue_date->format('F d, Y') }} &bull; Due {{ $invoice->due_date->format('F d, Y') }}</p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ route('invoices.pdf.download', $invoice->public_secure_token) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl shadow-sm transition">
                <i class="ph ph-file-pdf text-base"></i> View / Print PDF
            </a>
            <a href="{{ route('invoices.portal', $invoice->public_secure_token) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                <i class="ph ph-arrow-square-out text-base"></i> Customer Pay Portal
            </a>
        </div>
    </div>

    <!-- Customer Link Share Banner -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="text-xs text-blue-900">
            <span class="font-bold">Public Secure Customer URL:</span>
            <span class="font-mono text-blue-700 block truncate max-w-lg">{{ route('invoices.portal', $invoice->public_secure_token) }}</span>
        </div>
        <button onclick="navigator.clipboard.writeText('{{ route('invoices.portal', $invoice->public_secure_token) }}'); alert('Customer portal link copied to clipboard!');"
                class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-lg shadow-sm whitespace-nowrap">
            Copy Link
        </button>
    </div>

    <!-- Invoice Content Paper -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 space-y-8">
        <!-- Invoice Parties -->
        <div class="grid grid-cols-2 gap-8 border-b border-slate-100 pb-8">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">From</p>
                <h3 class="font-bold text-slate-900 text-base">{{ $invoice->workspace_snapshot['name'] ?? auth()->user()->workspace->name }}</h3>
                <p class="text-xs text-slate-500 whitespace-pre-line mt-1">{{ $invoice->workspace_snapshot['address'] ?? auth()->user()->workspace->address }}</p>
                @if(!empty($invoice->workspace_snapshot['tax_id']))
                    <p class="text-xs text-slate-500 mt-1">Tax ID: <span class="font-mono">{{ $invoice->workspace_snapshot['tax_id'] }}</span></p>
                @endif
            </div>

            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Billed To</p>
                <h3 class="font-bold text-slate-900 text-base">{{ $invoice->client_snapshot['company_name'] ?? $invoice->client->company_name ?? $invoice->client->name }}</h3>
                <p class="text-xs text-slate-500 whitespace-pre-line mt-1">{{ $invoice->client_snapshot['name'] ?? $invoice->client->name }}</p>
                <p class="text-xs text-slate-500">{{ $invoice->client_snapshot['email'] ?? $invoice->client->email }}</p>
                @if(!empty($invoice->client_snapshot['tax_id']))
                    <p class="text-xs text-slate-500 mt-1">VAT/Tax ID: <span class="font-mono">{{ $invoice->client_snapshot['tax_id'] }}</span></p>
                @endif
            </div>
        </div>

        <!-- Line Items Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 text-slate-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="py-3">Description</th>
                        <th class="py-3 text-center">Qty</th>
                        <th class="py-3 text-right">Rate</th>
                        <th class="py-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($invoice->items as $item)
                        <tr>
                            <td class="py-3.5 pr-4">
                                <div class="font-bold text-slate-900">{{ $item->description }}</div>
                                @if($item->category)
                                    <span class="text-[11px] text-slate-400 uppercase">{{ $item->category }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 text-center text-slate-600">
                                {{ $item->quantity }} <span class="text-xs text-slate-400">{{ $item->unit }}</span>
                            </td>
                            <td class="py-3.5 text-right font-medium text-slate-600">
                                ${{ $item->formatted_rate }}
                            </td>
                            <td class="py-3.5 text-right font-bold text-slate-900">
                                ${{ $item->formatted_amount }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals Breakdown -->
        <div class="flex justify-end pt-4 border-t border-slate-100">
            <div class="w-72 space-y-2 text-sm">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal:</span>
                    <span class="font-semibold text-slate-900">{{ $invoice->formatted_subtotal }}</span>
                </div>
                @if($invoice->discount_cents > 0)
                    <div class="flex justify-between text-emerald-600">
                        <span>Discount:</span>
                        <span class="font-semibold">-${{ number_format($invoice->discount_cents / 100, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-slate-600">
                    <span>{{ $invoice->tax_name }} ({{ number_format($invoice->tax_rate, 2) }}%):</span>
                    <span class="font-semibold text-slate-900">+{{ $invoice->formatted_tax }}</span>
                </div>
                <div class="flex justify-between border-t border-slate-200 pt-2 text-base font-black text-slate-900">
                    <span>Total Amount:</span>
                    <span class="text-blue-600">{{ $invoice->formatted_total }}</span>
                </div>
                <div class="flex justify-between text-xs font-bold text-slate-500 pt-1">
                    <span>Amount Paid:</span>
                    <span>${{ number_format($invoice->paid_cents / 100, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm font-black text-rose-600">
                    <span>Balance Due:</span>
                    <span>{{ $invoice->formatted_due }}</span>
                </div>
            </div>
        </div>

        @if($invoice->notes)
            <div class="pt-4 border-t border-slate-100 text-xs text-slate-600">
                <p class="font-bold text-slate-700 uppercase tracking-wider mb-1">Notes</p>
                <p class="whitespace-pre-line">{{ $invoice->notes }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
