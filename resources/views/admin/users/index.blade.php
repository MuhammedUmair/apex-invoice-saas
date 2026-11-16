@extends('layouts.admin', ['title' => 'All Users Directory', 'header' => 'Platform Users & Role Management'])

@section('content')
<div class="space-y-6" x-data="{ roleModal: false, selectedUser: null, openRoleModal(user) { this.selectedUser = user; this.roleModal = true; } }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white tracking-tight">Platform Users Directory</h1>
            <p class="text-xs text-slate-400 mt-0.5">Manage user credentials, email verification status, and administrative permissions.</p>
        </div>

        <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by user email, name..."
                   class="bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl px-3.5 py-2 w-64 focus:ring-2 focus:ring-amber-500 outline-none">
            
            <select name="role" onchange="this.form.submit()" 
                    class="bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
                <option value="">All Roles</option>
                <option value="owner" {{ request('role') === 'owner' ? 'selected' : '' }}>Tenant Owners</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Tenant Admins</option>
                <option value="staff" {{ request('role') === 'staff' ? 'selected' : '' }}>Tenant Staff</option>
                <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Platform Admins</option>
            </select>

            <button type="submit" class="p-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl transition">
                <i class="ph ph-magnifying-glass"></i>
            </button>
        </form>
    </div>

    <!-- Users Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-[10px] uppercase font-bold tracking-wider bg-slate-950/40">
                        <th class="py-3 px-4">User</th>
                        <th class="py-3 px-4">Workspace</th>
                        <th class="py-3 px-4">Tenant Role</th>
                        <th class="py-3 px-4">Platform Authority</th>
                        <th class="py-3 px-4">Email Status</th>
                        <th class="py-3 px-4">Registered</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3 px-4">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="font-bold text-white hover:text-amber-400 transition">
                                    {{ $user->name }}
                                </a>
                                <div class="text-[10px] text-slate-400">{{ $user->email }}</div>
                            </td>
                            <td class="py-3 px-4">
                                @if($user->workspace)
                                    <a href="{{ route('admin.workspaces.show', $user->workspace_id) }}" class="text-slate-300 hover:text-white font-medium">
                                        {{ $user->workspace->name }}
                                    </a>
                                @else
                                    <span class="text-slate-500">&mdash;</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $user->isOwner() ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-slate-800 text-slate-300' }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if($user->isSuperAdmin())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                        <i class="ph ph-shield-star"></i> {{ $user->getAdminRoleLabel() }}
                                    </span>
                                @else
                                    <span class="text-slate-500 text-[11px]">Regular User</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($user->isEmailVerified())
                                    <span class="inline-flex items-center gap-1 text-[11px] text-emerald-400 font-medium">
                                        <i class="ph ph-check-circle"></i> Verified
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] text-amber-400 font-medium">
                                        <i class="ph ph-clock"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-400 text-[11px] whitespace-nowrap">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 transition" title="View User Detail">
                                        <i class="ph ph-eye text-sm"></i>
                                    </a>
                                    @if(auth()->user()->getAdminRole() === 'super_admin')
                                        <button @click="openRoleModal({{ json_encode(['id' => $user->id, 'name' => $user->name, 'admin_role' => $user->getAdminRole()]) }})"
                                                class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-amber-500 hover:text-slate-950 text-slate-300 text-[11px] font-bold transition">
                                            Role
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-xs text-slate-500">No users match your criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Admin Role Assignment Modal -->
    <div x-show="roleModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 z-50" x-cloak>
        <div class="w-full max-w-md rounded-2xl bg-slate-900 border border-slate-800 p-6 space-y-4 shadow-2xl" @click.away="roleModal = false">
            <div class="flex items-center gap-3 text-amber-400">
                <i class="ph ph-shield-star text-2xl font-bold"></i>
                <h3 class="font-bold text-white text-base">Assign Platform Admin Role</h3>
            </div>
            <p class="text-xs text-slate-300">
                Configure administrative privileges for <strong class="text-white" x-text="selectedUser?.name"></strong>.
            </p>
            <form :action="'/admin/security/admins/' + selectedUser?.id + '/role'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Administrative Sub-Role</label>
                    <select name="admin_role" class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-200">
                        <option value="none">None (Standard Tenant User)</option>
                        <option value="support_admin">Support Admin (Workspaces, Tickets, Feedback, Impersonation)</option>
                        <option value="finance_admin">Finance Admin (Ledgers, Refunds, Subscriptions, Invoices)</option>
                        <option value="technical_admin">Technical Admin (Monitoring, Errors, Jobs, Integrations)</option>
                        <option value="super_admin">Super Admin (Unrestricted Full Authority)</option>
                    </select>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="roleModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-xs font-bold text-slate-950">Update Privileges</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
