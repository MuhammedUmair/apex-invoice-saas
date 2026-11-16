@extends('layouts.admin', ['title' => 'Tax & Revenue Reports', 'header' => 'Tax & Revenue Reporting'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white">Platform Tax & Revenue Summary</h1>
            <p class="text-xs text-slate-400 mt-0.5">Estimated gross tax liabilities, collected transaction volume, and monthly tax allocations.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
            <div class="text-xs font-bold text-slate-400 uppercase">Gross Revenue Collected</div>
            <div class="text-3xl font-black text-white mt-1">${{ number_format($totalCollectedCents / 100, 2) }}</div>
            <div class="text-xs text-slate-500 mt-1">Platform gross transaction receipts</div>
        </div>

        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
            <div class="text-xs font-bold text-amber-400 uppercase">Estimated Standard Tax (5% VAT)</div>
            <div class="text-3xl font-black text-amber-400 mt-1">${{ number_format($estimatedTaxCents / 100, 2) }}</div>
            <div class="text-xs text-slate-500 mt-1">Estimated platform indirect tax provision</div>
        </div>
    </div>

    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-800 font-bold text-white text-sm">Monthly Taxable Receipts Breakdown</div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-[10px] uppercase font-bold tracking-wider bg-slate-950/40">
                        <th class="py-3 px-4">Month</th>
                        <th class="py-3 px-4">Transaction Count</th>
                        <th class="py-3 px-4">Gross Collected</th>
                        <th class="py-3 px-4">Estimated Tax (5%)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($monthlyBreakdown as $row)
                        <tr>
                            <td class="py-3 px-4 font-bold text-white">{{ $row->month }}</td>
                            <td class="py-3 px-4">{{ $row->total_count }} payments</td>
                            <td class="py-3 px-4 font-mono font-bold text-emerald-400">${{ number_format($row->total_cents / 100, 2) }}</td>
                            <td class="py-3 px-4 font-mono text-amber-400">${{ number_format(($row->total_cents * 0.05) / 100, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-xs text-slate-500">No payment records available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
