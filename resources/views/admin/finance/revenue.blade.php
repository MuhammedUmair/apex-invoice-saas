@extends('layouts.admin', ['title' => 'SaaS Revenue', 'header' => 'Platform Financial Ledger & Recurring Revenue'])

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white">SaaS Revenue Run-Rate</h1>
            <p class="text-xs text-slate-400 mt-0.5">Integer-cents monetary precision for all recurring memberships and collected cash volume.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.export', ['type' => 'transactions']) }}" class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <i class="ph ph-file-csv font-bold"></i> Export Financial Ledger
            </a>
        </div>
    </div>

    <!-- Revenue Strip -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="p-6 rounded-2xl bg-gradient-to-br from-slate-900 to-amber-950/30 border border-amber-500/30 shadow-sm">
            <div class="text-xs font-bold text-amber-400 uppercase tracking-wider">Normalized MRR</div>
            <div class="text-3xl font-black text-white mt-1">${{ number_format($kpis['mrr_dollars'], 2) }}</div>
            <div class="text-xs text-slate-400 mt-2">Active recurring subscription value (monthly equivalent).</div>
        </div>

        <div class="p-6 rounded-2xl bg-gradient-to-br from-slate-900 to-emerald-950/30 border border-emerald-500/30 shadow-sm">
            <div class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Annualized ARR</div>
            <div class="text-3xl font-black text-white mt-1">${{ number_format($kpis['arr_dollars'], 2) }}</div>
            <div class="text-xs text-slate-400 mt-2">Projected 12-month annual run-rate at current MRR.</div>
        </div>

        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm">
            <div class="text-xs font-bold text-cyan-400 uppercase tracking-wider">Completed Cash Collected</div>
            <div class="text-3xl font-black text-white mt-1">${{ number_format($kpis['successful_payments_amount_dollars'], 2) }}</div>
            <div class="text-xs text-slate-400 mt-2">{{ number_format($kpis['successful_payments_count']) }} successful payment transactions.</div>
        </div>
    </div>

    <!-- Recent Payments Preview -->
    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="font-bold text-white text-sm">Recent Platform Transactions</h3>
            <a href="{{ route('admin.finance.transactions') }}" class="text-xs text-amber-400 hover:text-amber-300 font-semibold">View All Transactions &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-[10px] uppercase font-bold tracking-wider">
                        <th class="py-2.5 px-3">Date</th>
                        <th class="py-2.5 px-3">Payment #</th>
                        <th class="py-2.5 px-3">Gateway</th>
                        <th class="py-2.5 px-3">Amount</th>
                        <th class="py-2.5 px-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($recentTransactions as $p)
                        <tr>
                            <td class="py-2.5 px-3 font-mono text-[11px] text-slate-400">{{ $p->payment_date ? $p->payment_date->format('M d, H:i') : 'N/A' }}</td>
                            <td class="py-2.5 px-3 font-bold text-white">{{ $p->payment_number }}</td>
                            <td class="py-2.5 px-3 uppercase text-[10px] font-mono text-slate-400">{{ $p->gateway }}</td>
                            <td class="py-2.5 px-3 font-mono font-bold text-emerald-400">${{ number_format($p->amount_cents / 100, 2) }} {{ $p->currency }}</td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $p->status === 'completed' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-slate-800 text-slate-400' }}">
                                    {{ $p->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-xs text-slate-500">No payment transactions recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
