@extends('layouts.admin', ['title' => 'Workspace Activity - ' . $workspace->name, 'header' => 'Tenant Activity & Audit Trail'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white">Activity History: {{ $workspace->name }}</h1>
            <p class="text-xs text-slate-400 mt-0.5">Chronological immutable audit log of mutations, subscription changes, and operator logins.</p>
        </div>
        <a href="{{ route('admin.workspaces.show', $workspace->id) }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
            <i class="ph ph-arrow-left"></i> Back to Workspace
        </a>
    </div>

    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-[10px] uppercase font-bold tracking-wider bg-slate-950/40">
                        <th class="py-3 px-4">Timestamp</th>
                        <th class="py-3 px-4">Actor</th>
                        <th class="py-3 px-4">Action</th>
                        <th class="py-3 px-4">Values / Changes</th>
                        <th class="py-3 px-4">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($activity as $act)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-400 whitespace-nowrap">{{ $act->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="py-3 px-4 text-white font-medium">{{ $act->user?->name ?? 'System' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded font-mono text-[10px] bg-slate-800 text-amber-300 border border-slate-700">
                                    {{ $act->action }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-400">
                                @if(!empty($act->new_values))
                                    <pre class="text-[10px] font-mono text-slate-300 bg-slate-950/60 p-1.5 rounded max-w-md overflow-x-auto">{{ json_encode($act->new_values, JSON_PRETTY_PRINT) }}</pre>
                                @else
                                    <span class="text-slate-500">&mdash;</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-500">{{ $act->ip_address }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-xs text-slate-500">No activity recorded for this workspace.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($activity->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $activity->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
