@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <!-- Top Subscription Banner (if expired or trialing) -->
    @if($subscription && $subscription->isExpired())
        <div class="bg-gradient-to-r from-rose-900 to-red-900 rounded-2xl p-6 text-white shadow-lg flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-rose-500/20 border border-rose-400/30 flex items-center justify-center text-rose-300 text-2xl shrink-0">
                    <i class="ph ph-warning-octagon"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg">Subscription / Free Trial Expired</h3>
                    <p class="text-sm text-rose-200">
                        Your trial period has concluded. Upgrade to Starter Agency, Pro Business, or Enterprise Scale to continue invoicing clients without interruption.
                    </p>
                </div>
            </div>
            <a href="{{ route('membership.index') }}" class="px-5 py-2.5 bg-white hover:bg-slate-100 text-rose-900 font-bold text-sm rounded-xl shadow-md transition whitespace-nowrap">
                Choose a Plan & Upgrade &rarr;
            </a>
        </div>
    @elseif($subscription && $subscription->isTrialing())
        <div class="bg-gradient-to-r from-blue-900 to-indigo-900 rounded-2xl p-6 text-white shadow-lg flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-500/20 border border-blue-400/30 flex items-center justify-center text-amber-400 text-2xl shrink-0">
                    <i class="ph ph-sparkle"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg">{{ $subscription->plan->name ?? 'Free Trial' }} Active</h3>
                    <p class="text-sm text-blue-200">
                        Your free trial ends {{ $subscription->trial_ends_at ? $subscription->trial_ends_at->diffForHumans() : 'soon' }} ({{ $subscription->trialDaysRemaining() }} days left). Upgrade now to keep unlimited features & custom branding.
                    </p>
                </div>
            </div>
            <a href="{{ route('membership.index') }}" class="px-5 py-2.5 bg-amber-400 hover:bg-amber-300 text-slate-900 font-bold text-sm rounded-xl shadow-md transition whitespace-nowrap">
                Upgrade Membership
            </a>
        </div>
    @endif

    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Collected Revenue -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold">
                <i class="ph ph-money"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Collected Revenue</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">${{ number_format($totalRevenueCents / 100, 2) }}</p>
            </div>
        </div>

        <!-- Outstanding Due -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl font-bold">
                <i class="ph ph-hourglass-high"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Outstanding Due</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">${{ number_format($outstandingDueCents / 100, 2) }}</p>
            </div>
        </div>

        <!-- Invoices Issued -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl font-bold">
                <i class="ph ph-receipt"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Invoices</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $invoicesCount }}</p>
            </div>
        </div>

        <!-- Active Clients -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl font-bold">
                <i class="ph ph-users-three"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Saved Clients</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $clientsCount }}</p>
            </div>
        </div>
    </div>

    <!-- Tables Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Invoices -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-sm">Recent Invoices</h3>
                <a href="{{ route('invoices.index') }}" class="text-xs text-blue-600 font-semibold hover:underline">View All</a>
            </div>

            @if($recentInvoices->isEmpty())
                <div class="p-8 text-center text-slate-400 text-sm">
                    <i class="ph ph-receipt text-3xl mb-2 inline-block"></i>
                    <p>No invoices created yet.</p>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($recentInvoices as $inv)
                        <div class="px-6 py-3.5 flex items-center justify-between hover:bg-slate-50 transition">
                            <div>
                                <a href="{{ route('invoices.show', $inv->id) }}" class="font-semibold text-sm text-slate-900 hover:text-blue-600">
                                    {{ $inv->invoice_number }}
                                </a>
                                <p class="text-xs text-slate-500">{{ $inv->client->company_name ?? $inv->client->name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-slate-900">{{ $inv->formatted_total }}</p>
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold uppercase
                                    @if($inv->status === 'paid') bg-emerald-100 text-emerald-800
                                    @elseif($inv->status === 'partially_paid') bg-amber-100 text-amber-800
                                    @elseif($inv->status === 'issued') bg-blue-100 text-blue-800
                                    @else bg-slate-100 text-slate-800 @endif">
                                    {{ $inv->status }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Recent Payments -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-sm">Recent Payments Received</h3>
                <span class="text-xs text-slate-400">Live Gateway Feeds</span>
            </div>

            @if($recentPayments->isEmpty())
                <div class="p-8 text-center text-slate-400 text-sm">
                    <i class="ph ph-credit-card text-3xl mb-2 inline-block"></i>
                    <p>No customer payments recorded yet.</p>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($recentPayments as $payment)
                        <div class="px-6 py-3.5 flex items-center justify-between hover:bg-slate-50 transition">
                            <div>
                                <p class="font-semibold text-sm text-slate-900">{{ $payment->payment_number }}</p>
                                <p class="text-xs text-slate-500">
                                    Via <span class="capitalize font-medium">{{ $payment->gateway }}</span> &bull; {{ $payment->payment_date->diffForHumans() }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-emerald-600">+{{ $payment->formatted_amount }}</p>
                                <span class="text-[10px] text-slate-400">{{ $payment->invoice->invoice_number ?? 'Invoice' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
