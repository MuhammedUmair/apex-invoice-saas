@extends('layouts.admin', ['title' => 'Analytics & Platform Reports', 'header' => 'Revenue Analytics & Reporting Hub'])

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white tracking-tight flex items-center gap-2">
                <span>Platform Financial & Workspace Analytics</span>
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Recurring revenue metrics, churn analysis, plan adoption, and growth trajectory.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.export', ['type' => 'transactions']) }}" class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <i class="ph ph-file-csv font-bold"></i> Export Financial Ledger (CSV)
            </a>
        </div>
    </div>

    <!-- Core Revenue Metrics Strip -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
            <div class="text-[11px] font-bold uppercase tracking-wider text-amber-400 mb-1">Monthly Recurring (MRR)</div>
            <div class="text-3xl font-black text-white">${{ number_format($kpis['mrr_dollars'], 2) }}</div>
            <div class="text-xs text-slate-400 mt-1">Normalized active recurring subscription base</div>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
            <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-400 mb-1">Annual Recurring (ARR)</div>
            <div class="text-3xl font-black text-white">${{ number_format($kpis['arr_dollars'], 2) }}</div>
            <div class="text-xs text-slate-400 mt-1">Annualized recurring run-rate projection</div>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
            <div class="text-[11px] font-bold uppercase tracking-wider text-cyan-400 mb-1">Cash Collected</div>
            <div class="text-3xl font-black text-white">${{ number_format($kpis['successful_payments_amount_dollars'], 2) }}</div>
            <div class="text-xs text-slate-400 mt-1">{{ number_format($kpis['successful_payments_count']) }} completed transactions</div>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
            <div class="text-[11px] font-bold uppercase tracking-wider text-purple-400 mb-1">Platform Churn Rate</div>
            <div class="text-3xl font-black text-white">{{ $kpis['churn_rate'] }}%</div>
            <div class="text-xs text-slate-400 mt-1">Cancellations over total subscriptions</div>
        </div>
    </div>

    <!-- Monthly Trend Breakdown Table -->
    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm">
        <h3 class="font-bold text-white text-sm mb-4">Historical Revenue & Workspace Growth Breakdown</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-[10px] uppercase font-bold tracking-wider">
                        <th class="py-3 px-4">Billing Month</th>
                        <th class="py-3 px-4">New Registrations</th>
                        <th class="py-3 px-4">Subscription Revenue</th>
                        <th class="py-3 px-4">Growth Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @foreach($analytics['monthly_trend'] as $item)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3 px-4 font-bold text-white">{{ $item['month'] }}</td>
                            <td class="py-3 px-4">{{ number_format($item['registrations']) }} workspaces</td>
                            <td class="py-3 px-4 font-mono font-bold text-emerald-400">${{ number_format($item['revenue_dollars'], 2) }}</td>
                            <td class="py-3 px-4">
                                @if($item['revenue_dollars'] > 0)
                                    <span class="inline-flex items-center gap-1 text-[11px] text-emerald-400 font-semibold">
                                        <i class="ph ph-arrow-up-right"></i> Positive
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] text-slate-500 font-semibold">
                                        &bull; Baseline
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Plan Adoption Table -->
    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm">
        <h3 class="font-bold text-white text-sm mb-4">Subscription Plan Distribution & Tier Performance</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-[10px] uppercase font-bold tracking-wider">
                        <th class="py-3 px-4">Plan Name</th>
                        <th class="py-3 px-4">Billing Interval</th>
                        <th class="py-3 px-4">Price</th>
                        <th class="py-3 px-4">Active Workspaces</th>
                        <th class="py-3 px-4">Monthly Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @foreach($plans as $p)
                        @php
                            $monthlyPrice = $p->billing_interval === 'year' ? round($p->price_cents / 12) : $p->price_cents;
                            $totalMonthlyCents = $monthlyPrice * $p->subscriptions_count;
                        @endphp
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3 px-4 font-bold text-white">{{ $p->name }}</td>
                            <td class="py-3 px-4 uppercase text-[10px] font-bold text-slate-400">{{ $p->billing_interval }}</td>
                            <td class="py-3 px-4 font-mono">{{ $p->formatted_price }}</td>
                            <td class="py-3 px-4 font-bold text-amber-400">{{ $p->subscriptions_count }}</td>
                            <td class="py-3 px-4 font-mono font-bold text-emerald-400">${{ number_format($totalMonthlyCents / 100, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
