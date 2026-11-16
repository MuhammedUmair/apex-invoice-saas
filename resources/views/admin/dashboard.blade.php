@extends('layouts.admin', ['title' => 'Dashboard Overview', 'header' => 'Platform Overview & High-Value KPIs'])

@section('content')
<div class="space-y-8">
    <!-- Top Filter Bar & Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white tracking-tight flex items-center gap-2">
                <span>Platform Executive Control Room</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-500/10 text-amber-400 border border-amber-500/20">LIVE METRICS</span>
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Real-time SaaS health, financial run-rate, tenant lifecycle, and system diagnostics.</p>
        </div>

        <!-- Date Range Filter (Section 4 & 5) -->
        <div class="flex items-center gap-2">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                <select name="period" onchange="this.form.submit()" 
                        class="bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2 font-medium focus:ring-2 focus:ring-amber-500 outline-none">
                    <option value="all" {{ ($period ?? 'all') === 'all' ? 'selected' : '' }}>All Time</option>
                    <option value="today" {{ ($period ?? '') === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="last_7_days" {{ ($period ?? '') === 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="last_30_days" {{ ($period ?? '') === 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="this_month" {{ ($period ?? '') === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ ($period ?? '') === 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="this_year" {{ ($period ?? '') === 'this_year' ? 'selected' : '' }}>This Year</option>
                </select>
            </form>

            <a href="{{ route('admin.export', ['type' => 'workspaces']) }}" class="px-3 py-2 bg-slate-900 hover:bg-slate-800 border border-slate-700 text-slate-300 hover:text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                <i class="ph ph-download-simple"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- 12 High-Value Platform KPI Cards (Section 4) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
        <!-- 1. Total Workspaces -->
        <a href="{{ route('admin.workspaces.index') }}" class="block p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-amber-500/50 transition group shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider">Total Workspaces</span>
                <i class="ph ph-buildings text-lg text-sky-400 group-hover:scale-110 transition"></i>
            </div>
            <div class="text-2xl font-black text-white">{{ number_format($kpis['total_workspaces']) }}</div>
            <div class="text-[10px] text-slate-500 mt-1">All tenant workspaces</div>
        </a>

        <!-- 2. Active Workspaces -->
        <a href="{{ route('admin.workspaces.index', ['status' => 'active']) }}" class="block p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-emerald-500/50 transition group shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400">Active Tenants</span>
                <i class="ph ph-check-circle text-lg text-emerald-400 group-hover:scale-110 transition"></i>
            </div>
            <div class="text-2xl font-black text-emerald-400">{{ number_format($kpis['active_workspaces']) }}</div>
            <div class="text-[10px] text-slate-500 mt-1">Paid / valid access</div>
        </a>

        <!-- 3. Trial Workspaces -->
        <a href="{{ route('admin.workspaces.index', ['status' => 'trialing']) }}" class="block p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-amber-500/50 transition group shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-400">Trial Accounts</span>
                <i class="ph ph-hourglass-medium text-lg text-amber-400 group-hover:scale-110 transition"></i>
            </div>
            <div class="text-2xl font-black text-amber-400">{{ number_format($kpis['trial_workspaces']) }}</div>
            <div class="text-[10px] text-slate-500 mt-1">In evaluation window</div>
        </a>

        <!-- 4. Suspended Workspaces -->
        <a href="{{ route('admin.workspaces.suspended') }}" class="block p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-rose-500/50 transition group shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-400">Suspended</span>
                <i class="ph ph-prohibit text-lg text-rose-400 group-hover:scale-110 transition"></i>
            </div>
            <div class="text-2xl font-black text-rose-400">{{ number_format($kpis['suspended_workspaces']) }}</div>
            <div class="text-[10px] text-slate-500 mt-1">Temporarily locked</div>
        </a>

        <!-- 5. Total Users -->
        <a href="{{ route('admin.users.index') }}" class="block p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-indigo-500/50 transition group shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider">Total Users</span>
                <i class="ph ph-users text-lg text-indigo-400 group-hover:scale-110 transition"></i>
            </div>
            <div class="text-2xl font-black text-white">{{ number_format($kpis['total_users']) }}</div>
            <div class="text-[10px] text-slate-500 mt-1">Distinct platform seats</div>
        </a>

        <!-- 6. Monthly Recurring Revenue (MRR) -->
        <a href="{{ route('admin.finance.revenue') }}" class="block p-4 rounded-2xl bg-gradient-to-br from-slate-900 to-amber-950/20 border border-amber-500/30 hover:border-amber-500 transition group shadow-sm">
            <div class="flex items-center justify-between text-amber-400 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider">MRR Run-Rate</span>
                <i class="ph ph-trend-up text-lg text-amber-400 group-hover:scale-110 transition"></i>
            </div>
            <div class="text-2xl font-black text-amber-400">${{ number_format($kpis['mrr_dollars'], 2) }}</div>
            <div class="text-[10px] text-amber-500/80 mt-1">Normalized monthly recurring</div>
        </a>

        <!-- 7. Annual Recurring Revenue (ARR) -->
        <a href="{{ route('admin.finance.revenue') }}" class="block p-4 rounded-2xl bg-gradient-to-br from-slate-900 to-emerald-950/20 border border-emerald-500/30 hover:border-emerald-500 transition group shadow-sm">
            <div class="flex items-center justify-between text-emerald-400 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider">ARR Projected</span>
                <i class="ph ph-chart-line text-lg text-emerald-400 group-hover:scale-110 transition"></i>
            </div>
            <div class="text-2xl font-black text-emerald-400">${{ number_format($kpis['arr_dollars'], 2) }}</div>
            <div class="text-[10px] text-emerald-500/80 mt-1">MRR &times; 12 months</div>
        </a>

        <!-- 8. Total Platform Invoices -->
        <a href="{{ route('admin.finance.transactions') }}" class="block p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-cyan-500/50 transition group shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider">Total Invoices</span>
                <i class="ph ph-receipt text-lg text-cyan-400 group-hover:scale-110 transition"></i>
            </div>
            <div class="text-2xl font-black text-white">{{ number_format($kpis['total_invoices']) }}</div>
            <div class="text-[10px] text-slate-500 mt-1">Across all agency accounts</div>
        </a>

        <!-- 9. Successful Payments -->
        <a href="{{ route('admin.finance.transactions', ['status' => 'completed']) }}" class="block p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-emerald-500/50 transition group shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400">Paid Volume</span>
                <i class="ph ph-check-fat text-lg text-emerald-400 group-hover:scale-110 transition"></i>
            </div>
            <div class="text-2xl font-black text-emerald-400">${{ number_format($kpis['successful_payments_amount_dollars'], 2) }}</div>
            <div class="text-[10px] text-slate-500 mt-1">{{ number_format($kpis['successful_payments_count']) }} transactions</div>
        </a>

        <!-- 10. Failed Payments -->
        <a href="{{ route('admin.finance.failed_payments') }}" class="block p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-rose-500/50 transition group shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-400">Failed Volume</span>
                <i class="ph ph-warning-diamond text-lg text-rose-400 group-hover:scale-110 transition"></i>
            </div>
            <div class="text-2xl font-black text-rose-400">${{ number_format($kpis['failed_payments_amount_dollars'], 2) }}</div>
            <div class="text-[10px] text-slate-500 mt-1">{{ number_format($kpis['failed_payments_count']) }} attempts</div>
        </a>

        <!-- 11. Pending Upgrade Requests -->
        <a href="{{ route('admin.subscriptions.index', ['tab' => 'alerts']) }}" class="block p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-amber-500/50 transition group shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-400">Upgrades Pending</span>
                <i class="ph ph-bell text-lg text-amber-400 group-hover:scale-110 transition"></i>
            </div>
            <div class="text-2xl font-black text-amber-400">{{ number_format($kpis['pending_upgrade_requests']) }}</div>
            <div class="text-[10px] text-slate-500 mt-1">Awaiting wire / review</div>
        </a>

        <!-- 12. Churn Rate -->
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider">Churn Rate</span>
                <i class="ph ph-percent text-lg text-purple-400"></i>
            </div>
            <div class="text-2xl font-black text-white">{{ $kpis['churn_rate'] }}%</div>
            <div class="text-[10px] text-slate-500 mt-1">Cancellations vs base</div>
        </div>
    </div>

    <!-- Revenue Trend Chart & Plan Distribution Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Monthly Revenue Trend (2 Cols) -->
        <div class="lg:col-span-2 p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="font-bold text-white text-sm">Monthly Platform Revenue Trend (Last 6 Months)</h3>
                    <p class="text-xs text-slate-400">Aggregated completed subscription revenue in USD</p>
                </div>
                <a href="{{ route('admin.analytics') }}" class="text-xs text-amber-400 hover:text-amber-300 font-semibold flex items-center gap-1">
                    Detailed Analytics &rarr;
                </a>
            </div>

            <!-- Bar Chart Visualization -->
            <div class="h-48 flex items-end gap-3 sm:gap-6 pt-4 border-b border-slate-800 px-2">
                @php
                    $maxRev = 1;
                    foreach ($analytics['monthly_trend'] as $m) {
                        if ($m['revenue_dollars'] > $maxRev) $maxRev = $m['revenue_dollars'];
                    }
                @endphp
                @foreach($analytics['monthly_trend'] as $item)
                    @php
                        $heightPercent = max(8, round(($item['revenue_dollars'] / $maxRev) * 100));
                    @endphp
                    <div class="flex-1 flex flex-col items-center gap-2 group relative">
                        <div class="text-[10px] font-bold text-slate-400 opacity-0 group-hover:opacity-100 transition absolute -top-6">
                            ${{ number_format($item['revenue_dollars']) }}
                        </div>
                        <div class="w-full bg-slate-800 rounded-t-xl group-hover:bg-amber-500 transition duration-300 relative" style="height: {{ $heightPercent }}%;">
                            <div class="w-full h-full bg-gradient-to-t from-transparent to-white/10 rounded-t-xl"></div>
                        </div>
                        <span class="text-[11px] text-slate-400 font-medium whitespace-nowrap">{{ $item['month'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Plan Distribution (1 Col) -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-white text-sm">Active Subscriptions by Tier</h3>
                <span class="text-xs text-amber-400 font-bold">{{ $kpis['active_workspaces'] }} Total</span>
            </div>
            <div class="space-y-3">
                @forelse($analytics['plan_distribution'] as $plan)
                    <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800/80 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-xs text-white">{{ $plan['name'] }}</div>
                            <div class="text-[10px] text-slate-400">{{ $plan['price'] }}</div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-black bg-amber-500/10 text-amber-400 border border-amber-500/20">
                            {{ $plan['active_count'] }} active
                        </span>
                    </div>
                @empty
                    <div class="text-center py-8 text-xs text-slate-500">No active plans configured.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Operational Split: Recent Registrations & Live Alerts (Section 13) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Tenant Registrations -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <i class="ph ph-buildings text-amber-400 text-lg"></i>
                    <h3 class="font-bold text-white text-sm">Recent Workspace Registrations</h3>
                </div>
                <a href="{{ route('admin.workspaces.index') }}" class="text-xs text-amber-400 hover:text-amber-300 font-semibold">
                    View All &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-800/80">
                @forelse($recentRegistrations as $ws)
                    <div class="py-3 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <a href="{{ route('admin.workspaces.show', $ws->id) }}" class="font-bold text-xs text-white hover:text-amber-400 transition truncate block">
                                {{ $ws->name }}
                            </a>
                            <div class="text-[10px] text-slate-400 truncate">
                                Owner: {{ $ws->users->first()?->email ?? 'N/A' }} &bull; {{ $ws->created_at->diffForHumans() }}
                            </div>
                        </div>
                        <div class="shrink-0 flex items-center gap-2">
                            @php $sub = $ws->currentSubscription; @endphp
                            @if($sub && $sub->status === 'active')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    {{ $sub->plan?->name ?? 'Active' }}
                                </span>
                            @elseif($sub && $sub->status === 'suspended')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">Suspended</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">Trial</span>
                            @endif
                            <a href="{{ route('admin.workspaces.show', $ws->id) }}" class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                                <i class="ph ph-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-xs text-slate-500">No recent workspace registrations.</div>
                @endforelse
            </div>
        </div>

        <!-- Live Platform System Alerts -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <i class="ph ph-bell-ringing text-rose-400 text-lg"></i>
                    <h3 class="font-bold text-white text-sm">Live System Alerts & Notifications</h3>
                </div>
                <a href="{{ route('admin.subscriptions.index', ['tab' => 'alerts']) }}" class="text-xs text-amber-400 hover:text-amber-300 font-semibold">
                    Alert Center &rarr;
                </a>
            </div>

            <div class="space-y-3">
                @forelse($recentAlerts as $alert)
                    <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800/80 flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full mt-1.5 shrink-0 {{ $alert->is_read ? 'bg-slate-600' : 'bg-rose-500 animate-ping' }}"></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-bold text-xs text-white truncate">{{ $alert->title }}</span>
                                <span class="text-[10px] text-slate-500 shrink-0">{{ $alert->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-0.5 leading-relaxed">{{ $alert->message }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-xs text-slate-500">No pending system alerts. All services clear.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Platform Audit Activity Feed -->
    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <i class="ph ph-fingerprint text-violet-400 text-lg"></i>
                <h3 class="font-bold text-white text-sm">Recent Platform Administrative Activity</h3>
            </div>
            <a href="{{ route('admin.security.audit_logs') }}" class="text-xs text-amber-400 hover:text-amber-300 font-semibold">
                Complete Audit Trail &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-[10px] uppercase font-bold tracking-wider">
                        <th class="py-2.5 px-3">Timestamp</th>
                        <th class="py-2.5 px-3">Actor</th>
                        <th class="py-2.5 px-3">Action</th>
                        <th class="py-2.5 px-3">Target Entity</th>
                        <th class="py-2.5 px-3">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($recentActivities as $act)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-2.5 px-3 font-mono text-[11px] text-slate-400 whitespace-nowrap">{{ $act->created_at->format('M d, H:i:s') }}</td>
                            <td class="py-2.5 px-3 font-medium text-white">{{ $act->user?->name ?? 'System Automated' }}</td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-slate-800 border border-slate-700 text-amber-300">
                                    {{ $act->action }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-slate-400">{{ $act->workspace?->name ?? ($act->auditable_type ? class_basename($act->auditable_type) : 'Platform') }}</td>
                            <td class="py-2.5 px-3 font-mono text-[11px] text-slate-500">{{ $act->ip_address ?? '127.0.0.1' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-xs text-slate-500">No recent audit log entries recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
