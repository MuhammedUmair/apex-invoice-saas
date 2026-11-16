@extends('layouts.admin', ['title' => 'Admin Users & Roles', 'header' => 'Platform Administrators & Least-Privilege Roles'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white flex items-center gap-2">
                <i class="ph ph-shield-star text-amber-400"></i> Platform Administrators
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Role separation: Super Admin, Finance Admin, Support Admin, and Technical Admin.</p>
        </div>
    </div>

    <!-- Admins Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-[10px] uppercase font-bold tracking-wider bg-slate-950/40">
                        <th class="py-3 px-4">Administrator</th>
                        <th class="py-3 px-4">Assigned Admin Role</th>
                        <th class="py-3 px-4">Two-Factor Authentication</th>
                        <th class="py-3 px-4">Created At</th>
                        <th class="py-3 px-4 text-right">Modify Role</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($admins as $admin)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3 px-4">
                                <div class="font-bold text-white">{{ $admin->name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $admin->email }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                    {{ $admin->getAdminRoleLabel() }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if($admin->hasTwoFactorEnabled())
                                    <span class="text-emerald-400 font-semibold flex items-center gap-1">
                                        <i class="ph ph-lock-key"></i> Enabled
                                    </span>
                                @else
                                    <span class="text-amber-400 font-semibold flex items-center gap-1">
                                        <i class="ph ph-shield-warning"></i> Recommended
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-400 text-[11px] whitespace-nowrap">{{ $admin->created_at->format('M d, Y') }}</td>
                            <td class="py-3 px-4 text-right">
                                @if(auth()->user()->getAdminRole() === 'super_admin')
                                    <form action="{{ route('admin.security.admins.role', $admin->id) }}" method="POST" class="inline-flex items-center gap-2">
                                        @csrf
                                        <select name="admin_role" onchange="this.form.submit()" 
                                                class="bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-lg px-2 py-1">
                                            <option value="super_admin" {{ $admin->getAdminRole() === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                            <option value="finance_admin" {{ $admin->getAdminRole() === 'finance_admin' ? 'selected' : '' }}>Finance Admin</option>
                                            <option value="support_admin" {{ $admin->getAdminRole() === 'support_admin' ? 'selected' : '' }}>Support Admin</option>
                                            <option value="technical_admin" {{ $admin->getAdminRole() === 'technical_admin' ? 'selected' : '' }}>Technical Admin</option>
                                            <option value="none">Revoke Admin</option>
                                        </select>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-xs text-slate-500">No administrators configured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
