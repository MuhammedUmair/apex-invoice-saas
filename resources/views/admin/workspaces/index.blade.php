@extends('layouts.admin', ['title' => 'Tenant Workspaces', 'header' => 'Customer Workspaces & Lifecycle Directory'])

@section('content')
<div class="space-y-6" x-data="{ 
    selectedWs: null,
    suspendModal: false,
    reinstateModal: false,
    openSuspend(id, name) {
        this.selectedWs = { id: id, name: name };
        this.suspendModal = true;
    },
    openReinstate(id, name) {
        this.selectedWs = { id: id, name: name };
        this.reinstateModal = true;
    }
}">
    <!-- Top Filter & Search Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white tracking-tight">Workspaces Directory</h1>
            <p class="text-xs text-slate-400 mt-0.5">Filter, inspect, audit, or initiate assisted support sessions for any tenant.</p>
        </div>

        <form method="GET" action="{{ route('admin.workspaces.index') }}" class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, slug, email..."
                   class="bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl px-3.5 py-2 w-64 focus:ring-2 focus:ring-amber-500 outline-none">
            
            <select name="status" onchange="this.form.submit()" 
                    class="bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                <option value="trialing" {{ request('status') === 'trialing' ? 'selected' : '' }}>Trial Only</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                <option value="canceled" {{ request('status') === 'canceled' ? 'selected' : '' }}>Canceled</option>
            </select>

            <button type="submit" class="p-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl transition">
                <i class="ph ph-magnifying-glass"></i>
            </button>
        </form>
    </div>

    <!-- Workspaces Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-[10px] uppercase font-bold tracking-wider bg-slate-950/40">
                        <th class="py-3 px-4">Workspace</th>
                        <th class="py-3 px-4">Primary Contact / Owner</th>
                        <th class="py-3 px-4">Subscription Plan</th>
                        <th class="py-3 px-4">Lifecycle Status</th>
                        <th class="py-3 px-4">Registered</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($workspaces as $ws)
                        @php
                            $owner = $ws->users->first();
                            $sub = $ws->currentSubscription;
                            $status = $sub ? $sub->status : 'trialing';
                        @endphp
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3 px-4">
                                <a href="{{ route('admin.workspaces.show', $ws->id) }}" class="font-bold text-white hover:text-amber-400 transition">
                                    {{ $ws->name }}
                                </a>
                                <div class="text-[10px] font-mono text-slate-500">{{ $ws->slug }} &bull; UUID: {{ substr($ws->id, 0, 8) }}...</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-slate-200">{{ $owner?->name ?? 'No Owner' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $owner?->email ?? 'N/A' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-semibold text-slate-200">{{ $sub?->plan?->name ?? 'Free Trial' }}</span>
                                <div class="text-[10px] text-slate-500 font-mono">{{ $sub?->gateway ?? 'system' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                @if($status === 'active')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <i class="ph ph-check-circle"></i> Active
                                    </span>
                                @elseif($status === 'suspended')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        <i class="ph ph-prohibit"></i> Suspended
                                    </span>
                                @elseif($status === 'canceled')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-700 text-slate-300">
                                        <i class="ph ph-x"></i> Canceled
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        <i class="ph ph-hourglass-medium"></i> Trial
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-400 text-[11px] whitespace-nowrap">
                                {{ $ws->created_at->format('M d, Y') }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.workspaces.show', $ws->id) }}" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 transition" title="View 360 Profile">
                                        <i class="ph ph-eye text-sm"></i>
                                    </a>
                                    
                                    @if($status === 'suspended')
                                        <button @click="openReinstate('{{ $ws->id }}', '{{ addslashes($ws->name) }}')" 
                                                class="px-2 py-1 rounded-lg bg-emerald-600/20 text-emerald-300 hover:bg-emerald-600 hover:text-white text-[11px] font-bold transition">
                                            Reinstate
                                        </button>
                                    @else
                                        <button @click="openSuspend('{{ $ws->id }}', '{{ addslashes($ws->name) }}')" 
                                                class="px-2 py-1 rounded-lg bg-rose-600/20 text-rose-300 hover:bg-rose-600 hover:text-white text-[11px] font-bold transition">
                                            Suspend
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-xs text-slate-500">No workspaces match your query.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($workspaces->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $workspaces->links() }}
            </div>
        @endif
    </div>

    <!-- Suspend Workspace Modal (Section 6 & 12: requires confirmation and reason) -->
    <div x-show="suspendModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 z-50" x-cloak>
        <div class="w-full max-w-md rounded-2xl bg-slate-900 border border-slate-800 p-6 space-y-4 shadow-2xl" @click.away="suspendModal = false">
            <div class="flex items-center gap-3 text-rose-400">
                <i class="ph ph-warning-octagon text-2xl font-bold"></i>
                <h3 class="font-bold text-white text-base">Confirm Workspace Suspension</h3>
            </div>
            <p class="text-xs text-slate-300">
                You are about to suspend <strong class="text-white" x-text="selectedWs?.name"></strong>. Tenant access will be immediately blocked.
            </p>
            <form :action="'/admin/workspaces/' + selectedWs?.id + '/suspend'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">
                        Suspension Reason (Mandatory for Audit) <span class="text-rose-400">*</span>
                    </label>
                    <textarea name="reason" required rows="3" placeholder="e.g. Terms violation, overdue payment, malicious activity..."
                              class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-200 focus:ring-2 focus:ring-rose-500 outline-none"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="suspendModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-xs font-bold text-white">Confirm Suspension</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reinstate Workspace Modal -->
    <div x-show="reinstateModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 z-50" x-cloak>
        <div class="w-full max-w-md rounded-2xl bg-slate-900 border border-slate-800 p-6 space-y-4 shadow-2xl" @click.away="reinstateModal = false">
            <div class="flex items-center gap-3 text-emerald-400">
                <i class="ph ph-check-circle text-2xl font-bold"></i>
                <h3 class="font-bold text-white text-base">Reinstate Workspace Access</h3>
            </div>
            <p class="text-xs text-slate-300">
                Reinstate active subscription access for <strong class="text-white" x-text="selectedWs?.name"></strong>.
            </p>
            <form :action="'/admin/workspaces/' + selectedWs?.id + '/reinstate'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">
                        Reinstatement Reason (Mandatory for Audit) <span class="text-emerald-400">*</span>
                    </label>
                    <textarea name="reason" required rows="3" placeholder="e.g. Account reviewed, payment cleared, dispute resolved..."
                              class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-200 focus:ring-2 focus:ring-emerald-500 outline-none"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="reinstateModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white">Reinstate Workspace</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
