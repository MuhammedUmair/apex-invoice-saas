@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-200 gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('support.index') }}" class="hover:text-red-600 transition">{{ __('Support Tickets') }}</a>
                <span>&bull;</span>
                <span class="font-mono font-bold text-slate-800">{{ $ticket->ticket_number }}</span>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">{{ $ticket->subject }}</h1>
                <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] uppercase font-bold border {{ $ticket->getStatusBadgeClass() }}">
                    {{ str_replace('_', ' ', $ticket->status) }}
                </span>
                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] uppercase font-bold border {{ $ticket->getPriorityBadgeClass() }}">
                    {{ $ticket->priority }} priority
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">
                {{ __('Created on') }} {{ $ticket->created_at->format('M d, Y h:i A') }} &bull; {{ __('Category') }}: <span class="capitalize">{{ str_replace('_', ' ', $ticket->category) }}</span>
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if(!in_array($ticket->status, ['closed', 'resolved']))
                <form action="{{ route('support.close', $ticket->id) }}" method="POST" onsubmit="return confirm('Mark this support ticket as closed?');">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-100 transition flex items-center gap-1.5">
                        <i class="ph ph-check-circle text-base text-emerald-600"></i>
                        <span>{{ __('Mark as Closed') }}</span>
                    </button>
                </form>
            @endif
            <a href="{{ route('support.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition">
                {{ __('All Tickets') }}
            </a>
        </div>
    </div>

    <!-- 24-Hour SLA Status Banner (Request 12) -->
    <div class="p-4 rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-md border border-slate-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/30 border border-indigo-400/40 text-indigo-300 flex items-center justify-center text-xl shrink-0">
                <i class="ph ph-shield-check"></i>
            </div>
            <div>
                <p class="font-bold text-xs uppercase tracking-wider text-indigo-300">24-Hour Turnaround SLA Guarantee</p>
                <p class="text-xs text-slate-300">
                    Target Resolution: <strong>{{ $ticket->sla_due_at ? $ticket->sla_due_at->format('M d, Y h:i A') : $ticket->created_at->addHours(24)->format('M d, Y h:i A') }}</strong>
                </p>
            </div>
        </div>
        <div class="sm:text-right">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $ticket->isSlaBreached() ? 'bg-rose-500/20 text-rose-300 border border-rose-400/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' }}">
                <i class="ph {{ $ticket->isSlaBreached() ? 'ph-warning-circle' : 'ph-clock' }}"></i>
                <span>{{ $ticket->sla_remaining }}</span>
            </span>
        </div>
    </div>

    <!-- Message Thread -->
    <div class="space-y-4">
        @foreach($ticket->messages as $msg)
            <div class="bg-white rounded-2xl border {{ $msg->is_admin_reply ? 'border-red-200 shadow-sm' : 'border-slate-200 shadow-xs' }} p-6 space-y-3">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full {{ $msg->is_admin_reply ? 'bg-red-600 text-white' : 'bg-slate-800 text-white' }} flex items-center justify-center text-xs font-black shadow-xs">
                            {{ $msg->is_admin_reply ? 'RM' : substr($msg->user?->name ?? 'U', 0, 1) }}
                        </div>
                        <div>
                            <p class="font-bold text-xs text-slate-900 flex items-center gap-1.5">
                                <span>{{ $msg->is_admin_reply ? 'Invoicy Engineering Support' : ($msg->user?->name ?? 'Agency User') }}</span>
                                @if($msg->is_admin_reply)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 border border-red-200 uppercase tracking-wider">Support Agent</span>
                                @endif
                            </p>
                            <p class="text-[10px] text-slate-400">{{ $msg->created_at->format('M d, Y h:i A') }} ({{ $msg->created_at->diffForHumans() }})</p>
                        </div>
                    </div>
                </div>

                <div class="text-xs sm:text-sm text-slate-800 leading-relaxed whitespace-pre-wrap">
                    {{ $msg->message }}
                </div>

                @if(!empty($msg->attachments_json))
                    <div class="pt-3 border-t border-slate-100">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">{{ __('Attached Files') }}</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($msg->attachments_json as $att)
                                <a href="{{ $att['path'] ?? '#' }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 hover:text-red-600 hover:bg-slate-100 transition">
                                    <i class="ph ph-paperclip text-sm"></i>
                                    <span class="truncate max-w-xs">{{ $att['name'] ?? 'Attachment' }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Reply Box -->
    @if($ticket->status !== 'closed')
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <i class="ph ph-chat-teardrop-text text-red-600 text-base"></i>
                <span>{{ __('Post Reply to Support') }}</span>
            </h3>

            <form action="{{ route('support.reply', $ticket->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <textarea name="message" rows="4" required placeholder="Type your response or provide additional details here..."
                              class="w-full px-4 py-3 rounded-xl border border-slate-300 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition"></textarea>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2 border-t border-slate-100">
                    <div>
                        <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf,.zip,.doc,.docx"
                               class="text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                    </div>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs shadow-sm transition flex items-center justify-center gap-2">
                        <i class="ph ph-paper-plane-tilt"></i>
                        <span>{{ __('Send Reply') }}</span>
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="p-4 rounded-xl bg-slate-100 text-slate-600 text-center text-xs font-semibold">
            {{ __('This support ticket has been closed. To inquire about a new topic, please create a new support ticket.') }}
        </div>
    @endif

</div>
@endsection
