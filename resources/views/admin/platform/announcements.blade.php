@extends('layouts.admin', ['title' => 'System Announcements', 'header' => 'Platform Announcements Manager'])

@section('content')
<div class="space-y-6" x-data="{ createModal: false }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white flex items-center gap-2">
                <i class="ph ph-megaphone text-amber-400"></i> Platform Announcements
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Broadcast platform updates, scheduled maintenance, or system alerts to tenants.</p>
        </div>

        <button @click="createModal = true" class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl text-xs transition flex items-center gap-1.5 shadow-sm">
            <i class="ph ph-plus font-bold"></i> New Announcement
        </button>
    </div>

    <!-- Announcements List -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-[10px] uppercase font-bold tracking-wider bg-slate-950/40">
                        <th class="py-3 px-4">Title & Message</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Audience</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Created</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($announcements as $ann)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3 px-4">
                                <div class="font-bold text-white">{{ $ann->title }}</div>
                                <div class="text-[11px] text-slate-400 max-w-md truncate">{{ $ann->message }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $ann->getTypeBadgeClass() }}">
                                    {{ $ann->type }}
                                </span>
                            </td>
                            <td class="py-3 px-4 uppercase text-[10px] font-mono text-slate-400">{{ $ann->target_role }}</td>
                            <td class="py-3 px-4">
                                @if($ann->is_active)
                                    <span class="inline-flex items-center gap-1 text-[11px] text-emerald-400 font-bold">
                                        <i class="ph ph-broadcast"></i> Live
                                    </span>
                                @else
                                    <span class="text-slate-500 text-[11px]">Inactive</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-400 text-[11px]">{{ $ann->created_at->format('M d, Y') }}</td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <form action="{{ route('admin.platform.announcements.toggle', $ann->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-[11px] font-bold transition">
                                            {{ $ann->is_active ? 'Disable' : 'Enable' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.platform.announcements.delete', $ann->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete announcement?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 rounded-lg text-rose-400 hover:bg-rose-500/10 transition">
                                            <i class="ph ph-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-xs text-slate-500">No announcements published yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Modal -->
    <div x-show="createModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 z-50" x-cloak>
        <div class="w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-800 p-6 space-y-4 shadow-2xl" @click.away="createModal = false">
            <h3 class="font-bold text-white text-base">Create Platform Announcement</h3>
            <form action="{{ route('admin.platform.announcements.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Headline Title</label>
                    <input type="text" name="title" required placeholder="e.g. Scheduled Maintenance Notice"
                           class="w-full px-3.5 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-200 focus:ring-2 focus:ring-amber-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Announcement Body</label>
                    <textarea name="message" required rows="3" placeholder="Enter notification message..."
                              class="w-full px-3.5 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-200 focus:ring-2 focus:ring-amber-500 outline-none"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Banner Type</label>
                        <select name="type" class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-200">
                            <option value="info">Info (Blue)</option>
                            <option value="warning">Warning (Amber)</option>
                            <option value="danger">Urgent / Alert (Red)</option>
                            <option value="success">Success (Green)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Target Audience</label>
                        <select name="target_role" class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-200">
                            <option value="all">All Users</option>
                            <option value="owners">Agency Owners Only</option>
                            <option value="admins">Admins Only</option>
                            <option value="staff">Staff Members Only</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-xs font-bold text-slate-950">Publish Announcement</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
