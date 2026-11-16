@extends('layouts.admin', ['title' => 'Workspace: ' . $workspace->name, 'header' => 'Tenant 360° Profile'])

@section('content')
<div class="space-y-8" x-data="{ impersonateModal: false, deleteModal: false }">
    <!-- Header Summary Card -->
    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-start gap-4">
            <div class="w-14 h-14 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 font-black text-2xl shrink-0">
                {{ substr($workspace->name, 0, 1) }}
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl font-black text-white">{{ $workspace->name }}</h1>
                    @php $sub = $workspace->currentSubscription; $st = $sub ? $sub->status : 'trialing'; @endphp
                    @if($st === 'active')
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Active</span>
                    @elseif($st === 'suspended')
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">Suspended</span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">Free Trial</span>
                    @endif
                </div>
                <div class="text-xs text-slate-400 mt-1 flex flex-wrap items-center gap-3">
                    <span>Slug: <strong class="text-slate-200">{{ $workspace->slug }}</strong></span>
                    <span>UUID: <strong class="text-slate-200 font-mono">{{ $workspace->id }}</strong></span>
                    <span>Registered: <strong class="text-slate-200">{{ $workspace->created_at->format('M d, Y') }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Privileged Actions Strip -->
        <div class="flex items-center gap-3">
            <!-- Assisted Access / Impersonation Button (Section 11) -->
            <button @click="impersonateModal = true" 
                    class="px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <i class="ph ph-user-switch font-bold text-base"></i> Assisted Support Session
            </button>
            <button @click="deleteModal = true" class="px-3.5 py-2 bg-rose-600/20 text-rose-300 hover:bg-rose-600 hover:text-white rounded-xl text-xs font-bold transition flex items-center gap-1">
                <i class="ph ph-trash"></i> Delete Workspace
            </button>
            <a href="{{ route('admin.workspaces.activity', $workspace->id) }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold transition flex items-center gap-1">
                <i class="ph ph-clock-counter-clockwise"></i> Activity Log
            </a>
        </div>
    </div>

    <!-- Tenant Stats Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800">
            <div class="text-[10px] font-bold text-slate-400 uppercase">Subscription Plan</div>
            <div class="text-lg font-bold text-white mt-1">{{ $subscription?->plan?->name ?? 'Trial Plan' }}</div>
            <div class="text-[10px] text-slate-500">{{ $subscription?->plan?->formatted_price ?? '$0.00' }}</div>
        </div>
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800">
            <div class="text-[10px] font-bold text-slate-400 uppercase">Total Invoices Issued</div>
            <div class="text-lg font-bold text-cyan-400 mt-1">{{ number_format($invoicesCount) }}</div>
            <div class="text-[10px] text-slate-500">${{ number_format($totalBilledCents / 100, 2) }} billed</div>
        </div>
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800">
            <div class="text-[10px] font-bold text-slate-400 uppercase">Clients Managed</div>
            <div class="text-lg font-bold text-emerald-400 mt-1">{{ $workspace->clients->count() }}</div>
            <div class="text-[10px] text-slate-500">Active CRM records</div>
        </div>
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800">
            <div class="text-[10px] font-bold text-slate-400 uppercase">Team Members</div>
            <div class="text-lg font-bold text-indigo-400 mt-1">{{ $workspace->users->count() }}</div>
            <div class="text-[10px] text-slate-500">Registered users</div>
        </div>
    </div>

    <!-- Split: Team Members & Subscription Details -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Team Members -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm">
            <h3 class="font-bold text-white text-sm mb-4 flex items-center gap-2">
                <i class="ph ph-users text-indigo-400"></i> Team Members & Roles
            </h3>
            <div class="divide-y divide-slate-800/80">
                @foreach($workspace->users as $user)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-xs text-white">{{ $user->name }}</div>
                            <div class="text-[11px] text-slate-400">{{ $user->email }}</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $user->isOwner() ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-slate-800 text-slate-300' }}">
                                {{ $user->role }}
                            </span>
                            @if($user->isEmailVerified())
                                <i class="ph ph-check-circle text-emerald-400" title="Email Verified"></i>
                            @else
                                <i class="ph ph-warning-circle text-amber-400" title="Unverified Email"></i>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Subscription Management Card -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-4">
            <h3 class="font-bold text-white text-sm flex items-center gap-2">
                <i class="ph ph-crown text-amber-400"></i> Subscription & Renewal
            </h3>
            <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800/80 space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-400">Current Plan:</span>
                    <strong class="text-white">{{ $subscription?->plan?->name ?? 'Free Trial' }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Gateway:</span>
                    <strong class="text-slate-200 font-mono">{{ $subscription?->gateway ?? 'None' }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Period Ends:</span>
                    <strong class="text-slate-200">
                        {{ $subscription?->current_period_ends_at ? $subscription->current_period_ends_at->format('M d, Y') : ($subscription?->trial_ends_at ? $subscription->trial_ends_at->format('M d, Y') : 'Indefinite') }}
                    </strong>
                </div>
            </div>

            <!-- Renewal Form -->
            <form method="POST" action="{{ route('admin.workspaces.renew', $workspace->id) }}" class="space-y-3 pt-2">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Upgrade / Assign Plan</label>
                        <select name="plan_id" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-200">
                            @foreach($plans as $p)
                                <option value="{{ $p->id }}" {{ $subscription?->plan_id === $p->id ? 'selected' : '' }}>
                                    {{ $p->name }} ({{ $p->formatted_price }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Duration (Months)</label>
                        <input type="number" name="duration_months" value="1" min="1" max="36" 
                               class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-200">
                    </div>
                </div>
                <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-bold transition">
                    Apply Subscription Change
                </button>
            </form>
        </div>
    </div>

    <!-- Recent Audit Logs for this Tenant -->
    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm">
        <h3 class="font-bold text-white text-sm mb-4 flex items-center gap-2">
            <i class="ph ph-fingerprint text-violet-400"></i> Recent Tenant Audit Trail
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-[10px] uppercase font-bold tracking-wider">
                        <th class="py-2.5 px-3">Date</th>
                        <th class="py-2.5 px-3">Actor</th>
                        <th class="py-2.5 px-3">Action</th>
                        <th class="py-2.5 px-3">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($recentAuditLogs as $log)
                        <tr>
                            <td class="py-2.5 px-3 font-mono text-[11px] text-slate-400">{{ $log->created_at->format('M d, H:i') }}</td>
                            <td class="py-2.5 px-3 text-white">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="py-2.5 px-3"><span class="px-2 py-0.5 rounded font-mono text-[10px] bg-slate-800 text-amber-300">{{ $log->action }}</span></td>
                            <td class="py-2.5 px-3 font-mono text-slate-500">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-4 text-center text-xs text-slate-500">No audit events recorded for this workspace.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Assisted Session / Impersonation Modal (Section 11) -->
    <div x-show="impersonateModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 z-50" x-cloak>
        <div class="w-full max-w-md rounded-2xl bg-slate-900 border border-slate-800 p-6 space-y-4 shadow-2xl" @click.away="impersonateModal = false">
            <div class="flex items-center gap-3 text-amber-400">
                <i class="ph ph-shield-warning text-2xl font-bold"></i>
                <h3 class="font-bold text-white text-base">Start Privileged Assisted Session</h3>
            </div>
            <p class="text-xs text-slate-300 leading-relaxed">
                You are initiating an assisted session for <strong class="text-white">{{ $workspace->name }}</strong>. 
                Per Section 11 compliance, all actions will be recorded in the audit trail, and an assisted banner will display throughout your session.
            </p>
            <form action="{{ route('admin.impersonate.start', $workspace->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">
                        Support Reason / Ticket Reference <span class="text-amber-400">*</span>
                    </label>
                    <textarea name="reason" required rows="3" placeholder="e.g. Assisting customer with invoice template issue per Ticket #TCK-8821..."
                              class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-200 focus:ring-2 focus:ring-amber-500 outline-none"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="impersonateModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-xs font-bold text-slate-950">Confirm & Launch Session</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Permanent Delete Workspace Modal -->
    <div x-show="deleteModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 z-50" x-cloak>
        <div class="w-full max-w-md rounded-2xl bg-slate-900 border border-slate-800 p-6 space-y-4 shadow-2xl" @click.away="deleteModal = false">
            <div class="flex items-center gap-3 text-rose-400">
                <i class="ph ph-trash text-2xl font-bold"></i>
                <h3 class="font-bold text-white text-base">Permanently Purge Workspace</h3>
            </div>
            <p class="text-xs text-slate-300 leading-relaxed">
                Are you sure you want to completely delete <strong class="text-white">{{ $workspace->name }}</strong>? 
                This will purge all users, subscriptions, and data associated with this tenant. This action is irreversible.
            </p>
            <form action="{{ route('admin.workspaces.destroy', $workspace->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('DELETE')
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">
                        Deletion Justification (Audit) <span class="text-rose-400">*</span>
                    </label>
                    <textarea name="reason" required rows="2" placeholder="e.g. Spam account, test workspace, customer account closure..."
                              class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-200 focus:ring-2 focus:ring-rose-500 outline-none"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="deleteModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-xs font-bold text-white">Permanently Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
