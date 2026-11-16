@extends('layouts.admin', ['title' => 'Security Events', 'header' => 'Security Events & Assisted Access'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white flex items-center gap-2">
                <i class="ph ph-shield-warning text-rose-400"></i> Platform Security Events
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Assisted access / impersonation sessions and suspicious authentication events.</p>
        </div>
    </div>

    <!-- Assisted Access / Impersonation Sessions Log (Section 11) -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-sm space-y-4 p-6">
        <h3 class="font-bold text-white text-sm flex items-center gap-2">
            <i class="ph ph-user-switch text-amber-400"></i> Privileged Assisted Access History
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-[10px] uppercase font-bold tracking-wider">
                        <th class="py-2.5 px-3">Session Start</th>
                        <th class="py-2.5 px-3">Operator</th>
                        <th class="py-2.5 px-3">Target Workspace</th>
                        <th class="py-2.5 px-3">Mandatory Support Reason</th>
                        <th class="py-2.5 px-3">Session End</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($impersonations as $imp)
                        <tr>
                            <td class="py-2.5 px-3 font-mono text-[11px] text-slate-400">{{ $imp->started_at->format('M d, H:i') }}</td>
                            <td class="py-2.5 px-3 font-bold text-amber-400">{{ $imp->operator?->email ?? 'Admin' }}</td>
                            <td class="py-2.5 px-3 text-white">{{ $imp->targetWorkspace?->name ?? 'Workspace' }}</td>
                            <td class="py-2.5 px-3 text-slate-300 text-[11px] max-w-sm truncate">{{ $imp->reason }}</td>
                            <td class="py-2.5 px-3 font-mono text-slate-400">
                                {{ $imp->ended_at ? $imp->ended_at->format('M d, H:i') : 'Active / In Progress' }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-xs text-slate-500">No assisted access sessions recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Suspicious Failed Logins -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-sm space-y-4 p-6">
        <h3 class="font-bold text-white text-sm flex items-center gap-2">
            <i class="ph ph-warning text-rose-400"></i> Failed Sign-In Activity
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-[10px] uppercase font-bold tracking-wider">
                        <th class="py-2.5 px-3">Timestamp</th>
                        <th class="py-2.5 px-3">Attempted Email</th>
                        <th class="py-2.5 px-3">IP Address</th>
                        <th class="py-2.5 px-3">Failure Reason</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($suspiciousLogins as $sl)
                        <tr>
                            <td class="py-2.5 px-3 font-mono text-[11px] text-slate-400">{{ $sl->created_at->format('M d, H:i:s') }}</td>
                            <td class="py-2.5 px-3 font-bold text-rose-300">{{ $sl->email }}</td>
                            <td class="py-2.5 px-3 font-mono text-slate-500">{{ $sl->ip_address }}</td>
                            <td class="py-2.5 px-3 text-slate-400">{{ $sl->failure_reason ?? 'Invalid credentials' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 text-center text-xs text-slate-500">No recent failed logins.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
