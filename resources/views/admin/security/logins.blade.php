@extends('layouts.admin', ['title' => 'Login History', 'header' => 'Sign-In History & Access Logs'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white flex items-center gap-2">
                <i class="ph ph-sign-in text-blue-400"></i> Platform Login History
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Audit log of all successful, failed, and locked authentication attempts.</p>
        </div>

        <form method="GET" action="{{ route('admin.security.logins') }}" class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by email, IP..."
                   class="bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl px-3.5 py-2 w-64 focus:ring-2 focus:ring-amber-500 outline-none">
            
            <select name="status" onchange="this.form.submit()" 
                    class="bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
                <option value="">All Statuses</option>
                <option value="successful" {{ request('status') === 'successful' ? 'selected' : '' }}>Successful</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked / Lockout</option>
            </select>
            
            <button type="submit" class="p-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl transition">
                <i class="ph ph-magnifying-glass"></i>
            </button>
        </form>
    </div>

    <!-- Login History Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-[10px] uppercase font-bold tracking-wider bg-slate-950/40">
                        <th class="py-3 px-4">Timestamp</th>
                        <th class="py-3 px-4">Account Email</th>
                        <th class="py-3 px-4">Outcome Status</th>
                        <th class="py-3 px-4">Device</th>
                        <th class="py-3 px-4">IP Address</th>
                        <th class="py-3 px-4">Notes / Failure Reason</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($logins as $l)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-400 whitespace-nowrap">{{ $l->created_at->format('M d, Y H:i:s') }}</td>
                            <td class="py-3 px-4 font-bold text-white">{{ $l->email }}</td>
                            <td class="py-3 px-4">
                                @if($l->status === 'successful')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        Successful
                                    </span>
                                @elseif($l->status === 'blocked')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        Lockout / Blocked
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        Failed
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-400">{{ $l->device ?? 'Desktop' }}</td>
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-500">{{ $l->ip_address }}</td>
                            <td class="py-3 px-4 text-slate-400 text-[11px]">{{ $l->failure_reason ?? '&mdash;' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-xs text-slate-500">No login history recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logins->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $logins->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
