@extends('layouts.admin', ['title' => 'Website Builder', 'header' => 'Website & Page Builder'])

@section('content')
<div class="space-y-8" x-data="{ currentTab: 'branding', editingPage: null, newNavLabel: '', newNavUrl: '', newNavTarget: '_self' }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-800 gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20">
                    <i class="ph ph-paint-brush-broad text-xl"></i>
                </span>
                <h1 class="text-xl font-black text-white tracking-tight">Website & Page Builder</h1>
            </div>
            <p class="text-xs text-slate-400 mt-1">
                Customize your website main logo, login screen logo, navigation menus, landing page copy, and custom website pages.
            </p>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center bg-slate-900 border border-slate-800 p-1 rounded-xl text-xs font-bold overflow-x-auto">
            <button @click="currentTab = 'branding'" 
                    :class="currentTab === 'branding' ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-400 hover:text-white'"
                    class="px-3.5 py-2 rounded-lg transition flex items-center gap-1.5 shrink-0">
                <i class="ph ph-image text-base"></i> Logos & Branding
            </button>
            <button @click="currentTab = 'navigation'" 
                    :class="currentTab === 'navigation' ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-400 hover:text-white'"
                    class="px-3.5 py-2 rounded-lg transition flex items-center gap-1.5 shrink-0">
                <i class="ph ph-list-dashes text-base"></i> Navigation Menu
            </button>
            <button @click="currentTab = 'sections'" 
                    :class="currentTab === 'sections' ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-400 hover:text-white'"
                    class="px-3.5 py-2 rounded-lg transition flex items-center gap-1.5 shrink-0">
                <i class="ph ph-layout text-base"></i> Landing Sections
            </button>
            <button @click="currentTab = 'pages'" 
                    :class="currentTab === 'pages' ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-400 hover:text-white'"
                    class="px-3.5 py-2 rounded-lg transition flex items-center gap-1.5 shrink-0">
                <i class="ph ph-files text-base"></i> Custom Pages
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-950/40 border border-emerald-500/30 text-emerald-300 text-xs font-bold flex items-center gap-2">
            <i class="ph ph-check-circle text-base text-emerald-400"></i>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-950/40 border border-rose-500/30 text-rose-300 text-xs font-bold space-y-1">
            @foreach ($errors->all() as $error)
                <div class="flex items-center gap-2">
                    <i class="ph ph-warning-circle text-base text-rose-400"></i>
                    {{ $error }}
                </div>
            @endforeach
        </div>
    @endif

    <!-- TAB 1: LOGOS & BRANDING CUSTOMIZER -->
    <div x-show="currentTab === 'branding'" class="space-y-6">
        <form method="POST" action="{{ route('builder.branding') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            @php
                $systemLogo = \App\Models\CmsContent::getSystemLogo();
                $faviconUrl = \App\Models\CmsContent::getFaviconUrl();
            @endphp

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Master System Brand Logo Card (Single Upload Everywhere) -->
                <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6 shadow-sm space-y-5 lg:col-span-2">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-800 gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center justify-center text-xl shrink-0">
                                <i class="ph ph-image"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-white text-base">System & Website Brand Logo</h3>
                                <p class="text-xs text-slate-400">Single upload automatically changes your brand logo everywhere across web and app</p>
                            </div>
                        </div>
                        @if(!empty($systemLogo))
                            <button type="submit" name="remove_logo" value="1" 
                                    class="text-xs text-rose-400 hover:text-rose-300 font-bold flex items-center gap-1 shrink-0"
                                    onclick="return confirm('Remove custom logo and reset to default everywhere?')">
                                <i class="ph ph-trash"></i> Reset to Default
                            </button>
                        @endif
                    </div>

                    <!-- Coverage Badges -->
                    <div class="flex flex-wrap items-center gap-1.5 text-[10px] font-bold">
                        <span class="text-slate-400 uppercase tracking-wider mr-1">Automatically Updates:</span>
                        <span class="px-2 py-0.5 rounded bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">✓ Website Header & Footer</span>
                        <span class="px-2 py-0.5 rounded bg-blue-500/15 text-blue-400 border border-blue-500/30">✓ App Sidebar Cockpit</span>
                        <span class="px-2 py-0.5 rounded bg-purple-500/15 text-purple-400 border border-purple-500/30">✓ Login & Register Screens</span>
                        <span class="px-2 py-0.5 rounded bg-amber-500/15 text-amber-400 border border-amber-500/30">✓ Customer Invoice Portal</span>
                        <span class="px-2 py-0.5 rounded bg-indigo-500/15 text-indigo-400 border border-indigo-500/30">✓ PDF Invoices</span>
                    </div>

                    <!-- Current Preview Box -->
                    <div class="p-5 bg-slate-950/60 rounded-xl border border-dashed border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Current Active Logo</span>
                            <div class="h-16 flex items-center justify-center px-5 py-2 bg-slate-900 rounded-xl shadow-xs border border-slate-700 min-w-[200px] max-w-full">
                                @if(!empty($systemLogo))
                                    <img src="{{ $systemLogo }}" alt="System Logo" class="max-h-12 max-w-[220px] object-contain">
                                @else
                                    <div class="flex items-center gap-2">
                                        <div class="w-9 h-9 rounded-lg bg-red-500/10 border border-red-500/20 flex items-center justify-center p-1.5">
                                            <svg class="w-full h-full text-red-500" viewBox="0 0 900 900" fill="currentColor">
                                                <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                                            </svg>
                                        </div>
                                        <span class="font-black text-white text-sm">Invoicy <span class="text-xs font-normal text-slate-400">by</span> <span class="text-red-500 font-bold">RedMaple</span></span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Quick Preview Links -->
                        <div class="flex flex-col gap-1.5 w-full sm:w-auto text-xs font-bold text-slate-400">
                            <span class="text-[10px] text-slate-400 uppercase tracking-wider">Live Check Links:</span>
                            <a href="/" target="_blank" class="hover:text-amber-400 flex items-center gap-1 transition">
                                <i class="ph ph-browsers text-slate-400"></i> Landing Page <i class="ph ph-arrow-up-right text-[10px]"></i>
                            </a>
                            <a href="{{ route('login') }}" target="_blank" class="hover:text-amber-400 flex items-center gap-1 transition">
                                <i class="ph ph-lock-key text-slate-400"></i> Login Screen <i class="ph ph-arrow-up-right text-[10px]"></i>
                            </a>
                            <a href="{{ route('dashboard') }}" target="_blank" class="hover:text-amber-400 flex items-center gap-1 transition">
                                <i class="ph ph-squares-four text-slate-400"></i> App Sidebar <i class="ph ph-arrow-up-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Single Upload Controls -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Upload Brand Logo File</label>
                            <input type="file" name="logo_file" accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                   class="block w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-800 file:text-slate-200 hover:file:bg-slate-700 cursor-pointer">
                            <p class="text-[10px] text-slate-500 mt-1">PNG, JPG, WEBP, or SVG (Max 2MB). Transparent background recommended.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Or Direct Image URL</label>
                            <input type="url" name="logo_url" value="{{ $systemLogo ?? '' }}" placeholder="https://domain.com/brand-logo.png"
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-700 bg-slate-950 text-white text-xs focus:ring-2 focus:ring-amber-500 outline-none">
                            <p class="text-[10px] text-slate-500 mt-1">Paste hosted logo URL to apply instantly</p>
                        </div>
                    </div>
                </div>

                <!-- Website Favicon Card -->
                <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6 shadow-sm space-y-5 lg:col-span-1">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center justify-center text-xl shrink-0">
                                <i class="ph ph-sparkle"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-white text-sm">Website Favicon</h3>
                                <p class="text-[11px] text-slate-400">Browser tab icon (.ico, .png, .svg)</p>
                            </div>
                        </div>
                        @if(!empty($faviconUrl))
                            <button type="submit" name="remove_favicon" value="1" 
                                    class="text-xs text-rose-400 hover:text-rose-300 font-bold flex items-center gap-1"
                                    onclick="return confirm('Remove custom favicon and reset to default?')">
                                <i class="ph ph-trash"></i> Reset
                            </button>
                        @endif
                    </div>

                    <!-- Current Preview Box -->
                    <div class="p-4 bg-slate-950/60 rounded-xl border border-dashed border-slate-800 flex flex-col items-center justify-center text-center">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Browser Tab Simulation</span>
                        <div class="flex items-center gap-2 px-3 py-2 bg-slate-900 rounded-t-lg shadow-sm border border-slate-700 w-full max-w-[220px]">
                            @if(!empty($faviconUrl))
                                <img src="{{ $faviconUrl }}" alt="Favicon" class="w-4 h-4 object-contain shrink-0">
                            @else
                                <svg class="w-4 h-4 text-red-500 shrink-0" viewBox="0 0 900 900" fill="currentColor">
                                    <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                                </svg>
                            @endif
                            <span class="text-[11px] font-semibold text-slate-200 truncate">Invoicy Studio</span>
                            <i class="ph ph-x text-[10px] text-slate-400 ml-auto"></i>
                        </div>
                    </div>

                    <!-- Upload Controls -->
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">Upload Favicon File</label>
                            <input type="file" name="favicon_file" accept=".ico,image/png,image/x-icon,image/svg+xml,image/webp"
                                   class="block w-full text-xs text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-800 file:text-slate-200 hover:file:bg-slate-700 cursor-pointer">
                            <p class="text-[10px] text-slate-500 mt-0.5">.ICO, .PNG, .SVG, .WEBP (16x16, 32x32, or 64x64)</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">Or Direct Favicon URL</label>
                            <input type="url" name="favicon_url" value="{{ $cms['favicon_url'] ?? '' }}" placeholder="https://domain.com/favicon.ico"
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-700 bg-slate-950 text-white text-xs focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Brand Texts & Copyright -->
            <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-800">
                    <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 border border-purple-500/20 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-text-t"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-base">Brand Name & Screen Typography</h3>
                        <p class="text-xs text-slate-400">Customize brand titles, login card slogans, and footer copyright</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Brand Main Name</label>
                        <input type="text" name="website_brand_name" value="{{ $cms['website_brand_name'] ?? 'Invoicy' }}"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-700 bg-slate-950 text-white text-xs font-bold focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Brand Subtext / Byline</label>
                        <input type="text" name="website_brand_sub" value="{{ $cms['website_brand_sub'] ?? 'RedMaple' }}"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-700 bg-slate-950 text-white text-xs font-bold focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Login Screen Title</label>
                        <input type="text" name="login_title" value="{{ $cms['login_title'] ?? 'Invoicy by RedMaple' }}"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-700 bg-slate-950 text-white text-xs focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Login Screen Subtitle</label>
                        <input type="text" name="login_subtitle" value="{{ $cms['login_subtitle'] ?? 'Sign in to your commercial invoicing studio' }}"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-700 bg-slate-950 text-white text-xs focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Website Footer Copyright Text</label>
                        <input type="text" name="footer_copyright" value="{{ $cms['footer_copyright'] ?? '© ' . date('Y') . ' Invoicy by RedMaple. All rights reserved.' }}"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-700 bg-slate-950 text-white text-xs focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                </div>

                <div class="flex justify-end pt-3 border-t border-slate-800">
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl text-xs shadow-sm transition">
                        <i class="ph ph-floppy-disk text-base"></i> Save Logos & Branding
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- TAB 2: NAVIGATION MENU BUILDER -->
    <div x-show="currentTab === 'navigation'" class="space-y-6" x-data="{ 
        navTab: 'header', 
        headerItems: {{ json_encode($navItems) }}, 
        footerItems: {{ json_encode($footerNavItems) }} 
    }">
        <!-- Sub-navigation tabs: Header Nav vs Footer Nav -->
        <div class="flex items-center gap-3 border-b border-slate-800 pb-3">
            <button type="button" @click="navTab = 'header'" 
                    :class="navTab === 'header' ? 'bg-amber-500 text-slate-950 font-bold shadow-xs' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:bg-slate-800'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2">
                <i class="ph ph-navigation-arrow text-sm"></i> Header Navigation Menu
            </button>
            <button type="button" @click="navTab = 'footer'" 
                    :class="navTab === 'footer' ? 'bg-amber-500 text-slate-950 font-bold shadow-xs' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:bg-slate-800'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2">
                <i class="ph ph-layout text-sm"></i> Footer Navigation Menu
            </button>
        </div>

        <!-- 1. HEADER NAVIGATION FORM -->
        <div x-show="navTab === 'header'" class="space-y-6">
            <form method="POST" action="{{ route('builder.navigation') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="nav_type" value="header">

                <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6 shadow-sm space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-800 gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 border border-blue-500/20 flex items-center justify-center text-xl shrink-0">
                                <i class="ph ph-list-dashes"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-white text-base">Header Navigation Menu Links</h3>
                                <p class="text-xs text-slate-400">Configure menu links displayed on the main website header</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="headerItems.push({ label: 'New Link', url: '#', target: '_self', active: true })"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700 text-xs font-bold transition">
                                <i class="ph ph-plus-circle text-sm"></i> Add Menu Link
                            </button>
                            <button type="submit" name="action" value="reset" 
                                    onclick="return confirm('Reset header navigation menu links back to original default layout?')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-700 bg-slate-800 text-slate-300 hover:bg-slate-700 text-xs font-bold transition">
                                <i class="ph ph-arrows-clockwise text-sm"></i> Reset to Default
                            </button>
                        </div>
                    </div>

                    <!-- Header Navigation Items Table / List -->
                    <div class="space-y-3">
                        <template x-for="(item, index) in headerItems" :key="index">
                            <div class="p-3.5 rounded-xl border border-slate-800 bg-slate-950/60 flex flex-col sm:flex-row items-center gap-3">
                                <span class="text-slate-400 cursor-grab px-1" title="Drag/Order">
                                    <i class="ph ph-dots-six-vertical text-lg"></i>
                                </span>

                                <div class="w-full sm:w-1/3">
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-0.5">Link Text</label>
                                    <input type="text" :name="'nav_items[' + index + '][label]'" x-model="item.label" required
                                           class="w-full px-3 py-1.5 rounded-lg border border-slate-700 bg-slate-900 text-white text-xs font-semibold focus:ring-2 focus:ring-amber-500 outline-none">
                                </div>

                                <div class="w-full sm:w-1/2">
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-0.5">URL / Anchor</label>
                                    <input type="text" :name="'nav_items[' + index + '][url]'" x-model="item.url" required
                                           class="w-full px-3 py-1.5 rounded-lg border border-slate-700 bg-slate-900 text-white text-xs font-mono focus:ring-2 focus:ring-amber-500 outline-none">
                                </div>

                                <div class="w-full sm:w-auto flex items-center gap-4 shrink-0 pt-2 sm:pt-4">
                                    <label class="flex items-center gap-1 text-xs text-slate-300 cursor-pointer">
                                        <input type="checkbox" :name="'nav_items[' + index + '][target]'" value="_blank" 
                                               :checked="item.target === '_blank'" 
                                               @change="item.target = $event.target.checked ? '_blank' : '_self'"
                                               class="w-4 h-4 text-amber-500 rounded bg-slate-900 border-slate-700">
                                        <span>New Tab</span>
                                    </label>

                                    <label class="flex items-center gap-1 text-xs font-bold text-emerald-400 cursor-pointer">
                                        <input type="checkbox" :name="'nav_items[' + index + '][active]'" value="1" 
                                               :checked="item.active !== false"
                                               @change="item.active = $event.target.checked"
                                               class="w-4 h-4 text-emerald-500 rounded bg-slate-900 border-slate-700">
                                        <span>Visible</span>
                                    </label>

                                    <button type="button" @click="headerItems.splice(index, 1)" 
                                            class="p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-lg transition" title="Remove Link">
                                        <i class="ph ph-trash text-base"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Custom Pages Quick-Add Shortcut -->
                    @if($pages->where('is_published', true)->count() > 0)
                        <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-2 text-slate-300 font-semibold">
                                <i class="ph ph-info text-base text-amber-400"></i>
                                <span>Quick Add Published Custom Pages to Header Menu:</span>
                            </div>
                            <div class="flex flex-wrap items-center gap-1.5">
                                @foreach($pages->where('is_published', true) as $p)
                                    <button type="button" 
                                            @click="headerItems.push({ label: '{{ addslashes($p->title) }}', url: '{{ url('/p/' . $p->slug) }}', target: '_self', active: true })"
                                            class="px-2.5 py-1 rounded bg-slate-900 hover:bg-slate-800 border border-slate-700 text-amber-400 font-bold transition">
                                        + {{ $p->title }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-end pt-3 border-t border-slate-800">
                        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl text-xs shadow-sm transition">
                            <i class="ph ph-floppy-disk text-base"></i> Save Header Navigation Links
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- 2. FOOTER NAVIGATION FORM -->
        <div x-show="navTab === 'footer'" class="space-y-6">
            <form method="POST" action="{{ route('builder.navigation') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="nav_type" value="footer">

                <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6 shadow-sm space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-800 gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 flex items-center justify-center text-xl shrink-0">
                                <i class="ph ph-layout"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-white text-base">Footer Navigation Menu Links</h3>
                                <p class="text-xs text-slate-400">Configure links displayed at the bottom of the landing page, custom pages, and public documents</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="footerItems.push({ label: 'New Link', url: '#', target: '_self', active: true })"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700 text-xs font-bold transition">
                                <i class="ph ph-plus-circle text-sm"></i> Add Footer Link
                            </button>
                            <button type="submit" name="action" value="reset_footer" 
                                    onclick="return confirm('Reset footer navigation links back to default pages (Privacy, Terms, About)?')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-700 bg-slate-800 text-slate-300 hover:bg-slate-700 text-xs font-bold transition">
                                <i class="ph ph-arrows-clockwise text-sm"></i> Reset to Default
                            </button>
                        </div>
                    </div>

                    <!-- Footer Navigation Items Table / List -->
                    <div class="space-y-3">
                        <template x-for="(item, index) in footerItems" :key="index">
                            <div class="p-3.5 rounded-xl border border-slate-800 bg-slate-950/60 flex flex-col sm:flex-row items-center gap-3">
                                <span class="text-slate-400 cursor-grab px-1" title="Order">
                                    <i class="ph ph-dots-six-vertical text-lg"></i>
                                </span>

                                <div class="w-full sm:w-1/3">
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-0.5">Link Text</label>
                                    <input type="text" :name="'footer_nav_items[' + index + '][label]'" x-model="item.label" required
                                           class="w-full px-3 py-1.5 rounded-lg border border-slate-700 bg-slate-900 text-white text-xs font-semibold focus:ring-2 focus:ring-amber-500 outline-none">
                                </div>

                                <div class="w-full sm:w-1/2">
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-0.5">URL / Route</label>
                                    <input type="text" :name="'footer_nav_items[' + index + '][url]'" x-model="item.url" required
                                           class="w-full px-3 py-1.5 rounded-lg border border-slate-700 bg-slate-900 text-white text-xs font-mono focus:ring-2 focus:ring-amber-500 outline-none">
                                </div>

                                <div class="w-full sm:w-auto flex items-center gap-4 shrink-0 pt-2 sm:pt-4">
                                    <label class="flex items-center gap-1 text-xs text-slate-300 cursor-pointer">
                                        <input type="checkbox" :name="'footer_nav_items[' + index + '][target]'" value="_blank" 
                                               :checked="item.target === '_blank'" 
                                               @change="item.target = $event.target.checked ? '_blank' : '_self'"
                                               class="w-4 h-4 text-amber-500 rounded bg-slate-900 border-slate-700">
                                        <span>New Tab</span>
                                    </label>

                                    <label class="flex items-center gap-1 text-xs font-bold text-emerald-400 cursor-pointer">
                                        <input type="checkbox" :name="'footer_nav_items[' + index + '][active]'" value="1" 
                                               :checked="item.active !== false"
                                               @change="item.active = $event.target.checked"
                                               class="w-4 h-4 text-emerald-500 rounded bg-slate-900 border-slate-700">
                                        <span>Visible</span>
                                    </label>

                                    <button type="button" @click="footerItems.splice(index, 1)" 
                                            class="p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-lg transition" title="Remove Link">
                                        <i class="ph ph-trash text-base"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Custom Pages Quick-Add to Footer -->
                    @if($pages->where('is_published', true)->count() > 0)
                        <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-2 text-slate-300 font-semibold">
                                <i class="ph ph-info text-base text-amber-400"></i>
                                <span>Quick Add Published Custom Pages to Footer Menu:</span>
                            </div>
                            <div class="flex flex-wrap items-center gap-1.5">
                                @foreach($pages->where('is_published', true) as $p)
                                    <button type="button" 
                                            @click="footerItems.push({ label: '{{ addslashes($p->title) }}', url: '{{ url('/p/' . $p->slug) }}', target: '_self', active: true })"
                                            class="px-2.5 py-1 rounded bg-slate-900 hover:bg-slate-800 border border-slate-700 text-amber-400 font-bold transition">
                                        + {{ $p->title }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-end pt-3 border-t border-slate-800">
                        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl text-xs shadow-sm transition">
                            <i class="ph ph-floppy-disk text-base"></i> Save Footer Navigation Links
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 3: LANDING SECTIONS BUILDER -->
    <div x-show="currentTab === 'sections'" class="space-y-6">
        <form method="POST" action="{{ route('builder.sections') }}" class="space-y-6">
            @csrf

            <!-- Hero Section Copy -->
            <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-800">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-megaphone-simple"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-base">Hero Header & Call to Action</h3>
                        <p class="text-xs text-slate-400">Above the fold headlines, subtext, and primary action buttons</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Top Announcement Badge</label>
                        <input type="text" name="hero_badge" value="{{ $cms['hero_badge'] ?? 'Introducing Invoicy by RedMaple • Automated Payment Reminders & Multi-Currency GAAP Accounting' }}"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-700 bg-slate-950 text-white text-xs focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Hero Main Title (Headline)</label>
                        <input type="text" name="hero_title" value="{{ $cms['hero_title'] ?? 'Get Paid Faster. Automate Reminders. Invoice Worldwide.' }}"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-700 bg-slate-950 text-white text-sm font-bold focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Hero Subtitle Paragraph</label>
                        <textarea name="hero_subtitle" rows="3" class="w-full px-3.5 py-2 rounded-xl border border-slate-700 bg-slate-950 text-white text-xs focus:ring-2 focus:ring-amber-500 outline-none">{{ $cms['hero_subtitle'] ?? 'Invoicy by RedMaple gives modern agencies, freelancers, and businesses real-time VAT calculations, automated milestone payment reminders (45, 30, 15, 7, 3, 2 days before due), password-protected client PDFs, and seamless one-click billing.' }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Primary CTA Button Text</label>
                            <input type="text" name="hero_cta_primary" value="{{ $cms['hero_cta_primary'] ?? 'Start Free with Invoicy' }}"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-700 bg-slate-950 text-white text-xs focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Secondary CTA Button Text</label>
                            <input type="text" name="hero_cta_secondary" value="{{ $cms['hero_cta_secondary'] ?? 'Launch App / Login' }}"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-700 bg-slate-950 text-white text-xs focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Future SaaS Products Copy -->
            <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-800">
                    <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 border border-purple-500/20 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-sparkle"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-base">RedMaple SaaS Suite Roadmap Copy</h3>
                        <p class="text-xs text-slate-400">Descriptions for upcoming software modules</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">CRM Pro Description</label>
                        <textarea name="crm_desc" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-700 bg-slate-950 text-white text-xs focus:ring-2 focus:ring-amber-500 outline-none">{{ $cms['crm_desc'] ?? 'Visual Kanban pipelines, deal stages, client activity tracking, email sync, and automated proposal-to-invoice conversion.' }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">HRMS & Payroll Description</label>
                        <textarea name="hrms_desc" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-700 bg-slate-950 text-white text-xs focus:ring-2 focus:ring-amber-500 outline-none">{{ $cms['hrms_desc'] ?? 'Employee directories, attendance, vacation requests, digital payslips, and compliance withholding calculation.' }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Project Desk Description</label>
                        <textarea name="project_desc" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-700 bg-slate-950 text-white text-xs focus:ring-2 focus:ring-amber-500 outline-none">{{ $cms['project_desc'] ?? 'Project milestones, billable hours tracking, client collaborative boards, and instant time-log conversion into Invoicy line items.' }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">POS Description</label>
                        <textarea name="pos_desc" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-700 bg-slate-950 text-white text-xs focus:ring-2 focus:ring-amber-500 outline-none">{{ $cms['pos_desc'] ?? 'Barcode scanning, receipt printers, cash drawers, multi-location stock replenishment, and instant receipt dispatch.' }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end pt-3 border-t border-slate-800">
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl text-xs shadow-sm transition">
                        <i class="ph ph-floppy-disk text-base"></i> Save Landing Sections
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- TAB 4: CUSTOM WEBSITE PAGES -->
    <div x-show="currentTab === 'pages'" class="space-y-6">
        <!-- Create New Page Form -->
        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6 shadow-sm space-y-4">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-800">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center text-xl shrink-0">
                    <i class="ph ph-file-plus"></i>
                </div>
                <div>
                    <h3 class="font-bold text-white text-base">Create New Custom Website Page</h3>
                    <p class="text-xs text-slate-400">Publish custom pages (e.g. Terms, Privacy, FAQ, About Us, Landing promos) with public URLs at /p/{slug}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('builder.pages.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Page Title *</label>
                        <input type="text" name="title" required placeholder="e.g. Terms of Service"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-700 bg-slate-950 text-white text-xs font-bold focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Custom URL Slug (Optional)</label>
                        <div class="flex items-center">
                            <span class="px-3 py-2.5 rounded-l-xl bg-slate-800 border border-r-0 border-slate-700 text-slate-400 text-xs">/p/</span>
                            <input type="text" name="slug" placeholder="terms-of-service"
                                   class="w-full px-3.5 py-2.5 rounded-r-xl border border-slate-700 bg-slate-950 text-white text-xs focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">SEO Meta Description</label>
                    <input type="text" name="meta_description" placeholder="Brief summary of this page for search engines and social cards..."
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-700 bg-slate-950 text-white text-xs focus:ring-2 focus:ring-amber-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Page Content (HTML / Text) *</label>
                    <textarea name="content" rows="6" required placeholder="<h2>Your Page Heading</h2><p>Your custom content here...</p>"
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-700 bg-slate-950 text-white text-xs font-mono focus:ring-2 focus:ring-amber-500 outline-none"></textarea>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2 border-t border-slate-800">
                    <div class="flex flex-wrap items-center gap-4 sm:gap-5">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-emerald-400">
                            <input type="checkbox" name="is_published" value="1" checked class="w-4 h-4 text-emerald-500 rounded bg-slate-950 border-slate-700">
                            <span>Published (Publicly Viewable)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-blue-400">
                            <input type="checkbox" name="show_in_nav" value="1" class="w-4 h-4 text-blue-500 rounded bg-slate-950 border-slate-700">
                            <span>Show in Header Nav</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-indigo-400">
                            <input type="checkbox" name="show_in_footer" value="1" class="w-4 h-4 text-indigo-500 rounded bg-slate-950 border-slate-700">
                            <span>Show in Footer Nav</span>
                        </label>
                    </div>

                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl text-xs shadow-sm transition">
                        <i class="ph ph-plus-circle text-base"></i> Publish Website Page
                    </button>
                </div>
            </form>
        </div>

        <!-- Existing Pages List -->
        <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-white text-base">Published Website Pages</h3>
                    <p class="text-xs text-slate-400">Manage, edit, or preview all custom website pages</p>
                </div>
                <span class="text-xs bg-slate-800 text-slate-300 border border-slate-700 font-bold px-3 py-1 rounded-full">
                    {{ $pages->count() }} Pages
                </span>
            </div>

            @if($pages->isEmpty())
                <div class="p-8 text-center text-slate-400 text-sm">
                    No custom pages created yet.
                </div>
            @else
                <div class="divide-y divide-slate-800/60">
                    @foreach($pages as $page)
                        <div x-data="{ openEdit: false }" class="p-5 hover:bg-slate-800/40 transition">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-bold text-white">{{ $page->title }}</h4>
                                        @if($page->is_published)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">Published</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700">Draft</span>
                                        @endif
                                        @if($page->show_in_nav)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-500/15 text-blue-400 border border-blue-500/30">In Header Nav</span>
                                        @endif
                                        @if($page->show_in_footer)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/15 text-indigo-400 border border-indigo-500/30">In Footer Nav</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="text-xs text-amber-400 hover:text-amber-300 hover:underline flex items-center gap-1 font-mono">
                                            <span>/p/{{ $page->slug }}</span>
                                            <i class="ph ph-arrow-up-right text-[10px]"></i>
                                        </a>
                                        @if($page->meta_description)
                                            <span class="text-slate-600">&bull;</span>
                                            <span class="text-xs text-slate-400 line-clamp-1">{{ $page->meta_description }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <a href="{{ route('pages.show', $page->slug) }}" target="_blank" 
                                       class="px-3 py-1.5 rounded-lg border border-slate-700 bg-slate-800 text-slate-200 hover:bg-slate-700 text-xs font-bold flex items-center gap-1">
                                        <i class="ph ph-eye text-sm text-slate-400"></i> View
                                    </a>
                                    <button @click="openEdit = !openEdit"
                                            class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 text-xs font-bold transition flex items-center gap-1">
                                        <i class="ph ph-pencil-simple text-sm text-amber-400"></i> Edit
                                    </button>
                                    <form method="POST" action="{{ route('builder.pages.destroy', $page->id) }}" onsubmit="return confirm('Delete page {{ addslashes($page->title) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-lg transition" title="Delete">
                                            <i class="ph ph-trash text-base"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Expandable Edit Form -->
                            <div x-show="openEdit" x-cloak class="mt-4 pt-4 border-t border-slate-800 bg-slate-950/80 p-4 rounded-xl space-y-4">
                                <form method="POST" action="{{ route('builder.pages.update', $page->id) }}" class="space-y-4">
                                    @csrf
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Title</label>
                                            <input type="text" name="title" value="{{ $page->title }}" required class="w-full px-3 py-2 rounded-lg border border-slate-700 bg-slate-900 text-white text-xs font-bold focus:ring-2 focus:ring-amber-500 outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Slug</label>
                                            <input type="text" name="slug" value="{{ $page->slug }}" required class="w-full px-3 py-2 rounded-lg border border-slate-700 bg-slate-900 text-white text-xs font-mono focus:ring-2 focus:ring-amber-500 outline-none">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Meta Description</label>
                                        <input type="text" name="meta_description" value="{{ $page->meta_description }}" class="w-full px-3 py-2 rounded-lg border border-slate-700 bg-slate-900 text-white text-xs focus:ring-2 focus:ring-amber-500 outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Content</label>
                                        <textarea name="content" rows="6" required class="w-full px-3 py-2 rounded-lg border border-slate-700 bg-slate-900 text-white text-xs font-mono focus:ring-2 focus:ring-amber-500 outline-none">{{ $page->content }}</textarea>
                                    </div>
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2 border-t border-slate-800">
                                        <div class="flex flex-wrap items-center gap-4">
                                            <label class="flex items-center gap-1.5 cursor-pointer text-xs font-bold text-emerald-400">
                                                <input type="checkbox" name="is_published" value="1" {{ $page->is_published ? 'checked' : '' }} class="w-4 h-4 text-emerald-500 rounded bg-slate-900 border-slate-700">
                                                <span>Published</span>
                                            </label>
                                            <label class="flex items-center gap-1.5 cursor-pointer text-xs font-bold text-blue-400">
                                                <input type="checkbox" name="show_in_nav" value="1" {{ $page->show_in_nav ? 'checked' : '' }} class="w-4 h-4 text-blue-500 rounded bg-slate-900 border-slate-700">
                                                <span>In Header Nav</span>
                                            </label>
                                            <label class="flex items-center gap-1.5 cursor-pointer text-xs font-bold text-indigo-400">
                                                <input type="checkbox" name="show_in_footer" value="1" {{ $page->show_in_footer ? 'checked' : '' }} class="w-4 h-4 text-indigo-500 rounded bg-slate-900 border-slate-700">
                                                <span>In Footer Nav</span>
                                            </label>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="openEdit = false" class="px-4 py-1.5 rounded-lg border border-slate-700 text-xs font-bold text-slate-300 bg-slate-800 hover:bg-slate-700">
                                                Cancel
                                            </button>
                                            <button type="submit" class="px-4 py-1.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-lg text-xs shadow">
                                                Save Changes
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
