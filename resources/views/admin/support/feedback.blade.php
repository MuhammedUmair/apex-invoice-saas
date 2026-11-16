@extends('layouts.admin', ['title' => 'User Feedback', 'header' => 'Customer Feedback & Ratings'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white flex items-center gap-2">
                <i class="ph ph-chat-centered-text text-amber-400"></i> Customer Reviews & Feedback
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Direct tenant sentiment, satisfaction ratings, and feature requests.</p>
        </div>
    </div>

    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-[10px] uppercase font-bold tracking-wider bg-slate-950/40">
                        <th class="py-3 px-4">Rating</th>
                        <th class="py-3 px-4">Workspace / User</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Comment</th>
                        <th class="py-3 px-4">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($feedback as $fb)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="flex items-center text-amber-400 text-sm">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="ph {{ $i <= $fb->rating ? 'ph-star-fill' : 'ph-star' }}"></i>
                                    @endfor
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-white">{{ $fb->workspace?->name ?? 'External' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $fb->user?->email ?? 'Anonymous' }}</div>
                            </td>
                            <td class="py-3 px-4 uppercase text-[10px] font-mono text-slate-400">{{ $fb->category }}</td>
                            <td class="py-3 px-4 text-slate-200 text-xs max-w-md leading-relaxed">{{ $fb->comment }}</td>
                            <td class="py-3 px-4 text-slate-400 text-[11px] whitespace-nowrap">{{ $fb->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-xs text-slate-500">No customer feedback submitted yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($feedback->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $feedback->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
