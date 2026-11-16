@extends('layouts.admin', ['title' => 'Support Tickets', 'header' => 'Customer Support Desk'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white flex items-center gap-2">
                <i class="ph ph-ticket text-cyan-400"></i> Support Tickets Desk
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Customer assistance inquiries, billing disputes, and technical troubleshooting.</p>
        </div>

        <form method="GET" action="{{ route('admin.support.tickets') }}" class="flex items-center gap-2">
            <select name="status" onchange="this.form.submit()" 
                    class="bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
                <option value="">All Statuses</option>
                <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="waiting_on_customer" {{ request('status') === 'waiting_on_customer' ? 'selected' : '' }}>Waiting on Customer</option>
                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
            </select>

            <select name="priority" onchange="this.form.submit()" 
                    class="bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
                <option value="">All Priorities</option>
                <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
            </select>
        </form>
    </div>

    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-[10px] uppercase font-bold tracking-wider bg-slate-950/40">
                        <th class="py-3 px-4">Ticket</th>
                        <th class="py-3 px-4">Workspace / Customer</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Priority</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Created</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($tickets as $tck)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3 px-4">
                                <a href="{{ route('admin.support.tickets.show', $tck->id) }}" class="font-bold text-white hover:text-amber-400 transition">
                                    {{ $tck->subject }}
                                </a>
                                <div class="text-[10px] font-mono text-slate-500">{{ $tck->ticket_number }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-slate-200">{{ $tck->workspace?->name ?? 'External' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $tck->user?->email ?? 'N/A' }}</div>
                            </td>
                            <td class="py-3 px-4 uppercase text-[10px] font-mono text-slate-400">{{ $tck->category }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $tck->getPriorityBadgeClass() }}">
                                    {{ $tck->priority }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $tck->getStatusBadgeClass() }}">
                                    {{ str_replace('_', ' ', $tck->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-400 text-[11px] whitespace-nowrap">{{ $tck->created_at->format('M d, H:i') }}</td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('admin.support.tickets.show', $tck->id) }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition">
                                    Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-xs text-slate-500">No support tickets currently open.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
