@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- 24-Hour SLA Guarantee Hero Banner (Request 12) -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-red-950 p-6 sm:p-8 text-white shadow-xl border border-slate-700/60">
        <div class="absolute -right-12 -top-12 w-64 h-64 bg-red-600/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-bold tracking-wide">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>24-Hour Turnaround SLA Guarantee Active</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                    Agency Consumer Support Desk
                </h1>
                <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                    Direct access to our dedicated engineering and billing support team. Every support ticket is tracked under our <strong>24-hour turnaround SLA guarantee</strong>. All system upgrades, enhancements, and migrations are safeguarded with strict <strong>zero-data-loss architecture</strong>.
                </p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 shrink-0">
                <a href="{{ route('support.create') }}" class="px-5 py-3 rounded-xl bg-gradient-to-r from-red-600 to-rose-700 hover:from-red-700 hover:to-rose-800 text-white font-bold text-xs sm:text-sm shadow-lg hover:shadow-red-900/30 transition flex items-center justify-center gap-2">
                    <i class="ph ph-plus-circle text-base"></i>
                    <span>{{ __('Create New Ticket') }}</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Stats & SLA Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500">{{ __('Open / In Progress') }}</p>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ $openCount }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl">
                <i class="ph ph-clock-countdown"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500">{{ __('Resolved Tickets') }}</p>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ $resolvedCount }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl">
                <i class="ph ph-seal-check"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500">{{ __('Turnaround SLA') }}</p>
                <h3 class="text-2xl font-black text-indigo-600 mt-1">&lt; 24h</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl">
                <i class="ph ph-shield-check"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500">{{ __('Data Protection') }}</p>
                <h3 class="text-2xl font-black text-rose-600 mt-1">100%</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-2xl" title="Zero Data Loss Safe Migrations">
                <i class="ph ph-database"></i>
            </div>
        </div>
    </div>

    <!-- Tickets List Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- Filter Tabs -->
        <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-slate-50/50">
            <div class="flex items-center gap-1.5 flex-wrap">
                <a href="{{ route('support.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $status === 'all' ? 'bg-red-600 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-200' }}">
                    {{ __('All') }} ({{ $totalCount }})
                </a>
                <a href="{{ route('support.index', ['status' => 'open']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $status === 'open' ? 'bg-red-600 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-200' }}">
                    {{ __('Open') }}
                </a>
                <a href="{{ route('support.index', ['status' => 'in_progress']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $status === 'in_progress' ? 'bg-red-600 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-200' }}">
                    {{ __('In Progress') }}
                </a>
                <a href="{{ route('support.index', ['status' => 'resolved']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $status === 'resolved' ? 'bg-red-600 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-200' }}">
                    {{ __('Resolved') }}
                </a>
                <a href="{{ route('support.index', ['status' => 'closed']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $status === 'closed' ? 'bg-red-600 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-200' }}">
                    {{ __('Closed') }}
                </a>
            </div>

            <a href="{{ route('support.create') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-red-600 hover:text-red-700">
                <i class="ph ph-plus-circle text-sm"></i>
                <span>{{ __('Submit New Ticket') }}</span>
            </a>
        </div>

        @if($tickets->isEmpty())
            <div class="p-12 text-center space-y-3">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-3xl mx-auto">
                    <i class="ph ph-ticket"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-base">{{ __('No Support Tickets Found') }}</h3>
                <p class="text-slate-500 text-xs max-w-sm mx-auto">
                    {{ __('Have a technical question, billing inquiry, or need assistance? Open a ticket and our engineering team will respond within 24 hours.') }}
                </p>
                <div class="pt-2">
                    <a href="{{ route('support.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                        <i class="ph ph-plus"></i> {{ __('Open First Support Ticket') }}
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3.5">{{ __('Ticket #') }}</th>
                            <th class="px-5 py-3.5">{{ __('Subject') }}</th>
                            <th class="px-5 py-3.5">{{ __('Category') }}</th>
                            <th class="px-5 py-3.5 text-center">{{ __('Priority') }}</th>
                            <th class="px-5 py-3.5 text-center">{{ __('Status') }}</th>
                            <th class="px-5 py-3.5 text-center">{{ __('SLA Guarantee') }}</th>
                            <th class="px-5 py-3.5 text-right">{{ __('Last Updated') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($tickets as $ticket)
                            <tr class="hover:bg-slate-50/80 transition group">
                                <td class="px-5 py-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                    <a href="{{ route('support.show', $ticket->id) }}" class="text-red-600 hover:text-red-700 hover:underline">
                                        {{ $ticket->ticket_number }}
                                    </a>
                                </td>
                                <td class="px-5 py-4 font-semibold text-slate-900 max-w-xs truncate">
                                    <a href="{{ route('support.show', $ticket->id) }}" class="hover:text-red-600">
                                        {{ $ticket->subject }}
                                    </a>
                                    <span class="block text-[11px] font-normal text-slate-400 mt-0.5">
                                        {{ $ticket->messages->count() }} message(s)
                                    </span>
                                </td>
                                <td class="px-5 py-4 capitalize text-slate-600">
                                    {{ str_replace('_', ' ', $ticket->category) }}
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] uppercase font-bold border {{ $ticket->getPriorityBadgeClass() }}">
                                        {{ $ticket->priority }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] uppercase font-bold border {{ $ticket->getStatusBadgeClass() }}">
                                        {{ str_replace('_', ' ', $ticket->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    @if(in_array($ticket->status, ['resolved', 'closed']))
                                        <span class="inline-flex items-center gap-1 text-emerald-700 font-bold text-[11px]">
                                            <i class="ph ph-check-circle"></i> Resolved
                                        </span>
                                    @elseif($ticket->isSlaBreached())
                                        <span class="inline-flex items-center gap-1 text-rose-600 font-bold text-[11px]" title="SLA breached">
                                            <i class="ph ph-warning-circle"></i> SLA Overdue
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-indigo-700 font-bold text-[11px]" title="Guaranteed response time">
                                            <i class="ph ph-hourglass-simple"></i> {{ $ticket->sla_remaining }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right text-slate-400 whitespace-nowrap font-medium">
                                    {{ $ticket->updated_at->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($tickets->hasPages())
                <div class="p-4 border-t border-slate-200">
                    {{ $tickets->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
