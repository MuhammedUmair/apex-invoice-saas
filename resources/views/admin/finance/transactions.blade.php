@extends('layouts.admin', ['title' => 'Payment Transactions', 'header' => 'Platform Financial Transactions'])

@section('content')
<div class="space-y-6" x-data="{ 
    refundModal: false, 
    selectedPayment: null,
    openRefund(id, num, amount) {
        this.selectedPayment = { id: id, number: num, amount: amount };
        this.refundModal = true;
    }
}">
    <!-- Top Filter Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white">Payment Transactions Ledger</h1>
            <p class="text-xs text-slate-400 mt-0.5">Idempotent platform-wide payment records across Stripe, PayPal, and manual bank wires.</p>
        </div>

        <form method="GET" action="{{ route('admin.finance.transactions') }}" class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by payment #, idempotency key..."
                   class="bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl px-3.5 py-2 w-64 focus:ring-2 focus:ring-amber-500 outline-none">
            
            <select name="status" onchange="this.form.submit()" 
                    class="bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
                <option value="">All Statuses</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
            </select>

            <select name="gateway" onchange="this.form.submit()" 
                    class="bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
                <option value="">All Gateways</option>
                <option value="stripe" {{ request('gateway') === 'stripe' ? 'selected' : '' }}>Stripe</option>
                <option value="paypal" {{ request('gateway') === 'paypal' ? 'selected' : '' }}>PayPal</option>
                <option value="wire_transfer" {{ request('gateway') === 'wire_transfer' ? 'selected' : '' }}>Wire Transfer</option>
            </select>

            <button type="submit" class="p-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl transition">
                <i class="ph ph-magnifying-glass"></i>
            </button>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-[10px] uppercase font-bold tracking-wider bg-slate-950/40">
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Payment Reference</th>
                        <th class="py-3 px-4">Gateway</th>
                        <th class="py-3 px-4">Amount</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Idempotency Key</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($payments as $p)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-400 whitespace-nowrap">
                                {{ $p->payment_date ? $p->payment_date->format('M d, Y H:i') : 'N/A' }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-white">{{ $p->payment_number }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">{{ $p->gateway_transaction_id ?? 'N/A' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase bg-slate-800 text-slate-300">
                                    {{ $p->gateway }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-emerald-400">
                                ${{ number_format($p->amount_cents / 100, 2) }} {{ $p->currency }}
                            </td>
                            <td class="py-3 px-4">
                                @if($p->status === 'completed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        Completed
                                    </span>
                                @elseif($p->status === 'refunded')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        Refunded
                                    </span>
                                @elseif($p->status === 'failed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        Failed
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300">
                                        {{ $p->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-mono text-[10px] text-slate-500">
                                {{ substr($p->idempotency_key ?? 'N/A', 0, 16) }}...
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if($p->status === 'completed' && auth()->user()->hasAdminPermission('refunds'))
                                    <button @click="openRefund('{{ $p->id }}', '{{ $p->payment_number }}', '{{ number_format($p->amount_cents / 100, 2) }}')"
                                            class="px-2.5 py-1 rounded-lg bg-amber-500/10 text-amber-400 hover:bg-amber-500 hover:text-slate-950 text-[11px] font-bold transition">
                                        Refund
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-xs text-slate-500">No payment transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

    <!-- Refund Modal (Section 8: requires confirmation, reason, and role authorization) -->
    <div x-show="refundModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 z-50" x-cloak>
        <div class="w-full max-w-md rounded-2xl bg-slate-900 border border-slate-800 p-6 space-y-4 shadow-2xl" @click.away="refundModal = false">
            <div class="flex items-center gap-3 text-amber-400">
                <i class="ph ph-arrow-counter-clockwise text-2xl font-bold"></i>
                <h3 class="font-bold text-white text-base">Confirm Payment Refund</h3>
            </div>
            <p class="text-xs text-slate-300">
                You are about to process a refund for payment <strong class="text-white" x-text="selectedPayment?.number"></strong> ($<span x-text="selectedPayment?.amount"></span>).
            </p>
            <form :action="'/admin/finance/refunds/' + selectedPayment?.id" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">
                        Refund Justification (Mandatory for Audit) <span class="text-amber-400">*</span>
                    </label>
                    <textarea name="reason" required rows="3" placeholder="e.g. Customer billing dispute resolved, duplicate payment..."
                              class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-200 focus:ring-2 focus:ring-amber-500 outline-none"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="refundModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-xs font-bold text-slate-950">Execute Refund</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
