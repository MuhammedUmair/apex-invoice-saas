@extends('layouts.app', ['title' => 'Subscriptions & Plan Manager - Super Admin', 'header' => 'SaaS Subscriptions, Plans & Alerts'])

@section('content')
<div class="space-y-8" x-data="{ currentTab: '{{ old('tab', $currentTab ?? 'tenants') }}' }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-200 gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-amber-50 text-amber-600 border border-amber-200">
                    <i class="ph ph-crown text-xl"></i>
                </span>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Subscription & Plan Control Panel</h1>
            </div>
            <p class="text-sm text-slate-500 mt-1">
                Configure SaaS pricing tiers, enforce subscription rules, manage tenants, and monitor live alerts.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-slate-900 text-white flex items-center gap-1.5 shadow-sm">
                <i class="ph ph-shield-check text-emerald-400 text-base"></i> Super Admin Authority
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

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl shrink-0">
                <i class="ph ph-check-circle"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Active Paid Subs</span>
                <div class="text-2xl font-black text-slate-900">{{ $totalActiveSubs }}</div>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl shrink-0">
                <i class="ph ph-hourglass-high"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Free Trials Active</span>
                <div class="text-2xl font-black text-slate-900">{{ $totalTrials }}</div>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl shrink-0">
                <i class="ph ph-currency-dollar"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Monthly Run-Rate</span>
                <div class="text-2xl font-black text-slate-900">${{ number_format($monthlyRevenueRunRate, 2) }}</div>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl shrink-0">
                <i class="ph ph-bell"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Unread Alerts</span>
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-black text-slate-900">{{ $unreadAlertsCount }}</span>
                    @if($unreadAlertsCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500 text-white animate-pulse">NEW</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
        <button type="button" @click="currentTab = 'tenants'"
                :class="currentTab === 'tenants' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2">
            <i class="ph ph-users text-sm"></i> Tenant Workspaces ({{ $workspaces->total() }})
        </button>

        <button type="button" @click="currentTab = 'plans'"
                :class="currentTab === 'plans' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2">
            <i class="ph ph-sliders text-sm"></i> Plan Configuration & Rules
        </button>

        <button type="button" @click="currentTab = 'alerts'"
                :class="currentTab === 'alerts' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2">
            <i class="ph ph-bell text-sm"></i> Subscription Alerts & Automation
            @if($unreadAlertsCount > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-red-500 text-white font-black">{{ $unreadAlertsCount }}</span>
            @endif
        </button>
    </div>

    <!-- TAB 1: TENANT WORKSPACES & ACTIVE SUBSCRIPTIONS -->
    <div x-show="currentTab === 'tenants'" class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Tenant Workspaces & Subscriptions</h3>
                    <p class="text-xs text-slate-500">Live roster of subscribed agencies, subscription status, and renewals</p>
                </div>
                <span class="text-xs bg-slate-100 text-slate-700 font-bold px-3 py-1 rounded-full">
                    {{ $workspaces->total() }} Total Registered
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3">Agency / Workspace</th>
                            <th class="px-6 py-3">Owner Contact</th>
                            <th class="px-6 py-3">Assigned Plan</th>
                            <th class="px-6 py-3">Subscription Status</th>
                            <th class="px-6 py-3">Valid Until / Expiration</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($workspaces as $ws)
                            @php
                                $sub = $ws->subscriptions->first();
                                $plan = $sub->plan ?? null;
                                $owner = $ws->users->first();
                                $status = $sub->status ?? 'none';
                            @endphp
                            <tr class="hover:bg-slate-50/50" x-data="{ openRenew: false }">
                                <td class="px-6 py-4">
                                    <span class="font-bold text-slate-900">{{ $ws->name }}</span>
                                    <span class="block text-xs font-mono text-slate-400">/{{ $ws->slug }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-medium text-slate-800">{{ $owner->name ?? 'N/A' }}</span>
                                    <span class="block text-xs text-slate-400">{{ $owner->email ?? 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($plan)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                                            @if($plan->code === 'enterprise') bg-purple-50 text-purple-700 border border-purple-200
                                            @elseif($plan->code === 'pro') bg-amber-50 text-amber-800 border border-amber-200
                                            @elseif($plan->code === 'starter') bg-blue-50 text-blue-700 border border-blue-200
                                            @else bg-slate-100 text-slate-700 border border-slate-200 @endif">
                                            <i class="ph ph-crown mr-1"></i> {{ $plan->name }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">No Plan</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider
                                        @if($status === 'active') bg-emerald-100 text-emerald-800
                                        @elseif($status === 'trialing') bg-blue-100 text-blue-800
                                        @elseif($status === 'expired') bg-rose-100 text-rose-800
                                        @elseif($status === 'suspended') bg-rose-100 text-rose-800
                                        @else bg-slate-100 text-slate-700 @endif">
                                        {{ $status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600">
                                    @if($sub && $sub->current_period_ends_at)
                                        {{ $sub->current_period_ends_at->format('M d, Y') }}
                                    @elseif($sub && $sub->trial_ends_at)
                                        @if($sub->trial_ends_at->isPast())
                                            <span class="text-rose-600 font-bold">Expired ({{ $sub->trial_ends_at->format('M d, Y') }})</span>
                                        @else
                                            <span class="text-blue-700 font-bold">Trial ends {{ $sub->trial_ends_at->format('M d, Y') }} ({{ $sub->trialDaysRemaining() }}d left)</span>
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <button @click="openRenew = true" class="px-2.5 py-1.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-bold transition">
                                        <i class="ph ph-arrow-clockwise"></i> Renew / Change
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
                                                                {{ $p->name }} (${{ number_format($p->price_cents / 100, 2) }}/mo)
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Extension Duration (Months)</label>
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
                {{ $workspaces->appends(['tab' => 'tenants'])->links() }}
            </div>
        </div>
    </div>

    <!-- TAB 2: PLAN CONFIGURATION & BUSINESS RULES -->
    <div x-show="currentTab === 'plans'" class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                    <i class="ph ph-sliders"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base">SaaS Packages & Business Rules Customizer</h3>
                    <p class="text-xs text-slate-500">Edit pricing, monthly invoice quotas, client allowances, team seats, and feature permissions for each tier.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 pt-2">
                @foreach($plans as $p)
                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/70 flex flex-col justify-between space-y-5">
                        <div class="border-b border-slate-200/80 pb-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 font-mono">{{ $p->code }}</span>
                                @if($p->is_active)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Active</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-600">Inactive</span>
                                @endif
                            </div>
                            <h4 class="text-lg font-black text-slate-900 mt-1">{{ $p->name }}</h4>
                            <div class="text-xs text-emerald-700 font-bold mt-0.5">
                                ${{ number_format($p->price_cents / 100, 2) }} / {{ $p->billing_interval }}
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.plans.update', $p->id) }}" class="space-y-3.5">
                            @csrf
                            <input type="hidden" name="tab" value="plans">

                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 uppercase mb-1">Package Name</label>
                                <input type="text" name="name" value="{{ $p->name }}" required
                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-bold bg-white">
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-700 uppercase mb-1">Price ($)</label>
                                    <input type="number" step="0.01" name="price_dollars" value="{{ number_format($p->price_cents / 100, 2, '.', '') }}" required
                                           class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white font-semibold">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-700 uppercase mb-1">Interval</label>
                                    <select name="billing_interval" class="w-full px-2 py-1.5 rounded-lg border border-slate-300 text-xs bg-white">
                                        <option value="month" {{ $p->billing_interval === 'month' ? 'selected' : '' }}>Monthly</option>
                                        <option value="year" {{ $p->billing_interval === 'year' ? 'selected' : '' }}>Yearly</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-700 uppercase mb-1" title="-1 = Unlimited">Invoices/Mo</label>
                                    <input type="number" name="invoice_limit" value="{{ $p->invoice_limit }}" required
                                           placeholder="-1 for Unlimited"
                                           class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-700 uppercase mb-1" title="-1 = Unlimited">Max Clients</label>
                                    <input type="number" name="client_limit" value="{{ $p->client_limit }}" required
                                           placeholder="-1 for Unlimited"
                                           class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-700 uppercase mb-1">Team Seats</label>
                                    <input type="number" name="team_seats" value="{{ $p->team_seats }}" min="1" required
                                           class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-700 uppercase mb-1">Currency</label>
                                    <input type="text" name="currency" value="{{ $p->currency }}" maxlength="3" required
                                           class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white uppercase">
                                </div>
                            </div>

                            <!-- Feature Toggles -->
                            <div class="space-y-1.5 pt-1 border-t border-slate-200/70">
                                <label class="flex items-center gap-2 cursor-pointer text-[11px] text-slate-700">
                                    <input type="checkbox" name="has_whitelabel" value="1" {{ $p->has_whitelabel ? 'checked' : '' }}
                                           class="w-3.5 h-3.5 text-red-600 rounded">
                                    <span>White-Label Branding</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-[11px] text-slate-700">
                                    <input type="checkbox" name="has_recurring" value="1" {{ $p->has_recurring ? 'checked' : '' }}
                                           class="w-3.5 h-3.5 text-red-600 rounded">
                                    <span>Recurring Invoicing</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-[11px] text-slate-700">
                                    <input type="checkbox" name="has_client_portal" value="1" {{ $p->has_client_portal ? 'checked' : '' }}
                                           class="w-3.5 h-3.5 text-red-600 rounded">
                                    <span>Interactive Pay Portal</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-[11px] font-bold text-emerald-700">
                                    <input type="checkbox" name="is_active" value="1" {{ $p->is_active ? 'checked' : '' }}
                                           class="w-3.5 h-3.5 text-emerald-600 rounded">
                                    <span>Package Active / Visible</span>
                                </label>
                            </div>

                            <!-- Feature List Bullets -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 uppercase mb-1">Marketing Bullets (1 per line)</label>
                                <textarea name="features_list" rows="3"
                                          placeholder="Bullet 1&#10;Bullet 2&#10;Bullet 3"
                                          class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-[11px] font-mono bg-white">{{ $p->features_list_text }}</textarea>
                            </div>

                            <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow transition flex items-center justify-center gap-1.5">
                                <i class="ph ph-floppy-disk text-sm"></i> Save {{ $p->name }}
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- TAB 3: SUBSCRIPTION ALERTS & NOTIFICATION AUTOMATION -->
    <div x-show="currentTab === 'alerts'" class="space-y-6">
        <!-- Notification Settings & Trial Automation Configuration -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-envelope"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Super Admin Subscription Alert Settings</h3>
                        <p class="text-xs text-slate-500">Configure email destinations and triggers for tenant lifecycle alerts.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.subscriptions.settings') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="tab" value="alerts">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Admin Notification Recipient Email *</label>
                            <input type="email" name="admin_alert_email" value="{{ $alertSettings['admin_alert_email'] }}" required
                                   placeholder="billing@redmapleagency.com"
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold bg-white">
                            <p class="text-[10px] text-slate-400 mt-1">Super admin alerts will be delivered immediately to this address</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Free Trial Duration (Days) *</label>
                            <input type="number" name="trial_duration_days" value="{{ $alertSettings['trial_duration_days'] }}" min="1" max="90" required
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold bg-white">
                            <p class="text-[10px] text-slate-400 mt-1">Default trial period for new user signups (standard: 14 days)</p>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-2">Automated Alert Triggers</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 bg-slate-50/60 cursor-pointer">
                                <input type="checkbox" name="alert_on_signup" value="1" {{ $alertSettings['alert_on_signup'] ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 rounded">
                                <div>
                                    <span class="block text-xs font-bold text-slate-800">New User Signup Alert</span>
                                    <span class="text-[10px] text-slate-500">Send alert when any new tenant registers a trial</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 bg-slate-50/60 cursor-pointer">
                                <input type="checkbox" name="alert_on_upgrade" value="1" {{ $alertSettings['alert_on_upgrade'] ? 'checked' : '' }}
                                       class="w-4 h-4 text-emerald-600 rounded">
                                <div>
                                    <span class="block text-xs font-bold text-slate-800">Plan Upgrade Alert</span>
                                    <span class="text-[10px] text-slate-500">Send alert when a customer upgrades their package</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 bg-slate-50/60 cursor-pointer">
                                <input type="checkbox" name="alert_on_downgrade" value="1" {{ $alertSettings['alert_on_downgrade'] ? 'checked' : '' }}
                                       class="w-4 h-4 text-amber-600 rounded">
                                <div>
                                    <span class="block text-xs font-bold text-slate-800">Plan Downgrade Alert</span>
                                    <span class="text-[10px] text-slate-500">Send alert when customer switches to a lower tier</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 bg-slate-50/60 cursor-pointer">
                                <input type="checkbox" name="alert_on_trial_expire" value="1" {{ $alertSettings['alert_on_trial_expire'] ? 'checked' : '' }}
                                       class="w-4 h-4 text-rose-600 rounded">
                                <div>
                                    <span class="block text-xs font-bold text-slate-800">Trial Expired Alert</span>
                                    <span class="text-[10px] text-slate-500">Send alert when a 14-day free trial concludes</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
                            <i class="ph ph-floppy-disk text-base"></i> Save Alert Preferences
                        </button>
                    </div>
                </form>
            </div>

            <!-- Manual Actions & Automated Scanner Trigger -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between space-y-4">
                <div class="space-y-2">
                    <div class="flex items-center gap-2 text-rose-600 font-bold text-sm">
                        <i class="ph ph-hourglass-low text-xl"></i>
                        <span>Trial Expiration Scanner</span>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Trigger on-demand scan across all workspaces to detect concluded 14-day trials, update their status, and send admin alerts.
                    </p>
                </div>

                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 text-xs">
                    <div class="font-bold flex items-center gap-1.5 mb-1">
                        <i class="ph ph-info"></i> Automated Cron
                    </div>
                    <span>Also executable via command:</span>
                    <code class="block font-mono bg-white px-2 py-1 rounded text-[11px] mt-1 border border-amber-200">php artisan subscriptions:check-expirations</code>
                </div>

                <form method="POST" action="{{ route('admin.subscriptions.check_expirations') }}">
                    @csrf
                    <input type="hidden" name="tab" value="alerts">
                    <button type="submit" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow transition flex items-center justify-center gap-2">
                        <i class="ph ph-arrows-clockwise text-base"></i> Run Expiration Check Now
                    </button>
                </form>
            </div>
        </div>

        <!-- Live Alerts Feed Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Live Subscription Activity & Alerts Feed</h3>
                    <p class="text-xs text-slate-500">Real-time audit log of customer signups, upgrades, downgrades, and trial expirations</p>
                </div>

                <div class="flex items-center gap-2">
                    @if($unreadAlertsCount > 0)
                        <form method="POST" action="{{ route('admin.subscriptions.alerts.read_all') }}">
                            @csrf
                            <input type="hidden" name="tab" value="alerts">
                            <button type="submit" class="px-3 py-1.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-bold transition flex items-center gap-1.5">
                                <i class="ph ph-checks text-sm"></i> Mark All as Read
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            @if($alerts->isEmpty())
                <div class="p-8 text-center text-slate-400 text-sm">
                    No subscription alerts recorded yet. Alerts will appear here when users signup or change plans.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3">Event Type</th>
                                <th class="px-6 py-3">Agency / Tenant</th>
                                <th class="px-6 py-3">Details & Message</th>
                                <th class="px-6 py-3">Dispatched At</th>
                                <th class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($alerts as $alert)
                                <tr class="hover:bg-slate-50/50 {{ !$alert->is_read ? 'bg-amber-50/30' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border {{ $alert->type_badge_class }}">
                                            <i class="ph {{ $alert->type_icon }}"></i>
                                            {{ strtoupper(str_replace('_', ' ', $alert->type)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-bold text-slate-900">{{ $alert->workspace->name ?? ($alert->metadata['workspace_name'] ?? 'N/A') }}</span>
                                        <span class="block text-xs text-slate-400">{{ $alert->user->email ?? ($alert->metadata['owner_email'] ?? 'N/A') }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-slate-800 text-xs">{{ $alert->title }}</p>
                                        <p class="text-xs text-slate-500 mt-0.5">{{ $alert->message }}</p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500">
                                        <span>{{ $alert->created_at->format('M d, Y • h:i A') }}</span>
                                        @if($alert->email_sent)
                                            <span class="block text-[10px] text-emerald-600 font-semibold"><i class="ph ph-check"></i> Emailed to Admin</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if(!$alert->is_read)
                                            <form method="POST" action="{{ route('admin.subscriptions.alerts.read', $alert->id) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="tab" value="alerts">
                                                <button type="submit" class="text-xs font-bold text-slate-600 hover:text-slate-900 underline">
                                                    Mark Read
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-400">Read</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-100">
                    {{ $alerts->appends(['tab' => 'alerts'])->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
