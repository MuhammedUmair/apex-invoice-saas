@extends('layouts.admin', ['title' => 'Integrations Status', 'header' => 'Connected Platform Integrations'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white">Platform External Integrations</h1>
            <p class="text-xs text-slate-400 mt-0.5">Operating status of third-party cloud infrastructure and payment processing networks.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Stripe -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="ph ph-stripe-logo text-2xl text-indigo-400"></i>
                    <h3 class="font-bold text-white text-sm">Stripe Payments Network</h3>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Operational</span>
            </div>
            <p class="text-xs text-slate-400">Webhooks endpoint active for payment intent confirmation and subscription lifecycle updates.</p>
        </div>

        <!-- PayPal -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="ph ph-paypal-logo text-2xl text-sky-400"></i>
                    <h3 class="font-bold text-white text-sm">PayPal Checkout API</h3>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Operational</span>
            </div>
            <p class="text-xs text-slate-400">REST v2 payment order validation and webhook dispatch active.</p>
        </div>

        <!-- Hostinger Mail -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="ph ph-envelope text-2xl text-rose-400"></i>
                    <h3 class="font-bold text-white text-sm">Hostinger SMTP Server</h3>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Active</span>
            </div>
            <p class="text-xs text-slate-400">Primary outbound transactional relay for OTPs, invoices, and password resets.</p>
        </div>

        <!-- Cloudflare -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="ph ph-cloud text-2xl text-amber-400"></i>
                    <h3 class="font-bold text-white text-sm">Cloudflare Edge & SSL</h3>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Active</span>
            </div>
            <p class="text-xs text-slate-400">SSL termination, DDoS mitigation, and edge asset caching enabled.</p>
        </div>
    </div>
</div>
@endsection
