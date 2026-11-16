@extends('layouts.admin', ['title' => 'User Profile: ' . $user->name, 'header' => 'Platform User Detail'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white">{{ $user->name }}</h1>
            <p class="text-xs text-slate-400 mt-0.5">Account credentials, session metadata, security events, and audit actions.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
            <i class="ph ph-arrow-left"></i> All Users
        </a>
    </div>

    <!-- Metadata Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-amber-500 text-slate-950 font-black text-xl flex items-center justify-center">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div>
                    <div class="font-bold text-white text-sm">{{ $user->name }}</div>
                    <div class="text-xs text-slate-400">{{ $user->email }}</div>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800/80 space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-400">Workspace:</span>
                    <strong class="text-white">{{ $user->workspace?->name ?? 'None' }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Role:</span>
                    <strong class="text-amber-400 uppercase font-mono">{{ $user->role }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Platform Admin:</span>
                    <strong class="text-white">{{ $user->getAdminRoleLabel() }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Verified:</span>
                    <strong class="{{ $user->isEmailVerified() ? 'text-emerald-400' : 'text-amber-400' }}">
                        {{ $user->isEmailVerified() ? 'Yes' : 'Pending' }}
                    </strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Created:</span>
                    <strong class="text-slate-300">{{ $user->created_at->format('M d, Y') }}</strong>
                </div>
            </div>
        </div>

        <!-- Recent Sign-in History -->
        <div class="md:col-span-2 p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-4">
            <h3 class="font-bold text-white text-sm flex items-center gap-2">
                <i class="ph ph-sign-in text-blue-400"></i> Recent Sign-In Metadata
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400 text-[10px] uppercase font-bold">
                            <th class="py-2 px-3">Date</th>
                            <th class="py-2 px-3">Status</th>
                            <th class="py-2 px-3">Device</th>
                            <th class="py-2 px-3">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        @forelse($loginHistory as $lh)
                            <tr>
                                <td class="py-2 px-3 font-mono text-[11px] text-slate-400">{{ $lh->created_at->format('M d, H:i') }}</td>
                                <td class="py-2 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $lh->status === 'successful' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400' }}">
                                        {{ $lh->status }}
                                    </span>
                                </td>
                                <td class="py-2 px-3 text-slate-400">{{ $lh->device ?? 'Desktop' }}</td>
                                <td class="py-2 px-3 font-mono text-slate-500">{{ $lh->ip_address }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-center text-xs text-slate-500">No login attempts logged for this user.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
