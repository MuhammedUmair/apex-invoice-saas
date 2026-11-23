@extends('layouts.admin', ['title' => 'AI Conversation Transcript', 'header' => 'AI Conversation Details'])

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.support.ai_conversations') }}" class="p-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white transition">
                <i class="ph ph-arrow-left text-lg"></i>
            </a>
            <div>
                <h1 class="text-xl font-black text-white flex items-center gap-2">
                    <span>Thread #{{ $conversation->id }}: {{ $conversation->title }}</span>
                </h1>
                <p class="text-xs text-slate-400 mt-0.5">Session: <span class="font-mono">{{ $conversation->session_id }}</span> &bull; {{ $conversation->created_at->format('M d, Y H:i:s') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if($conversation->rating === 1)
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">👍 Helpful</span>
            @elseif($conversation->rating === -1)
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">👎 Unhelpful</span>
            @endif

            @if($conversation->escalatedTicket)
                <a href="{{ route('admin.support.tickets.show', $conversation->escalatedTicket->id) }}" class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 hover:bg-amber-500/20 transition flex items-center gap-1">
                    <i class="ph ph-ticket"></i> Escalated: {{ $conversation->escalatedTicket->ticket_number }}
                </a>
            @endif
        </div>
    </div>

    <!-- Metadata Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <div class="text-[10px] uppercase font-bold text-slate-500">Workspace</div>
            <div class="text-sm font-bold text-white mt-1">{{ $conversation->workspace?->name ?? 'External / Guest' }}</div>
            <div class="text-xs text-slate-400">{{ $conversation->user?->email ?? 'No user attached' }}</div>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <div class="text-[10px] uppercase font-bold text-slate-500">Channel & Language</div>
            <div class="text-sm font-bold text-white mt-1 capitalize">{{ $conversation->channel }} Mode</div>
            <div class="text-xs text-slate-400">Language: <span class="font-mono font-bold uppercase text-indigo-400">{{ $conversation->language ?? 'en' }}</span></div>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <div class="text-[10px] uppercase font-bold text-slate-500">Voice Telemetry</div>
            <div class="text-sm font-bold text-white mt-1">{{ gmdate("i:s", $conversation->duration_seconds) }} minutes</div>
            <div class="text-xs text-slate-400">{{ $conversation->voiceSessions->count() }} voice session(s)</div>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <div class="text-[10px] uppercase font-bold text-slate-500">Resolution Status</div>
            <div class="text-sm font-bold {{ $conversation->resolved ? 'text-emerald-400' : 'text-slate-300' }} mt-1">
                {{ $conversation->resolved ? 'Resolved' : 'Open / Unmarked' }}
            </div>
            @if($conversation->feedback)
                <div class="text-xs text-amber-300 mt-1 italic">"{{ $conversation->feedback }}"</div>
            @endif
        </div>
    </div>

    <!-- Full Transcript Thread -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 space-y-4">
        <h3 class="text-sm font-black uppercase text-slate-400 tracking-wider flex items-center gap-2 border-b border-slate-800 pb-3">
            <i class="ph ph-chat-circle-dots text-indigo-400"></i> Conversation Messages Transcript ({{ $conversation->messages->count() }})
        </h3>

        <div class="space-y-4">
            @forelse($conversation->messages as $msg)
                <div class="flex gap-3 {{ $msg->role === 'assistant' ? 'bg-slate-950/50 p-4 rounded-2xl border border-slate-800' : 'p-2' }}">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 text-sm font-bold {{ $msg->role === 'assistant' ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-300' }}">
                        @if($msg->role === 'assistant')
                            <i class="ph ph-robot"></i>
                        @else
                            <i class="ph ph-user"></i>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-bold text-white capitalize">{{ $msg->role === 'assistant' ? 'Invoicy AI Copilot' : ($conversation->user?->name ?? 'User') }}</span>
                            <span class="text-[10px] text-slate-500 font-mono">{{ $msg->created_at->format('H:i:s') }}</span>
                            @if($msg->mode === 'voice')
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-500/20 text-emerald-300 flex items-center gap-1">
                                    <i class="ph ph-microphone"></i> Voice
                                </span>
                            @endif
                            @if($msg->interrupted)
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-500/20 text-amber-300 flex items-center gap-1">
                                    <i class="ph ph-hand-palm"></i> Interrupted
                                </span>
                            @endif
                        </div>

                        <div class="text-xs text-slate-200 whitespace-pre-wrap leading-relaxed">
                            {{ $msg->content }}
                        </div>

                        @if(!empty($msg->tool_calls))
                            <div class="mt-2 p-2 rounded-lg bg-indigo-950/30 border border-indigo-900/50 text-[11px] text-indigo-300 font-mono">
                                <strong>Tool Action:</strong> {{ json_encode($msg->tool_calls) }}
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-xs text-slate-500">
                    No messages logged in this conversation.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Voice Sessions Telemetry Log if available -->
    @if($conversation->voiceSessions->isNotEmpty())
        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 space-y-4">
            <h3 class="text-sm font-black uppercase text-slate-400 tracking-wider flex items-center gap-2 border-b border-slate-800 pb-3">
                <i class="ph ph-waveform text-emerald-400"></i> Ephemeral Voice Sessions
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-500 text-[10px] uppercase font-bold">
                            <th class="py-2 px-3">Session Token</th>
                            <th class="py-2 px-3">Provider</th>
                            <th class="py-2 px-3">Status</th>
                            <th class="py-2 px-3">Duration</th>
                            <th class="py-2 px-3">Started At</th>
                            <th class="py-2 px-3">Ended At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        @foreach($conversation->voiceSessions as $vs)
                            <tr>
                                <td class="py-2 px-3 font-mono text-slate-400 text-[11px]">{{ Str::limit($vs->token, 20) }}</td>
                                <td class="py-2 px-3 text-[11px] text-slate-300">{{ $vs->provider }}</td>
                                <td class="py-2 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $vs->status === 'active' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-800 text-slate-400' }}">
                                        {{ $vs->status }}
                                    </span>
                                </td>
                                <td class="py-2 px-3 font-mono text-[11px]">{{ $vs->duration_seconds }}s</td>
                                <td class="py-2 px-3 text-[11px] text-slate-400">{{ $vs->started_at?->format('H:i:s') ?? '-' }}</td>
                                <td class="py-2 px-3 text-[11px] text-slate-400">{{ $vs->ended_at?->format('H:i:s') ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
