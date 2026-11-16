@extends('layouts.admin', ['title' => 'Gateway Settings', 'header' => 'System Configuration'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white">Platform Payment Gateways</h1>
            <p class="text-xs text-slate-400 mt-0.5">Master Stripe and PayPal merchant credentials for SaaS subscription billing.</p>
        </div>
    </div>

    <div class="flex items-center gap-2 border-b border-slate-800 pb-2 text-xs">
        <a href="{{ route('admin.config.general') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">General</a>
        <a href="{{ route('admin.config.gateways') }}" class="px-3.5 py-1.5 rounded-xl font-bold bg-amber-500 text-slate-950">Gateways</a>
        <a href="{{ route('admin.config.smtp') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">SMTP Server</a>
        <a href="{{ route('admin.config.storage') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">Storage</a>
        <a href="{{ route('admin.config.taxes') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">Currency & Taxes</a>
        <a href="{{ route('admin.config.branding') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">Platform Branding</a>
    </div>

    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 max-w-2xl space-y-4 shadow-sm text-xs text-slate-300">
        <div class="flex items-center gap-2 text-emerald-400 font-bold">
            <i class="ph ph-shield-check text-lg"></i>
            <span>Environment-Managed Credentials</span>
        </div>
        <p class="text-slate-400 leading-relaxed">
            Platform-level Stripe and PayPal merchant secrets are secured in production environment variables (<code>.env</code>) and managed via Hostinger environment isolation. Per Section 12, secrets are never exposed in plaintext.
        </p>
    </div>
</div>
@endsection
