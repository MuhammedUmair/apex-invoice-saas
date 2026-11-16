@extends('layouts.admin', ['title' => 'Usage & Limits', 'header' => 'Platform Consumption & Usage Limits'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white">Platform Usage & Consumption</h1>
            <p class="text-xs text-slate-400 mt-0.5">Global aggregate counts across all tenant workspaces.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
            <div class="text-xs font-bold text-slate-400 uppercase">Total Workspaces</div>
            <div class="text-3xl font-black text-white mt-1">{{ number_format($totalWorkspaces) }}</div>
            <div class="text-xs text-slate-500 mt-1">Tenant agencies</div>
        </div>

        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
            <div class="text-xs font-bold text-cyan-400 uppercase">Total Invoices</div>
            <div class="text-3xl font-black text-white mt-1">{{ number_format($totalInvoices) }}</div>
            <div class="text-xs text-slate-500 mt-1">Generated documents</div>
        </div>

        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
            <div class="text-xs font-bold text-indigo-400 uppercase">Registered Users</div>
            <div class="text-3xl font-black text-white mt-1">{{ number_format($totalUsers) }}</div>
            <div class="text-xs text-slate-500 mt-1">Platform user accounts</div>
        </div>

        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
            <div class="text-xs font-bold text-emerald-400 uppercase">Recorded Payments</div>
            <div class="text-3xl font-black text-white mt-1">{{ number_format($totalPayments) }}</div>
            <div class="text-xs text-slate-500 mt-1">Ledger transactions</div>
        </div>
    </div>
</div>
@endsection
