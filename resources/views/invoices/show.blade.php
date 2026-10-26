@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ showCreditModal: false }">
    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-200 gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black text-slate-900">{{ $invoice->invoice_number }}</h1>
                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold uppercase
                    @if($invoice->status === 'paid') bg-emerald-100 text-emerald-800
                    @elseif($invoice->status === 'partially_paid') bg-amber-100 text-amber-800
                    @elseif($invoice->status === 'issued') bg-blue-100 text-blue-800
                    @elseif($invoice->status === 'cancelled') bg-rose-100 text-rose-800
                    @else bg-slate-100 text-slate-800 @endif">
                    {{ $invoice->status }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Issued {{ $invoice->issue_date->format('F d, Y') }} &bull; Due {{ $invoice->due_date->format('F d, Y') }}</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap" x-data="{ openActions: false }">
            <a href="{{ route('invoices.edit', $invoice->id) }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl border border-slate-300 shadow-xs transition">
                <i class="ph ph-pencil-simple text-base text-slate-500"></i> Revise
            </a>

            <a href="{{ route('invoices.pdf.download', $invoice->public_secure_token) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-xs transition">
                <i class="ph ph-file-pdf text-base text-slate-300"></i> PDF
            </a>

            <a href="{{ route('invoices.portal', $invoice->public_secure_token) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                <i class="ph ph-arrow-square-out text-base"></i> Pay Portal
            </a>

            <!-- More Actions Dropdown Menu -->
            <div class="relative">
                <button @click="openActions = !openActions" @click.outside="openActions = false" type="button"
                        class="inline-flex items-center gap-1 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl border border-slate-300 transition">
                    <span>More</span>
                    <i class="ph ph-caret-down text-xs"></i>
                </button>

                <div x-show="openActions" x-cloak 
                     class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-200 py-1.5 z-50 text-xs">
                    
                    @if($invoice->client && !empty($invoice->client->email))
                        <form action="{{ route('invoices.send', $invoice->id) }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 hover:bg-slate-50 flex items-center gap-2 text-slate-700 font-semibold">
                                <i class="ph ph-paper-plane-tilt text-emerald-600 text-sm"></i>
                                Send to Client Email
                            </button>
                        </form>
                    @endif

                    @if($invoice->due_cents > 0)
                        <form action="{{ route('invoices.reminder', $invoice->id) }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 hover:bg-slate-50 flex items-center gap-2 text-slate-700 font-semibold">
                                <i class="ph ph-bell-ringing text-amber-600 text-sm"></i>
                                Send Payment Reminder
                            </button>
                        </form>
                    @endif

                    <button @click="showCreditModal = true; openActions = false" type="button"
                            class="w-full text-left px-4 py-2 hover:bg-slate-50 flex items-center gap-2 text-rose-700 font-semibold border-t border-slate-100">
                        <i class="ph ph-arrow-u-up-left text-rose-600 text-sm"></i>
                        Issue Credit Note
                    </button>

                    <button onclick="navigator.clipboard.writeText('{{ route('invoices.portal', $invoice->public_secure_token) }}'); alert('Customer portal link copied!');"
                            @click="openActions = false" type="button"
                            class="w-full text-left px-4 py-2 hover:bg-slate-50 flex items-center gap-2 text-slate-600 font-medium">
                        <i class="ph ph-copy text-slate-500 text-sm"></i>
                        Copy Portal Link
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Link Share Banner -->
    <div class="bg-red-50/70 border border-red-200 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="text-xs text-red-900">
            <span class="font-bold flex items-center gap-1.5 mb-0.5">
                <svg class="w-3.5 h-3.5 text-red-600" viewBox="0 0 900 900" fill="currentColor">
                    <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                </svg>
                Public Secure Customer URL:
            </span>
            <span class="font-mono text-red-700 block truncate max-w-lg">{{ route('invoices.portal', $invoice->public_secure_token) }}</span>
        </div>
        <button onclick="navigator.clipboard.writeText('{{ route('invoices.portal', $invoice->public_secure_token) }}'); alert('Customer portal link copied to clipboard!');"
                class="px-3.5 py-1.5 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg shadow-sm whitespace-nowrap">
            Copy Link
        </button>
    </div>

    <!-- Automated Milestone Reminders Timeline Card -->
    @php
        $daysRemaining = (int) \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($invoice->due_date)->startOfDay(), false);
        $sentMilestones = $invoice->reminders->pluck('days_before')->toArray();
    @endphp
    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
                <i class="ph ph-bell-ringing text-red-600 text-lg"></i>
                <h4 class="font-bold text-sm text-slate-900">Payment Reminder Automation</h4>
                <span class="text-xs text-slate-500">(Milestones: 45, 30, 15, 7, 3, 2 days before due)</span>
            </div>
            @if($invoice->due_cents > 0)
                <form action="{{ route('invoices.reminder', $invoice->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 text-xs px-3 py-1 bg-red-50 hover:bg-red-100 text-red-700 font-bold rounded-lg border border-red-200 transition">
                        <i class="ph ph-paper-plane-tilt"></i> Send Manual Reminder Now
                    </button>
                </form>
            @endif
        </div>

        <div class="flex items-center gap-2 flex-wrap text-xs pt-1">
            <span class="text-slate-500 font-medium">Schedule Progress:</span>
            @foreach([45, 30, 15, 7, 3, 2] as $m)
                @php
                    $isSent = in_array($m, $sentMilestones, true);
                    $isPast = $daysRemaining < $m;
                @endphp
                <span class="px-2.5 py-1 rounded-lg font-bold border {{ $isSent ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : ($isPast ? 'bg-slate-100 text-slate-400 border-slate-200' : 'bg-blue-50 text-blue-700 border-blue-200') }}">
                    {{ $m }} Days Before {{ $isSent ? '✓ Sent' : ($isPast ? '(Passed)' : '') }}
                </span>
            @endforeach
        </div>

        @if($invoice->reminders->isNotEmpty())
            <div class="text-xs text-slate-500 pt-1">
                Latest dispatch: {{ $invoice->reminders->first()->created_at->format('M d, Y H:i') }} to {{ $invoice->reminders->first()->recipient_customer_email }}
            </div>
        @endif
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
                @if(!empty($invoice->workspace_snapshot['email']))
                    <p class="text-xs text-slate-500 mt-0.5">{{ $invoice->workspace_snapshot['email'] }}</p>
                @endif
            </div>

            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Billed To</p>
                <h3 class="font-bold text-slate-900 text-base">{{ $invoice->client_snapshot['company_name'] ?? $invoice->client_snapshot['name'] }}</h3>
                @if(!empty($invoice->client_snapshot['company_name']))
                    <p class="text-xs text-slate-600 font-medium">{{ $invoice->client_snapshot['name'] }}</p>
                @endif
                <p class="text-xs text-slate-500 whitespace-pre-line mt-1">{{ $invoice->client_snapshot['address'] ?? 'Address on file' }}</p>
                @if(!empty($invoice->client_snapshot['tax_id']))
                    <p class="text-xs text-slate-500 mt-1">VAT/Tax ID: <span class="font-mono">{{ $invoice->client_snapshot['tax_id'] }}</span></p>
                @endif
                <p class="text-xs text-slate-500 mt-0.5">{{ $invoice->client_snapshot['email'] ?? '' }}</p>
            </div>
        </div>

        <!-- Line Items Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 text-slate-400 text-xs uppercase font-bold">
                    <tr>
                        <th class="py-3">Description</th>
                        <th class="py-3 text-center">Qty</th>
                        <th class="py-3 text-right">Unit Rate</th>
                        <th class="py-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($invoice->items as $item)
                        <tr>
                            <td class="py-4">
                                <div class="font-bold text-slate-800">{{ $item->title }}</div>
                                @if($item->description)
                                    <div class="text-xs text-slate-500 mt-0.5">{{ $item->description }}</div>
                                @endif
                            </td>
                            <td class="py-4 text-center font-medium text-slate-600">
                                {{ (float)$item->quantity == (int)$item->quantity ? (int)$item->quantity : $item->quantity }} {{ $item->unit }}
                            </td>
                            <td class="py-4 text-right font-medium text-slate-600">
                                ${{ number_format($item->rate_cents / 100, 2) }}
                            </td>
                            <td class="py-4 text-right font-bold text-slate-900">
                                ${{ number_format($item->amount_cents / 100, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Financial Totals Summary -->
        <div class="flex justify-end pt-4 border-t border-slate-100">
            <div class="w-72 space-y-2 text-sm">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal:</span>
                    <span class="font-semibold text-slate-900">${{ number_format($invoice->subtotal_cents / 100, 2) }}</span>
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
                    <span class="text-red-600">{{ $invoice->formatted_total }}</span>
                </div>
                <div class="flex justify-between text-xs font-bold text-slate-500 pt-1">
                    <span>Amount Paid:</span>
                    <span>${{ number_format($invoice->paid_cents / 100, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm font-black text-rose-600">
                    <span>Balance Due:</span>
                    <span>{{ $invoice->formatted_due }}</span>
                </div>
                @if($invoice->getLateSurchargeCents() > 0)
                    <div class="flex justify-between text-xs font-bold text-rose-700 bg-rose-50 px-2 py-1 rounded">
                        <span>Late Surcharge (Overdue):</span>
                        <span>+{{ $invoice->formatted_late_surcharge }}</span>
                    </div>
                    <div class="flex justify-between border-t border-rose-200 pt-1 text-sm font-black text-rose-700">
                        <span>Adjusted Total Due:</span>
                        <span>{{ $invoice->formatted_total_with_surcharge }}</span>
                    </div>
                @endif
            </div>
        </div>

        @if($invoice->notes)
            <div class="pt-4 border-t border-slate-100 text-xs text-slate-600">
                <p class="font-bold text-slate-700 uppercase tracking-wider mb-1">Notes</p>
                <p class="whitespace-pre-line">{{ $invoice->notes }}</p>
            </div>
        @endif
    </div>

    <!-- Credit Notes & Ledger Adjustments Card -->
    @if($invoice->creditNotes->isNotEmpty())
        <div class="bg-rose-50/50 rounded-2xl border border-rose-200 p-6 shadow-sm space-y-3">
            <div class="flex items-center gap-2 text-rose-900 font-bold text-sm">
                <i class="ph ph-arrow-u-up-left text-lg text-rose-600"></i>
                Issued Credit Notes & Reversals
            </div>
            <div class="divide-y divide-rose-200/60 text-xs">
                @foreach($invoice->creditNotes as $cn)
                    <div class="py-2.5 flex items-center justify-between">
                        <div>
                            <span class="font-mono font-bold text-rose-700">{{ $cn->credit_note_number }}</span>
                            <span class="text-slate-500 ml-2">&bull; {{ $cn->reason }}</span>
                            <span class="text-slate-400 ml-2">({{ $cn->created_at->format('M d, Y') }})</span>
                        </div>
                        <div class="font-mono font-bold text-rose-700">
                            -{{ $cn->formatted_amount }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Issue Credit Note Modal -->
    <div x-show="showCreditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Issue Credit Note</h3>
                    <p class="text-xs text-slate-500">For invoice {{ $invoice->invoice_number }}</p>
                </div>
                <button @click="showCreditModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="ph ph-x text-xl"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('credit-notes.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Credited Amount ({{ $invoice->currency }}) *</label>
                    <input type="number" step="0.01" max="{{ $invoice->total_cents / 100 }}" name="amount"
                           value="{{ number_format($invoice->due_cents > 0 ? $invoice->due_cents / 100 : $invoice->total_cents / 100, 2, '.', '') }}" required
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500">
                    <p class="text-[10px] text-slate-400 mt-1">Maximum creditable: {{ $invoice->formatted_total }}</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Reason for Credit Note *</label>
                    <select name="reason" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 bg-white">
                        <option value="Billing error or incorrect amount">Billing error or incorrect amount</option>
                        <option value="Customer refund requested">Customer refund requested</option>
                        <option value="Project cancelled / unfulfilled service">Project cancelled / unfulfilled service</option>
                        <option value="Dispute resolution">Dispute resolution</option>
                        <option value="Discount applied after issuance">Discount applied after issuance</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Audit Notes / Comments</label>
                    <textarea name="notes" rows="2" placeholder="Explanation for accounting audit log..."
                              class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showCreditModal = false"
                            class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2 text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white rounded-lg shadow-sm">
                        Confirm & Issue Credit Note
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
