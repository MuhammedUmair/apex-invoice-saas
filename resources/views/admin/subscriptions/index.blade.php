@extends('layouts.app', ['title' => 'Subscriptions Manager - Super Admin', 'header' => 'SaaS Subscriptions & Tenant Management'])

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-200 gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-amber-50 text-amber-600 border border-amber-200">
                    <i class="ph ph-crown text-xl"></i>
                </span>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Super Admin Control Panel</h1>
            </div>
            <p class="text-sm text-slate-500 mt-1">
                Manage all agency workspaces, active subscriptions, renewals, plan upgrades, and pricing tiers.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-slate-900 text-white flex items-center gap-1.5 shadow-sm">
                <i class="ph ph-shield-check text-emerald-400 text-base"></i> Platform Owner Authority
            </span>
        </div>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
            <i class="ph ph-check-circle text-base text-emerald-600"></i>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center gap-2">
            <i class="ph ph-warning-circle text-base text-rose-600"></i>
            {{ session('error') }}
        </div>
    @endif

    <!-- Tenant Workspaces Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-base">Tenant Workspaces & Active Subscriptions</h3>
            <span class="text-xs bg-slate-100 text-slate-700 font-bold px-3 py-1 rounded-full">
                {{ $workspaces->total() }} Total Agencies
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Agency / Workspace</th>
                        <th class="px-6 py-3">Owner Contact</th>
                        <th class="px-6 py-3">Current Plan</th>
                        <th class="px-6 py-3">Subscription Status</th>
                        <th class="px-6 py-3">Valid Until</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($workspaces as $ws)
                        @php
                            $sub = $ws->subscriptions->first();
                            $plan = $sub->plan ?? null;
                            $owner = $ws->users->first();
                        @endphp
                        <tr class="hover:bg-slate-50/50" x-data="{ openRenew: false }">
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-900">{{ $ws->name }}</span>
                                <span class="block text-xs font-mono text-slate-400">{{ $ws->slug }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-medium text-slate-800">{{ $owner->name ?? 'N/A' }}</span>
                                <span class="block text-xs text-slate-400">{{ $owner->email ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($plan)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        <i class="ph ph-crown mr-1"></i> {{ $plan->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">No Plan</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @php $status = $sub->status ?? 'none'; @endphp
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider
                                    @if($status === 'active') bg-emerald-100 text-emerald-800
                                    @elseif($status === 'trialing') bg-blue-100 text-blue-800
                                    @elseif($status === 'suspended') bg-rose-100 text-rose-800
                                    @else bg-slate-100 text-slate-700 @endif">
                                    {{ $status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600">
                                @if($sub && $sub->current_period_ends_at)
                                    {{ $sub->current_period_ends_at->format('M d, Y') }}
                                @elseif($sub && $sub->trial_ends_at)
                                    Trial: {{ $sub->trial_ends_at->format('M d, Y') }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button @click="openRenew = true" class="px-2.5 py-1.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-bold transition">
                                    <i class="ph ph-arrow-clockwise"></i> Renew / Change Plan
                                </button>
                                <form action="{{ route('admin.workspaces.toggle', $ws->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition {{ ($sub && $sub->status === 'active') ? 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' }}">
                                        {{ ($sub && $sub->status === 'active') ? 'Suspend' : 'Activate' }}
                                    </button>
                                </form>

                                <!-- Renew Modal -->
                                <div x-show="openRenew" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 text-left">
                                    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
                                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                                            <h3 class="font-bold text-slate-900 text-base">Renew / Upgrade {{ $ws->name }}</h3>
                                            <button @click="openRenew = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                                        </div>

                                        <form method="POST" action="{{ route('admin.workspaces.renew', $ws->id) }}" class="space-y-4">
                                            @csrf
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Select Subscription Plan</label>
                                                <select name="plan_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                                                    @foreach($plans as $p)
                                                        <option value="{{ $p->id }}" {{ ($plan && $plan->id === $p->id) ? 'selected' : '' }}>
                                                            {{ $p->name }} (${{ number_format($p->price_monthly_cents / 100, 2) }}/mo)
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Renewal Extension (Months)</label>
                                                <select name="duration_months" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                                                    <option value="1">1 Month Extension</option>
                                                    <option value="3">3 Months Extension</option>
                                                    <option value="6">6 Months Extension</option>
                                                    <option value="12" selected>1 Year Extension</option>
                                                </select>
                                            </div>

                                            <div class="flex justify-end gap-2 pt-3 border-t">
                                                <button type="button" @click="openRenew = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                                                <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg shadow">Confirm Renewal</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $workspaces->links() }}
        </div>
    </div>

    <!-- Section 2: SaaS Plans & Pricing Configuration -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
        <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                <i class="ph ph-sliders"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-900 text-base">SaaS Plans & Pricing Tiers</h3>
                <p class="text-xs text-slate-500">Configure monthly/yearly pricing, invoice limits, and active status for each package</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($plans as $p)
                <div class="p-6 rounded-2xl border border-slate-200 bg-slate-50/50 flex flex-col justify-between space-y-4">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 font-mono">{{ $p->code }}</span>
                        <h4 class="text-lg font-black text-slate-900">{{ $p->name }}</h4>
                    </div>

                    <form method="POST" action="{{ route('admin.plans.update', $p->id) }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="name" value="{{ $p->name }}">

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Monthly Price ($)</label>
                            <input type="number" step="0.01" name="price_monthly" value="{{ number_format($p->price_monthly_cents / 100, 2, '.', '') }}"
                                   onchange="this.form.price_monthly_cents.value = Math.round(this.value * 100)"
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white">
                            <input type="hidden" name="price_monthly_cents" value="{{ $p->price_monthly_cents }}">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Yearly Price ($)</label>
                            <input type="number" step="0.01" name="price_yearly" value="{{ number_format($p->price_yearly_cents / 100, 2, '.', '') }}"
                                   onchange="this.form.price_yearly_cents.value = Math.round(this.value * 100)"
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white">
                            <input type="hidden" name="price_yearly_cents" value="{{ $p->price_yearly_cents }}">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Invoice Limit / Month</label>
                            <input type="number" name="invoice_limit_monthly" value="{{ $p->invoice_limit_monthly }}" placeholder="Null = Unlimited"
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white">
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow transition">
                                Update Plan Settings
                            </button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
