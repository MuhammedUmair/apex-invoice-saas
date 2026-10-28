@extends('layouts.app', ['title' => 'Advertisements Management', 'header' => 'Advertisements Management'])

@section('content')
<div class="space-y-8" x-data="{ 
    showCreateModal: false, 
    editingAd: null,
    adType: 'text',
    adPlacement: 'invoice_sidebar',
    themeColor: 'brand',
    badgeText: 'SPONSORED PROMO',
    headline: 'Scale Your Business with Custom Web Apps',
    bodyText: 'Dedicated full-stack developers, custom CRM integrations, and turnkey SaaS development with RedMaple.',
    ctaText: 'Get Agency Quote',
    destUrl: 'https://redmapleagency.com',
    imageUrl: '',
    htmlSnippet: ''
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-200 gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-200">
                    <i class="ph ph-megaphone text-xl"></i>
                </span>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Advertisement Management</h1>
            </div>
            <p class="text-sm text-slate-500 mt-1">
                Upload image banners, insert custom HTML / Adsense code, or design text-based sponsored promos for invoice and sidebar placements.
            </p>
        </div>

        <button @click="showCreateModal = true; editingAd = null; adType = 'text'" 
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-xs shadow-md shadow-red-500/20 transition">
            <i class="ph ph-plus-circle text-base"></i> Create New Advertisement
        </button>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
            <i class="ph ph-check-circle text-base text-emerald-600"></i>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold space-y-1">
            @foreach ($errors->all() as $error)
                <div class="flex items-center gap-2">
                    <i class="ph ph-warning-circle text-base text-rose-600"></i>
                    {{ $error }}
                </div>
            @endforeach
        </div>
    @endif

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Ads</span>
                <span class="p-2 rounded-lg bg-indigo-50 text-indigo-600"><i class="ph ph-broadcast text-base"></i></span>
            </div>
            <p class="text-2xl font-black text-slate-900 mt-2">{{ $stats['total'] }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Active Live</span>
                <span class="p-2 rounded-lg bg-emerald-50 text-emerald-600"><i class="ph ph-check-circle text-base"></i></span>
            </div>
            <p class="text-2xl font-black text-emerald-600 mt-2">{{ $stats['active'] }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Impressions</span>
                <span class="p-2 rounded-lg bg-blue-50 text-blue-600"><i class="ph ph-eye text-base"></i></span>
            </div>
            <p class="text-2xl font-black text-blue-600 mt-2">{{ number_format($stats['impressions']) }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Ad Clicks</span>
                <span class="p-2 rounded-lg bg-amber-50 text-amber-600"><i class="ph ph-cursor-click text-base"></i></span>
            </div>
            <p class="text-2xl font-black text-amber-600 mt-2">{{ number_format($stats['clicks']) }}</p>
        </div>
    </div>

    <!-- Placement Filter Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs font-bold">
        <a href="{{ route('admin.ads.index') }}" 
           class="px-4 py-2 rounded-xl transition {{ $filterPlacement === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            All Placements
        </a>
        <a href="{{ route('admin.ads.index', ['placement' => 'invoice_sidebar']) }}" 
           class="px-4 py-2 rounded-xl transition {{ $filterPlacement === 'invoice_sidebar' ? 'bg-red-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            Invoice Screen (Right Card)
        </a>
        <a href="{{ route('admin.ads.index', ['placement' => 'app_sidebar']) }}" 
           class="px-4 py-2 rounded-xl transition {{ $filterPlacement === 'app_sidebar' ? 'bg-slate-800 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            App Sidebar (Left Menu)
        </a>
        <a href="{{ route('admin.ads.index', ['placement' => 'landing_page']) }}" 
           class="px-4 py-2 rounded-xl transition {{ $filterPlacement === 'landing_page' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            Landing Page
        </a>
    </div>

    <!-- Advertisements List -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-base">Configured Advertisements</h3>
            <span class="text-xs text-slate-500 font-semibold">{{ $ads->count() }} total advertisements</span>
        </div>

        @if($ads->isEmpty())
            <div class="p-10 text-center text-slate-400 text-sm">
                No advertisements created yet. Click "Create New Advertisement" to get started!
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($ads as $ad)
                    <div class="p-6 hover:bg-slate-50/50 transition">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                            <!-- Left: Ad Info & Details -->
                            <div class="space-y-2 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="text-base font-bold text-slate-900">{{ $ad->title }}</h4>
                                    
                                    <!-- Status Badge -->
                                    @if($ad->is_active)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Active
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">Paused</span>
                                    @endif

                                    <!-- Type Badge -->
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                        {{ $ad->type === 'image' ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}
                                        {{ $ad->type === 'html' ? 'bg-purple-50 text-purple-700 border border-purple-200' : '' }}
                                        {{ $ad->type === 'text' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}">
                                        {{ $ad->type === 'image' ? '🖼 Image Banner' : '' }}
                                        {{ $ad->type === 'html' ? '⚡ HTML Embed' : '' }}
                                        {{ $ad->type === 'text' ? '📝 Text Promo' : '' }}
                                    </span>

                                    <!-- Placement Badge -->
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        <i class="ph ph-map-pin"></i> 
                                        {{ match($ad->placement) {
                                            'invoice_sidebar' => 'Invoice Screen (Right)',
                                            'app_sidebar' => 'Left App Sidebar',
                                            'landing_page' => 'Landing Page',
                                            'client_portal' => 'Client Invoice Portal',
                                            default => 'All Placements'
                                        } }}
                                    </span>
                                </div>

                                @if($ad->type === 'text')
                                    <p class="text-xs text-slate-700 font-semibold">{{ $ad->headline }}</p>
                                    <p class="text-xs text-slate-500 line-clamp-2">{{ $ad->body_text }}</p>
                                @elseif($ad->type === 'image')
                                    <div class="flex items-center gap-2 text-xs text-slate-500">
                                        <i class="ph ph-image text-sm"></i> Image: <span class="font-mono text-slate-700 truncate max-w-sm">{{ $ad->image_path }}</span>
                                    </div>
                                @elseif($ad->type === 'html')
                                    <div class="text-[11px] font-mono text-slate-600 bg-slate-100 p-2 rounded-lg max-h-16 overflow-hidden">
                                        {{ Str::limit($ad->html_code, 120) }}
                                    </div>
                                @endif

                                @if($ad->destination_url)
                                    <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                        <i class="ph ph-link"></i> Click Destination: 
                                        <a href="{{ $ad->destination_url }}" target="_blank" class="text-blue-600 hover:underline font-mono truncate max-w-md">
                                            {{ $ad->destination_url }}
                                        </a>
                                    </div>
                                @endif
                            </div>

                            <!-- Right: Preview Thumbnail & Actions -->
                            <div class="flex items-center gap-4 shrink-0">
                                <!-- Compact Visual Preview -->
                                <div class="w-48 bg-slate-900 rounded-xl p-2.5 text-white text-[10px] shadow-2xs border border-slate-800 hidden sm:block">
                                    @if($ad->type === 'image' && $ad->image_path)
                                        <img src="{{ $ad->image_path }}" alt="Preview" class="w-full h-16 object-cover rounded-lg">
                                    @elseif($ad->type === 'text')
                                        <span class="text-[8px] font-black uppercase tracking-wider text-red-400 bg-red-600/20 px-1 py-0.5 rounded">{{ $ad->badge_text }}</span>
                                        <h5 class="font-bold text-white text-[11px] truncate mt-1">{{ $ad->headline }}</h5>
                                        <p class="text-[9px] text-slate-400 truncate mt-0.5">{{ $ad->body_text }}</p>
                                    @else
                                        <span class="text-[9px] font-mono text-purple-300">Custom HTML Ad</span>
                                    @endif
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center gap-2">
                                    <form method="POST" action="{{ route('admin.ads.toggle', $ad->id) }}">
                                        @csrf
                                        <button type="submit" 
                                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1
                                                {{ $ad->is_active ? 'bg-slate-100 hover:bg-slate-200 text-slate-700' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200' }}">
                                            <i class="ph {{ $ad->is_active ? 'ph-pause' : 'ph-play' }}"></i>
                                            {{ $ad->is_active ? 'Pause' : 'Activate' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.ads.destroy', $ad->id) }}" onsubmit="return confirm('Delete advertisement {{ addslashes($ad->title) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition" title="Delete Advertisement">
                                            <i class="ph ph-trash text-base"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- CREATE / EDIT ADVERTISEMENT MODAL -->
    <div x-show="showCreateModal" x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showCreateModal = false" class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl border border-slate-200 overflow-hidden my-8">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-megaphone"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Create New Advertisement</h3>
                        <p class="text-xs text-slate-500">Configure ad creative, placement, and destination link</p>
                    </div>
                </div>
                <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.ads.store') }}" enctype="multipart/form-data" class="p-6 space-y-5">
                @csrf

                <!-- Basic Meta -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Ad Internal Title *</label>
                        <input type="text" name="title" required placeholder="e.g. Summer Agency Retention Offer"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Target Placement *</label>
                        <select name="placement" x-model="adPlacement" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs bg-white font-semibold">
                            <option value="invoice_sidebar">Invoice Screen (Right Card)</option>
                            <option value="app_sidebar">Left App Sidebar (Below Menus)</option>
                            <option value="landing_page">Landing Page</option>
                            <option value="client_portal">Client Invoice Portal</option>
                            <option value="all">All Placements</option>
                        </select>
                    </div>
                </div>

                <!-- Ad Type Selector Tabs -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Ad Creative Format *</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" @click="adType = 'text'" 
                                :class="adType === 'text' ? 'bg-red-50 border-red-500 text-red-700 font-bold' : 'bg-slate-50 border-slate-200 text-slate-600'"
                                class="p-3 rounded-xl border text-xs flex flex-col items-center gap-1 transition">
                            <i class="ph ph-text-t text-lg"></i>
                            <span>Text Promo Ad</span>
                        </button>

                        <button type="button" @click="adType = 'image'" 
                                :class="adType === 'image' ? 'bg-red-50 border-red-500 text-red-700 font-bold' : 'bg-slate-50 border-slate-200 text-slate-600'"
                                class="p-3 rounded-xl border text-xs flex flex-col items-center gap-1 transition">
                            <i class="ph ph-image text-lg"></i>
                            <span>Image Banner Upload</span>
                        </button>

                        <button type="button" @click="adType = 'html'" 
                                :class="adType === 'html' ? 'bg-red-50 border-red-500 text-red-700 font-bold' : 'bg-slate-50 border-slate-200 text-slate-600'"
                                class="p-3 rounded-xl border text-xs flex flex-col items-center gap-1 transition">
                            <i class="ph ph-code text-lg"></i>
                            <span>Custom HTML / Adsense</span>
                        </button>
                    </div>
                    <input type="hidden" name="type" :value="adType">
                </div>

                <!-- Dynamic Type Fields -->
                <!-- 1. Text Promo Fields -->
                <div x-show="adType === 'text'" class="space-y-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Badge Tag</label>
                            <input type="text" name="badge_text" x-model="badgeText" placeholder="SPONSORED PROMO"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Theme Gradient</label>
                            <select name="theme_color" x-model="themeColor" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white">
                                <option value="brand">Brand (RedMaple Gradient)</option>
                                <option value="slate">Dark Slate</option>
                                <option value="purple">Purple Indigo</option>
                                <option value="emerald">Emerald Forest</option>
                                <option value="blue">Ocean Blue</option>
                                <option value="amber">Warm Amber</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Ad Headline *</label>
                        <input type="text" name="headline" x-model="headline" placeholder="Headline capturing user interest"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-bold bg-white">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Body Description *</label>
                        <textarea name="body_text" x-model="bodyText" rows="2" placeholder="Brief value proposition or announcement..."
                                  class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">CTA Button Text</label>
                        <input type="text" name="cta_text" x-model="ctaText" placeholder="Get Agency Quote"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-bold bg-white">
                    </div>
                </div>

                <!-- 2. Image Banner Fields -->
                <div x-show="adType === 'image'" class="space-y-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Upload Banner Image File</label>
                        <input type="file" name="image_file" accept="image/png,image/jpeg,image/webp,image/svg+xml"
                               class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                        <p class="text-[10px] text-slate-400 mt-1">Recommended size: 400x200 or 600x300. Max 2MB.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Or Direct Image URL</label>
                        <input type="url" name="image_path" x-model="imageUrl" placeholder="https://domain.com/banner.png"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Image Alt Text</label>
                        <input type="text" name="alt_text" placeholder="Banner description"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white">
                    </div>
                </div>

                <!-- 3. HTML Embed Fields -->
                <div x-show="adType === 'html'" class="space-y-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Raw HTML / Adsense Embed Code</label>
                        <textarea name="html_code" x-model="htmlSnippet" rows="5" placeholder="<!-- Insert your Google Adsense or HTML ad script here -->"
                                  class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-mono bg-white"></textarea>
                    </div>
                </div>

                <!-- Destination Link -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Click-Through Destination URL</label>
                    <input type="url" name="destination_url" x-model="destUrl" placeholder="https://redmapleagency.com"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-mono">
                </div>

                <div class="flex items-center justify-between pt-2">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                        <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 text-emerald-600 rounded">
                        <span>Activate Advertisement Immediately</span>
                    </label>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-xs shadow-md transition">
                            Save Advertisement
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
