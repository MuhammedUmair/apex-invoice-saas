@extends('layouts.admin', ['title' => 'Subscription Invoices', 'header' => 'Platform Subscription Invoices'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white">SaaS Subscription Invoices</h1>
            <p class="text-xs text-slate-400 mt-0.5">Platform-level billing charges issued to tenant agencies for their platform memberships.</p>
        </div>
    </div>

    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-[10px] uppercase font-bold tracking-wider bg-slate-950/40">
                        <th class="py-3 px-4">Workspace</th>
                        <th class="py-3 px-4">Subscribed Plan</th>
                        <th class="py-3 px-4">Gateway</th>
                        <th class="py-3 px-4">Billing Rate</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Period Ending</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($subscriptions as $sub)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3 px-4 font-bold text-white">
                                <a href="{{ route('admin.workspaces.show', $sub->workspace_id) }}" class="hover:text-amber-400 transition">
                                    {{ $sub->workspace?->name ?? 'Deleted Workspace' }}
                                </a>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-200">{{ $sub->plan?->name ?? 'Free Trial' }}</td>
                            <td class="py-3 px-4 uppercase text-[10px] font-mono text-slate-400">{{ $sub->gateway }}</td>
                            <td class="py-3 px-4 font-mono font-bold text-emerald-400">{{ $sub->plan?->formatted_price ?? '$0.00' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $sub->status === 'active' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-slate-800 text-slate-400' }}">
                                    {{ $sub->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-400">
                                {{ $sub->current_period_ends_at ? $sub->current_period_ends_at->format('M d, Y') : 'Active' }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-xs text-slate-500">No subscription records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subscriptions->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
