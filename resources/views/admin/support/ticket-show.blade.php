@extends('layouts.admin', ['title' => 'Ticket #' . $ticket->ticket_number, 'header' => 'Support Ticket Thread'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <div class="flex items-center gap-3">
                <span class="font-mono text-xs text-amber-400 font-bold">{{ $ticket->ticket_number }}</span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $ticket->getStatusBadgeClass() }}">
                    {{ str_replace('_', ' ', $ticket->status) }}
                </span>
            </div>
            <h1 class="text-xl font-black text-white mt-1">{{ $ticket->subject }}</h1>
        </div>
        <a href="{{ route('admin.support.tickets') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
            <i class="ph ph-arrow-left"></i> All Tickets
        </a>
    </div>

    <!-- Ticket Thread -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            <div class="space-y-4">
                @forelse($ticket->messages as $msg)
                    <div class="p-5 rounded-2xl border shadow-sm {{ $msg->is_admin_reply ? 'bg-slate-900 border-amber-500/30' : 'bg-slate-950/80 border-slate-800' }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-xs {{ $msg->is_admin_reply ? 'text-amber-400' : 'text-slate-200' }}">
                                {{ $msg->user?->name ?? ($msg->is_admin_reply ? 'Support Specialist' : 'Customer') }}
                            </span>
                            <span class="text-[10px] text-slate-500 font-mono">{{ $msg->created_at->format('M d, H:i') }}</span>
                        </div>
                        @php
                            $displayMessage = $msg->message;
                            if (str_contains($displayMessage, "\n\nContext: {")) {
                                $displayMessage = explode("\n\nContext: {", $displayMessage)[0];
                            } elseif (str_contains($displayMessage, "Context: {")) {
                                $displayMessage = explode("Context: {", $displayMessage)[0];
                            }
                        @endphp
                        <div class="text-xs text-slate-300 leading-relaxed whitespace-pre-wrap">{{ trim($displayMessage) }}</div>
                    </div>
                @empty
                    <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-400">
                        No messages recorded yet.
                    </div>
                @endforelse
            </div>

            <!-- Reply Form -->
            <form action="{{ route('admin.support.tickets.reply', $ticket->id) }}" method="POST" class="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-4 shadow-sm">
                @csrf
                <h3 class="font-bold text-white text-sm">Post Operator Response</h3>
                <textarea name="message" required rows="4" placeholder="Draft your response to the customer..."
                          class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-200 focus:ring-2 focus:ring-amber-500 outline-none"></textarea>
                
                <div class="flex items-center justify-between">
                    <select name="status" class="bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2">
                        <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>Set: In Progress</option>
                        <option value="waiting_on_customer" {{ $ticket->status === 'waiting_on_customer' ? 'selected' : '' }}>Set: Waiting on Customer</option>
                        <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Set: Resolved</option>
                        <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Set: Closed</option>
                    </select>

                    <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl text-xs transition">
                        Dispatch Reply
                    </button>
                </div>
            </form>
        </div>

        <!-- Ticket Metadata Sidebar -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-4 h-fit">
            <h3 class="font-bold text-white text-sm">Ticket Properties</h3>
            <div class="space-y-3 text-xs">
                <div class="flex justify-between border-b border-slate-800/80 pb-2">
                    <span class="text-slate-400">Workspace:</span>
                    <strong class="text-white">{{ $ticket->workspace?->name ?? 'External' }}</strong>
                </div>
                <div class="flex justify-between border-b border-slate-800/80 pb-2">
                    <span class="text-slate-400">Customer:</span>
                    <strong class="text-white">{{ $ticket->user?->email ?? 'N/A' }}</strong>
                </div>
                <div class="flex justify-between border-b border-slate-800/80 pb-2">
                    <span class="text-slate-400">Priority:</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $ticket->getPriorityBadgeClass() }}">
                        {{ $ticket->priority }}
                    </span>
                </div>
                <div class="flex justify-between border-b border-slate-800/80 pb-2">
                    <span class="text-slate-400">Category:</span>
                    <strong class="text-slate-200 uppercase font-mono">{{ $ticket->category }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
