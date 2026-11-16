@extends('layouts.admin', ['title' => 'SMTP Configuration', 'header' => 'System Configuration'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white">Platform Outgoing Mail Server (SMTP)</h1>
            <p class="text-xs text-slate-400 mt-0.5">Hostinger live SMTP relay configuration for system-wide notifications.</p>
        </div>
    </div>

    <div class="flex items-center gap-2 border-b border-slate-800 pb-2 text-xs">
        <a href="{{ route('admin.config.general') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">General</a>
        <a href="{{ route('admin.config.gateways') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">Gateways</a>
        <a href="{{ route('admin.config.smtp') }}" class="px-3.5 py-1.5 rounded-xl font-bold bg-amber-500 text-slate-950">SMTP Server</a>
        <a href="{{ route('admin.config.storage') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">Storage</a>
        <a href="{{ route('admin.config.taxes') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">Currency & Taxes</a>
        <a href="{{ route('admin.config.branding') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">Platform Branding</a>
    </div>

    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 max-w-2xl space-y-4 shadow-sm text-xs text-slate-300">
        <div class="space-y-2">
            <div class="flex justify-between border-b border-slate-800/80 pb-2">
                <span class="text-slate-400">SMTP Host:</span>
                <strong class="text-white font-mono">smtp.hostinger.com</strong>
            </div>
            <div class="flex justify-between border-b border-slate-800/80 pb-2">
                <span class="text-slate-400">SMTP Port:</span>
                <strong class="text-white font-mono">465 (SSL / TLS)</strong>
            </div>
            <div class="flex justify-between border-b border-slate-800/80 pb-2">
                <span class="text-slate-400">From Address:</span>
                <strong class="text-white font-mono">billing@redmapleagency.com</strong>
            </div>
            <div class="flex justify-between pb-1">
                <span class="text-slate-400">From Display Name:</span>
                <strong class="text-white">Invoicy Platform</strong>
            </div>
        </div>
    </div>
</div>
@endsection
