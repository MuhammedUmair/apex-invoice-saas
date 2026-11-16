@extends('layouts.admin', ['title' => 'Webhook Logs', 'header' => 'API & Webhook Delivery Logs'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white">API & Webhook Delivery Logs</h1>
            <p class="text-xs text-slate-400 mt-0.5">Inbound payment webhook dispatches from Stripe and PayPal with secrets redacted.</p>
        </div>
    </div>

    <div class="rounded-2xl bg-slate-900 border border-slate-800 p-8 text-center text-xs text-slate-400 space-y-2">
        <i class="ph ph-webhooks-logo text-4xl text-amber-400"></i>
        <div class="font-bold text-white text-sm">Live Webhook Relay Active</div>
        <p class="max-w-md mx-auto text-slate-500">
            Payment webhooks for Stripe and PayPal endpoints are actively receiving callbacks at <code>/webhooks/stripe</code> and <code>/webhooks/paypal</code>.
        </p>
    </div>
</div>
@endsection
