@extends('layouts.admin', ['title' => 'Audit Trail', 'header' => 'Platform Audit Trail & Security Ledger'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white flex items-center gap-2">
                <i class="ph ph-fingerprint text-violet-400"></i> Platform Audit Trail
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Immutable audit log of all administrative actions, data exports, plan changes, and security events.</p>
        </div>

        <form method="GET" action="{{ route('admin.security.audit_logs') }}" class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search action, actor, IP..."
                   class="bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl px-3.5 py-2 w-64 focus:ring-2 focus:ring-amber-500 outline-none">
            
            <button type="submit" class="p-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl transition">
                <i class="ph ph-magnifying-glass"></i>
            </button>
        </form>
    </div>

    <!-- Audit Logs Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-[10px] uppercase font-bold tracking-wider bg-slate-950/40">
                        <th class="py-3 px-4">Timestamp</th>
                        <th class="py-3 px-4">Operator / Actor</th>
                        <th class="py-3 px-4">Action Event</th>
                        <th class="py-3 px-4">Target Entity</th>
                        <th class="py-3 px-4">Payload & Differences</th>
                        <th class="py-3 px-4">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($auditLogs as $log)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-400 whitespace-nowrap">{{ $log->created_at->format('M d, H:i:s') }}</td>
                            <td class="py-3 px-4 font-bold text-white">{{ $log->user?->email ?? 'System Automated' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded font-mono text-[10px] bg-slate-800 text-amber-300 border border-slate-700">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-300">
                                {{ $log->workspace?->name ?? ($log->auditable_type ? class_basename($log->auditable_type) : 'Platform Global') }}
                            </td>
                            <td class="py-3 px-4">
                                @if(!empty($log->new_values) || !empty($log->old_values))
                                    <div class="max-w-xs truncate text-[10px] font-mono text-slate-400 bg-slate-950/60 p-1.5 rounded">
                                        {{ json_encode($log->new_values ?? $log->old_values) }}
                                    </div>
                                @else
                                    <span class="text-slate-600">&mdash;</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-500">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-xs text-slate-500">No audit events match your criteria.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($auditLogs->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $auditLogs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
