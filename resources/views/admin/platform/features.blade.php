@extends('layouts.admin', ['title' => 'Feature Flags', 'header' => 'Platform Feature Flags & Capabilities'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white">Platform Feature Entitlements</h1>
            <p class="text-xs text-slate-400 mt-0.5">Control global platform features, maintenance mode, and gateway visibility.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($features as $key => $enabled)
            <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-white text-sm capitalize">{{ str_replace('_', ' ', $key) }}</h3>
                    <p class="text-xs text-slate-400 mt-1">
                        @if($key === 'enable_signups')
                            Allow new customer agencies to register accounts on Invoicy.
                        @elseif($key === 'enable_paypal')
                            Enable PayPal payment gateway for agency subscriptions and client payments.
                        @elseif($key === 'enable_stripe')
                            Enable Stripe card processing across the platform.
                        @elseif($key === 'enable_recurring')
                            Enable monthly automated retainer and recurring invoice scheduler.
                        @elseif($key === 'enable_whitelabel')
                            Allow agencies on Enterprise tiers to remove Invoicy branding.
                        @else
                            Put the platform into maintenance mode for system updates.
                        @endif
                    </p>
                </div>
                <form action="{{ route('admin.platform.features.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="key" value="{{ $key }}">
                    <input type="hidden" name="value" value="{{ $enabled ? '0' : '1' }}">
                    <button type="submit" 
                            class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $enabled ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-slate-800 hover:bg-slate-700 text-slate-400' }}">
                        {{ $enabled ? 'Active / ON' : 'Disabled / OFF' }}
                    </button>
                </form>
            </div>
        @endforeach
    </div>
</div>
@endsection
