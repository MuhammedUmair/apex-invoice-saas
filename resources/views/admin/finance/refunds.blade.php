@extends('layouts.admin', ['title' => 'Refunds Ledger', 'header' => 'Payment Refunds & Reversals'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white flex items-center gap-2">
                <i class="ph ph-arrow-counter-clockwise text-amber-400"></i> Payment Refunds
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Audited log of refunded payments and disputed transaction reversals.</p>
        </div>
        <a href="{{ route('admin.finance.transactions') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
            <i class="ph ph-arrow-left"></i> All Transactions
        </a>
    </div>

    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-[10px] uppercase font-bold tracking-wider bg-slate-950/40">
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Payment #</th>
                        <th class="py-3 px-4">Gateway</th>
                        <th class="py-3 px-4">Refunded Amount</th>
                        <th class="py-3 px-4">Refund Notes / Justification</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($refunds as $p)
                        <tr>
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-400">{{ $p->payment_date ? $p->payment_date->format('M d, Y H:i') : 'N/A' }}</td>
                            <td class="py-3 px-4 font-bold text-white">{{ $p->payment_number }}</td>
                            <td class="py-3 px-4 uppercase text-[10px] font-mono text-slate-400">{{ $p->gateway }}</td>
                            <td class="py-3 px-4 font-mono font-bold text-amber-400">${{ number_format($p->amount_cents / 100, 2) }} {{ $p->currency }}</td>
                            <td class="py-3 px-4 text-slate-300 text-[11px] max-w-md truncate">{{ $p->notes ?? 'Administrative refund processed' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-xs text-slate-500">No refunded payments recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
