@extends('layouts.admin', ['title' => 'AI Conversations & Voice Usage', 'header' => 'AI Agent Intelligence & Voice Usage'])

@section('content')
<div class="space-y-6">
    <!-- Header Summary & KPI Cards -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white flex items-center gap-2">
                <i class="ph ph-microphone text-indigo-400"></i> AI Conversations & Voice Telemetry
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Real-time transcripts, duplex voice calls, resolution ratings, and tenant interactions.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Voice Duplex Active
            </span>
        </div>
    </div>

    <!-- Analytics Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Conversations</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                    <i class="ph ph-chats-circle text-lg"></i>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-white">{{ number_format($totalConversations) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">{{ number_format($totalMessages) }} total messages exchanged</div>
        </div>

        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Voice Telemetry</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                    <i class="ph ph-waveform text-lg"></i>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-white">{{ number_format($totalVoiceMinutes, 1) }} <span class="text-xs font-normal text-slate-400">min</span></div>
            <div class="text-[11px] text-slate-400 mt-1">Two-way real-time duplex voice</div>
        </div>

        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Satisfaction Rate</span>
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-400">
                    <i class="ph ph-thumbs-up text-lg"></i>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-white">{{ $satisfactionRate }}%</div>
            <div class="text-[11px] text-slate-400 mt-1">{{ $helpfulCount }} helpful / {{ $unhelpfulCount }} unhelpful</div>
        </div>

        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Supported Languages</span>
                <div class="w-8 h-8 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-400">
                    <i class="ph ph-translate text-lg"></i>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-white">10+ <span class="text-xs font-normal text-slate-400">Languages</span></div>
            <div class="text-[11px] text-slate-400 mt-1">Auto-detected multilingual NLP</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 p-4">
        <form method="GET" action="{{ route('admin.support.ai_conversations') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title, session, or user..." 
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">
            </div>

            <div>
                <select name="channel" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                    <option value="">All Channels</option>
                    <option value="text" {{ request('channel') === 'text' ? 'selected' : '' }}>Text Only</option>
                    <option value="voice" {{ request('channel') === 'voice' ? 'selected' : '' }}>Voice Only</option>
                    <option value="hybrid" {{ request('channel') === 'hybrid' ? 'selected' : '' }}>Hybrid (Text + Voice)</option>
                </select>
            </div>

            <div>
                <select name="rating" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                    <option value="">All Feedback</option>
                    <option value="1" {{ request('rating') === '1' ? 'selected' : '' }}>Helpful 👍</option>
                    <option value="-1" {{ request('rating') === '-1' ? 'selected' : '' }}>Unhelpful 👎</option>
                </select>
            </div>

            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl transition">
                Filter
            </button>
            @if(request()->anyFilled(['search', 'channel', 'rating']))
                <a href="{{ route('admin.support.ai_conversations') }}" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-xl transition">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Conversations Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-[10px] uppercase font-bold tracking-wider bg-slate-950/40">
                        <th class="py-3 px-4">Thread ID & Title</th>
                        <th class="py-3 px-4">Workspace / User</th>
                        <th class="py-3 px-4">Channel & Mode</th>
                        <th class="py-3 px-4">Language</th>
                        <th class="py-3 px-4">Duration</th>
                        <th class="py-3 px-4">Rating</th>
                        <th class="py-3 px-4">Messages</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($conversations as $conv)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3 px-4">
                                <div class="font-bold text-white flex items-center gap-1.5">
                                    @if($conv->channel === 'voice' || $conv->channel === 'hybrid')
                                        <i class="ph ph-waveform text-emerald-400 text-sm"></i>
                                    @else
                                        <i class="ph ph-chat-teardrop-text text-indigo-400 text-sm"></i>
                                    @endif
                                    {{ Str::limit($conv->title, 32) }}
                                </div>
                                <div class="text-[10px] text-slate-500 font-mono mt-0.5">#{{ $conv->id }} &bull; {{ Str::limit($conv->session_id, 16) }}</div>
                            </td>

                            <td class="py-3 px-4">
                                <div class="font-bold text-white">{{ $conv->workspace?->name ?? 'System' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $conv->user?->email ?? 'Guest Session' }}</div>
                            </td>

                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($conv->channel === 'voice')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <i class="ph ph-microphone"></i> Voice Duplex
                                    </span>
                                @elseif($conv->channel === 'hybrid')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/10 text-purple-400 border border-purple-500/20">
                                        <i class="ph ph-arrows-split"></i> Hybrid
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                        <i class="ph ph-chat-centered-dots"></i> Text Chat
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-4 uppercase font-mono text-[10px] text-slate-400">
                                <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 font-bold">{{ $conv->language ?? 'en' }}</span>
                            </td>

                            <td class="py-3 px-4 text-slate-400 text-xs whitespace-nowrap">
                                @if($conv->duration_seconds > 0)
                                    {{ gmdate("i:s", $conv->duration_seconds) }} min
                                @else
                                    <span class="text-slate-600">-</span>
                                @endif
                            </td>

                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($conv->rating === 1)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        👍 Helpful
                                    </span>
                                @elseif($conv->rating === -1)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        👎 Unhelpful
                                    </span>
                                @else
                                    <span class="text-slate-600 text-[10px]">Unrated</span>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-slate-300 font-bold">
                                {{ $conv->messages->count() }}
                            </td>

                            <td class="py-3 px-4 text-slate-400 text-[11px] whitespace-nowrap">
                                {{ $conv->created_at->format('M d, H:i') }}
                            </td>

                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <a href="{{ route('admin.support.ai_conversations.show', $conv->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-indigo-400 hover:text-indigo-300 font-bold text-xs transition">
                                    <i class="ph ph-eye"></i> View Thread
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-xs text-slate-500">
                                No AI conversations recorded yet. Conversations will populate here automatically as users interact with the AI assistant.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($conversations->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950/20">
                {{ $conversations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
