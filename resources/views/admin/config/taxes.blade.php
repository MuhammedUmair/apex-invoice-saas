@extends('layouts.admin', ['title' => 'Currency & Taxes', 'header' => 'System Configuration'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white">Currency & Tax Configurations</h1>
            <p class="text-xs text-slate-400 mt-0.5">Platform-level VAT rates, tax ID validation rules, and base accounting currency.</p>
        </div>
    </div>

    <div class="flex items-center gap-2 border-b border-slate-800 pb-2 text-xs overflow-x-auto">
        <a href="{{ route('admin.config.general') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">General</a>
        <a href="{{ route('admin.config.tour') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 flex items-center gap-1.5 font-bold">
            <i class="ph ph-compass"></i> Welcome Tour
        </a>
        <a href="{{ route('admin.config.gateways') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">Gateways</a>
        <a href="{{ route('admin.config.smtp') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">SMTP Server</a>
        <a href="{{ route('admin.config.storage') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">Storage</a>
        <a href="{{ route('admin.config.taxes') }}" class="px-3.5 py-1.5 rounded-xl font-bold bg-amber-500 text-slate-950">Currency & Taxes</a>
        <a href="{{ route('admin.config.branding') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">Platform Branding</a>
    </div>

    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 max-w-2xl space-y-4 shadow-sm text-xs text-slate-300">
        <div class="space-y-2">
            <div class="flex justify-between border-b border-slate-800/80 pb-2">
                <span class="text-slate-400">Base Currency:</span>
                <strong class="text-white font-mono">USD ($)</strong>
            </div>
            <div class="flex justify-between border-b border-slate-800/80 pb-2">
                <span class="text-slate-400">Standard VAT Rate:</span>
                <strong class="text-white font-mono">5.0% (UAE Standard)</strong>
            </div>
            <div class="flex justify-between pb-1">
                <span class="text-slate-400">Calculation Engine:</span>
                <strong class="text-emerald-400">Integer Cents Precision</strong>
            </div>
        </div>
    </div>
</div>
@endsection
