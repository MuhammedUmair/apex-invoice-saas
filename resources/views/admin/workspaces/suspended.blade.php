@extends('layouts.admin', ['title' => 'Suspended Workspaces', 'header' => 'Suspended Accounts Queue'])

@section('content')
<div class="space-y-6" x-data="{ 
    selectedWs: null,
    reinstateModal: false,
    openReinstate(id, name) {
        this.selectedWs = { id: id, name: name };
        this.reinstateModal = true;
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white tracking-tight flex items-center gap-2">
                <i class="ph ph-prohibit text-rose-400"></i>
                <span>Suspended Tenant Workspaces</span>
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Workspaces currently locked from accessing the Invoicy SaaS platform.</p>
        </div>

        <a href="{{ route('admin.workspaces.index') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
            <i class="ph ph-arrow-left"></i> All Workspaces
        </a>
    </div>

    <!-- Suspended List -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-[10px] uppercase font-bold tracking-wider bg-slate-950/40">
                        <th class="py-3 px-4">Workspace</th>
                        <th class="py-3 px-4">Owner Email</th>
                        <th class="py-3 px-4">Suspension Reason / Audit</th>
                        <th class="py-3 px-4">Suspended On</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($workspaces as $ws)
                        @php
                            $owner = $ws->users->first();
                            $lastSuspendAudit = \App\Models\AuditLog::where('workspace_id', $ws->id)
                                ->where('action', 'admin.workspace_suspended')
                                ->latest('created_at')
                                ->first();
                            $reason = $lastSuspendAudit?->new_values['reason'] ?? 'Administrator action';
                        @endphp
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3 px-4">
                                <a href="{{ route('admin.workspaces.show', $ws->id) }}" class="font-bold text-white hover:text-amber-400 transition">
                                    {{ $ws->name }}
                                </a>
                                <div class="text-[10px] font-mono text-slate-500">{{ $ws->slug }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-300">
                                {{ $owner?->email ?? 'No contact' }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="text-rose-300 font-medium max-w-sm truncate">{{ $reason }}</div>
                                <div class="text-[10px] text-slate-500">By {{ $lastSuspendAudit?->new_values['actor'] ?? 'Admin' }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-400 text-[11px] whitespace-nowrap">
                                {{ $ws->updated_at->format('M d, Y H:i') }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <button @click="openReinstate('{{ $ws->id }}', '{{ addslashes($ws->name) }}')"
                                        class="px-3 py-1.5 rounded-xl bg-emerald-600/20 text-emerald-300 hover:bg-emerald-600 hover:text-white font-bold transition flex items-center gap-1 inline-flex text-xs">
                                    <i class="ph ph-arrow-counter-clockwise"></i> Reinstate
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-xs text-slate-500">
                                No workspaces currently in suspended state.
                            </td>
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

    <!-- Reinstate Modal -->
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
                    <textarea name="reason" required rows="3" placeholder="e.g. Account cleared, payment confirmed..."
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
