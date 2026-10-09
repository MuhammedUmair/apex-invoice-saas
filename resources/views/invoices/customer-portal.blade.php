@extends('layouts.guest')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10 space-y-8">
    <!-- Success Alert -->
    @if(session('payment_success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3 shadow-sm">
            <i class="ph ph-check-circle text-2xl text-emerald-600"></i>
            <div>
                <p class="font-bold">Payment Received!</p>
                <p class="text-xs text-emerald-700">{{ session('payment_success') }}</p>
            </div>
        </div>
    @endif

    <!-- Portal Header & Action Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-md p-6 sm:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Invoice from</span>
                <span class="text-sm font-bold text-slate-900">{{ $invoice->workspace_snapshot['name'] ?? $invoice->workspace->name }}</span>
            </div>
            <h1 class="text-3xl font-black text-slate-900 mt-1">{{ $invoice->invoice_number }}</h1>
            <p class="text-xs text-slate-500 mt-1">Due by <span class="font-bold text-slate-700">{{ $invoice->due_date->format('F d, Y') }}</span></p>
        </div>

        <div class="text-left md:text-right">
            <span class="text-xs uppercase font-bold text-slate-400 block tracking-wider">Amount Due</span>
            <div class="text-3xl font-black {{ $invoice->due_cents > 0 ? 'text-blue-600' : 'text-emerald-600' }}">
                {{ $invoice->formatted_due }}
            </div>
            <span class="inline-block mt-1 px-3 py-0.5 rounded-full text-xs font-black uppercase
                @if($invoice->status === 'paid') bg-emerald-100 text-emerald-800
                @elseif($invoice->status === 'partially_paid') bg-amber-100 text-amber-800
                @else bg-blue-100 text-blue-800 @endif">
                {{ $invoice->status === 'paid' ? 'Paid in Full' : 'Payment Outstanding' }}
            </span>
        </div>
    </div>

    <!-- Dual Payment Gateways (Stripe & PayPal) -->
    @if($invoice->due_cents > 0)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-md p-6 sm:p-8 space-y-4">
            <h3 class="font-bold text-slate-900 text-base">Select Online Payment Method</h3>
            <p class="text-xs text-slate-500">Pay securely online using credit card, debit card, or PayPal account.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <!-- Stripe Option -->
                <form method="POST" action="{{ route('invoices.pay', $invoice->public_secure_token) }}">
                    @csrf
                    <input type="hidden" name="gateway" value="stripe">
                    <button type="submit" class="w-full p-4 rounded-xl border-2 border-blue-600 bg-blue-50/50 hover:bg-blue-50 text-blue-900 flex items-center justify-between transition group shadow-sm hover:shadow">
                        <div class="text-left">
                            <span class="block font-bold text-sm">Pay with Credit Card</span>
                            <span class="text-[11px] text-blue-700">Visa, Mastercard, Amex via Stripe</span>
                        </div>
                        <i class="ph ph-credit-card text-2xl text-blue-600 group-hover:scale-110 transition-transform"></i>
                    </button>
                </form>

                <!-- PayPal Option -->
                <form method="POST" action="{{ route('invoices.pay', $invoice->public_secure_token) }}">
                    @csrf
                    <input type="hidden" name="gateway" value="paypal">
                    <button type="submit" class="w-full p-4 rounded-xl border-2 border-amber-500 bg-amber-50/50 hover:bg-amber-50 text-amber-900 flex items-center justify-between transition group shadow-sm hover:shadow">
                        <div class="text-left">
                            <span class="block font-bold text-sm">Pay with PayPal</span>
                            <span class="text-[11px] text-amber-700">PayPal balance or linked cards</span>
                        </div>
                        <i class="ph ph-wallet text-2xl text-amber-600 group-hover:scale-110 transition-transform"></i>
                    </button>
                </form>
            </div>
        </div>
    @endif

    <!-- Invoice Details Sheet -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-md p-8 space-y-8">
        <!-- Download PDF Action -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-6">
            <div>
                <h4 class="font-bold text-slate-900 text-sm">Official Invoice Record</h4>
                <p class="text-xs text-slate-500">Download or print your official document</p>
            </div>
            <a href="{{ route('invoices.pdf.download', $invoice->public_secure_token) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition shadow-sm">
                <i class="ph ph-download-simple text-base"></i>
                @if($invoice->is_pdf_password_protected)
                    Download Protected PDF <i class="ph ph-lock-key text-amber-400"></i>
                @else
                    Download Invoice PDF
                @endif
            </a>
        </div>

        <!-- Line Items -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 text-slate-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="py-3">Item Description</th>
                        <th class="py-3 text-center">Quantity</th>
                        <th class="py-3 text-right">Rate</th>
                        <th class="py-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($invoice->items as $item)
                        <tr>
                            <td class="py-4 font-bold text-slate-900">
                                {{ $item->description }}
                            </td>
                            <td class="py-4 text-center text-slate-600">
                                {{ $item->quantity }} {{ $item->unit }}
                            </td>
                            <td class="py-4 text-right font-medium text-slate-600">
                                ${{ $item->formatted_rate }}
                            </td>
                            <td class="py-4 text-right font-bold text-slate-900">
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
                    <span>Paid to Date:</span>
                    <span>${{ number_format($invoice->paid_cents / 100, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm font-black text-rose-600">
                    <span>Outstanding:</span>
                    <span>{{ $invoice->formatted_due }}</span>
                </div>
            </div>
        </div>

        <!-- Bank Details for Wire Transfer -->
        @if(!empty($invoice->workspace_snapshot['bank_details']))
            @php $bank = $invoice->workspace_snapshot['bank_details']; @endphp
            <div class="bg-slate-50 rounded-xl p-6 border border-slate-200 text-xs text-slate-700 space-y-2">
                <h4 class="font-bold text-slate-900 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    <i class="ph ph-bank text-base text-blue-600"></i> Direct Wire / Bank Transfer Instructions
                </h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Bank Name</span>
                        <span class="font-semibold">{{ $bank['bank_name'] ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Account Number</span>
                        <span class="font-mono font-semibold">{{ $bank['account_number'] ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Routing Number</span>
                        <span class="font-mono font-semibold">{{ $bank['routing_number'] ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">SWIFT / BIC</span>
                        <span class="font-mono font-semibold">{{ $bank['swift'] ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
