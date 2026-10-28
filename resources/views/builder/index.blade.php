@extends('layouts.app', ['title' => 'Website Builder', 'header' => 'Website & Page Builder'])

@section('content')
<div class="space-y-8" x-data="{ currentTab: 'branding', editingPage: null, newNavLabel: '', newNavUrl: '', newNavTarget: '_self' }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-200 gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-red-50 text-red-600 border border-red-200">
                    <i class="ph ph-paint-brush-broad text-xl"></i>
                </span>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Website & Page Builder</h1>
            </div>
            <p class="text-sm text-slate-500 mt-1">
                Customize your website main logo, login screen logo, navigation menus, landing page copy, and custom website pages.
            </p>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center bg-slate-100 p-1 rounded-xl text-xs font-bold overflow-x-auto">
            <button @click="currentTab = 'branding'" 
                    :class="currentTab === 'branding' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3.5 py-2 rounded-lg transition flex items-center gap-1.5 shrink-0">
                <i class="ph ph-image text-base text-red-600"></i> Logos & Branding
            </button>
            <button @click="currentTab = 'navigation'" 
                    :class="currentTab === 'navigation' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3.5 py-2 rounded-lg transition flex items-center gap-1.5 shrink-0">
                <i class="ph ph-list-dashes text-base text-blue-600"></i> Navigation Menu
            </button>
            <button @click="currentTab = 'sections'" 
                    :class="currentTab === 'sections' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3.5 py-2 rounded-lg transition flex items-center gap-1.5 shrink-0">
                <i class="ph ph-layout text-base text-purple-600"></i> Landing Sections
            </button>
            <button @click="currentTab = 'pages'" 
                    :class="currentTab === 'pages' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3.5 py-2 rounded-lg transition flex items-center gap-1.5 shrink-0">
                <i class="ph ph-files text-base text-emerald-600"></i> Custom Pages
            </button>
        </div>
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

    <!-- TAB 1: LOGOS & BRANDING CUSTOMIZER -->
    <div x-show="currentTab === 'branding'" class="space-y-6">
        <form method="POST" action="{{ route('builder.branding') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Website Main Logo Card -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-xl shrink-0">
                                <i class="ph ph-browsers"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 text-sm">Website Main Logo</h3>
                                <p class="text-[11px] text-slate-500">Navigation bar, footer & app</p>
                            </div>
                        </div>
                        @if(!empty($cms['website_logo_url']))
                            <button type="submit" name="remove_website_logo" value="1" 
                                    class="text-xs text-rose-600 hover:text-rose-700 font-bold flex items-center gap-1"
                                    onclick="return confirm('Remove custom website logo and reset to default?')">
                                <i class="ph ph-trash"></i> Reset
                            </button>
                        @endif
                    </div>

                    <!-- Current Preview Box -->
                    <div class="p-4 bg-slate-50 rounded-xl border border-dashed border-slate-200 flex flex-col items-center justify-center text-center">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Live Logo Preview</span>
                        <div class="h-14 flex items-center justify-center px-4 py-2 bg-white rounded-lg shadow-2xs border border-slate-100 max-w-full">
                            @if(!empty($cms['website_logo_url']))
                                <img src="{{ $cms['website_logo_url'] }}" alt="Website Logo" class="max-h-10 max-w-[180px] object-contain">
                            @else
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-red-50 border border-red-200 flex items-center justify-center p-1.5">
                                        <svg class="w-full h-full text-red-600" viewBox="0 0 900 900" fill="currentColor">
                                            <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                                        </svg>
                                    </div>
                                    <span class="font-black text-slate-900 text-xs">Invoicy <span class="text-[10px] font-normal text-slate-400">by</span> <span class="text-red-600 font-bold">RedMaple</span></span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Upload Controls -->
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Upload Logo File</label>
                            <input type="file" name="website_logo_file" accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                   class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer">
                            <p class="text-[10px] text-slate-400 mt-0.5">PNG, JPG, WEBP, SVG (Max 2MB)</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Or Logo Image URL</label>
                            <input type="url" name="website_logo_url" value="{{ $cms['website_logo_url'] ?? '' }}" placeholder="https://domain.com/logo.png"
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                </div>

                <!-- Login Screen Logo Card -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                                <i class="ph ph-lock-key"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 text-sm">Login Screen Logo</h3>
                                <p class="text-[11px] text-slate-500">Appears on /login & /register</p>
                            </div>
                        </div>
                        @if(!empty($cms['login_logo_url']))
                            <button type="submit" name="remove_login_logo" value="1" 
                                    class="text-xs text-rose-600 hover:text-rose-700 font-bold flex items-center gap-1"
                                    onclick="return confirm('Remove custom login logo and reset to default?')">
                                <i class="ph ph-trash"></i> Reset
                            </button>
                        @endif
                    </div>

                    <!-- Current Preview Box -->
                    <div class="p-4 bg-slate-50 rounded-xl border border-dashed border-slate-200 flex flex-col items-center justify-center text-center">
                        <div class="flex items-center justify-between w-full mb-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Live Login Box Preview</span>
                            <a href="{{ route('login') }}" target="_blank" class="text-[10px] font-bold text-blue-600 hover:underline flex items-center gap-0.5">
                                Preview <i class="ph ph-arrow-up-right"></i>
                            </a>
                        </div>
                        <div class="h-14 flex items-center justify-center px-4 py-2 bg-white rounded-lg shadow-2xs border border-slate-100 max-w-full">
                            @if(!empty($cms['login_logo_url']))
                                <img src="{{ $cms['login_logo_url'] }}" alt="Login Logo" class="max-h-10 max-w-[180px] object-contain">
                            @else
                                <div class="w-10 h-10 bg-red-50 border border-red-100 rounded-xl flex items-center justify-center p-2">
                                    <svg class="w-full h-full text-red-600" viewBox="0 0 900 900" fill="currentColor">
                                        <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Upload Controls -->
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Upload Login Logo File</label>
                            <input type="file" name="login_logo_file" accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                   class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                            <p class="text-[10px] text-slate-400 mt-0.5">PNG, JPG, WEBP, SVG (Max 2MB)</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Or Login Logo URL</label>
                            <input type="url" name="login_logo_url" value="{{ $cms['login_logo_url'] ?? '' }}" placeholder="https://domain.com/login-logo.png"
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Website Favicon Card -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                                <i class="ph ph-sparkle"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 text-sm">Website Favicon</h3>
                                <p class="text-[11px] text-slate-500">Browser tab icon (.ico, .png, .svg)</p>
                            </div>
                        </div>
                        @if(!empty($cms['favicon_url']))
                            <button type="submit" name="remove_favicon" value="1" 
                                    class="text-xs text-rose-600 hover:text-rose-700 font-bold flex items-center gap-1"
                                    onclick="return confirm('Remove custom favicon and reset to default?')">
                                <i class="ph ph-trash"></i> Reset
                            </button>
                        @endif
                    </div>

                    <!-- Current Preview Box -->
                    <div class="p-4 bg-slate-50 rounded-xl border border-dashed border-slate-200 flex flex-col items-center justify-center text-center">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Browser Tab Simulation</span>
                        <div class="flex items-center gap-2 px-3 py-2 bg-white rounded-t-lg shadow-2xs border border-slate-200 w-full max-w-[220px]">
                            @if(!empty($cms['favicon_url']))
                                <img src="{{ $cms['favicon_url'] }}" alt="Favicon" class="w-4 h-4 object-contain shrink-0">
                            @else
                                <svg class="w-4 h-4 text-red-600 shrink-0" viewBox="0 0 900 900" fill="currentColor">
                                    <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                                </svg>
                            @endif
                            <span class="text-[11px] font-semibold text-slate-700 truncate">Invoicy Studio</span>
                            <i class="ph ph-x text-[10px] text-slate-400 ml-auto"></i>
                        </div>
                    </div>

                    <!-- Upload Controls -->
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Upload Favicon File</label>
                            <input type="file" name="favicon_file" accept=".ico,image/png,image/x-icon,image/svg+xml,image/webp"
                                   class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 cursor-pointer">
                            <p class="text-[10px] text-slate-400 mt-0.5">.ICO, .PNG, .SVG, .WEBP (16x16, 32x32, or 64x64)</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Or Direct Favicon URL</label>
                            <input type="url" name="favicon_url" value="{{ $cms['favicon_url'] ?? '' }}" placeholder="https://domain.com/favicon.ico"
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-amber-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Brand Texts & Copyright -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-text-t"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Brand Name & Screen Typography</h3>
                        <p class="text-xs text-slate-500">Customize brand titles, login card slogans, and footer copyright</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Brand Main Name</label>
                        <input type="text" name="website_brand_name" value="{{ $cms['website_brand_name'] ?? 'Invoicy' }}"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Brand Subtext / Byline</label>
                        <input type="text" name="website_brand_sub" value="{{ $cms['website_brand_sub'] ?? 'RedMaple' }}"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Login Screen Title</label>
                        <input type="text" name="login_title" value="{{ $cms['login_title'] ?? 'Invoicy by RedMaple' }}"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Login Screen Subtitle</label>
                        <input type="text" name="login_subtitle" value="{{ $cms['login_subtitle'] ?? 'Sign in to your commercial invoicing studio' }}"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Website Footer Copyright Text</label>
                        <input type="text" name="footer_copyright" value="{{ $cms['footer_copyright'] ?? '© ' . date('Y') . ' Invoicy by RedMaple. All rights reserved.' }}"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                </div>

                <div class="flex justify-end pt-3">
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-xs shadow-md transition">
                        <i class="ph ph-floppy-disk text-base"></i> Save Logos & Branding
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- TAB 2: NAVIGATION MENU BUILDER -->
    <div x-show="currentTab === 'navigation'" class="space-y-6" x-data="{ items: {{ json_encode($navItems) }} }">
        <form method="POST" action="{{ route('builder.navigation') }}" class="space-y-6">
            @csrf

            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-100 gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                            <i class="ph ph-list-dashes"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-base">Header Navigation Menu Links</h3>
                            <p class="text-xs text-slate-500">Configure menu links displayed on the main website header</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="items.push({ label: 'New Link', url: '#', target: '_self', active: true })"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-bold transition">
                            <i class="ph ph-plus-circle text-sm"></i> Add Menu Link
                        </button>
                        <button type="submit" name="action" value="reset" 
                                onclick="return confirm('Reset navigation menu links back to original default layout?')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-bold transition">
                            <i class="ph ph-arrows-clockwise text-sm"></i> Reset to Default
                        </button>
                    </div>
                </div>

                <!-- Navigation Items Table / List -->
                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row items-center gap-3">
                            <span class="text-slate-400 cursor-grab px-1" title="Drag/Order">
                                <i class="ph ph-dots-six-vertical text-lg"></i>
                            </span>

                            <div class="w-full sm:w-1/3">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">Link Text</label>
                                <input type="text" :name="'nav_items[' + index + '][label]'" x-model="item.label" required
                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-semibold bg-white">
                            </div>

                            <div class="w-full sm:w-1/2">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">URL / Anchor</label>
                                <input type="text" :name="'nav_items[' + index + '][url]'" x-model="item.url" required
                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white">
                            </div>

                            <div class="w-full sm:w-auto flex items-center gap-4 shrink-0 pt-2 sm:pt-4">
                                <label class="flex items-center gap-1 text-xs text-slate-600 cursor-pointer">
                                    <input type="checkbox" :name="'nav_items[' + index + '][target]'" value="_blank" 
                                           :checked="item.target === '_blank'" 
                                           @change="item.target = $event.target.checked ? '_blank' : '_self'"
                                           class="w-4 h-4 text-blue-600 rounded">
                                    <span>New Tab</span>
                                </label>

                                <label class="flex items-center gap-1 text-xs font-bold text-emerald-700 cursor-pointer">
                                    <input type="checkbox" :name="'nav_items[' + index + '][active]'" value="1" 
                                           :checked="item.active !== false"
                                           @change="item.active = $event.target.checked"
                                           class="w-4 h-4 text-emerald-600 rounded">
                                    <span>Visible</span>
                                </label>

                                <button type="button" @click="items.splice(index, 1)" 
                                        class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition" title="Remove Link">
                                    <i class="ph ph-trash text-base"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Custom Pages Quick-Add Shortcut -->
                @if($pages->where('is_published', true)->count() > 0)
                    <div class="p-4 rounded-xl bg-blue-50/60 border border-blue-100 flex flex-wrap items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-2 text-blue-900 font-semibold">
                            <i class="ph ph-info text-base text-blue-600"></i>
                            <span>Quick Add Published Custom Pages to Menu:</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5">
                            @foreach($pages->where('is_published', true) as $p)
                                <button type="button" 
                                        @click="items.push({ label: '{{ addslashes($p->title) }}', url: '{{ url('/p/' . $p->slug) }}', target: '_self', active: true })"
                                        class="px-2.5 py-1 rounded bg-white hover:bg-blue-100 border border-blue-200 text-blue-800 font-bold transition">
                                    + {{ $p->title }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex justify-end pt-3">
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs shadow-md transition">
                        <i class="ph ph-floppy-disk text-base"></i> Save Navigation Links
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- TAB 3: LANDING SECTIONS BUILDER -->
    <div x-show="currentTab === 'sections'" class="space-y-6">
        <form method="POST" action="{{ route('builder.sections') }}" class="space-y-6">
            @csrf

            <!-- Hero Section Copy -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-megaphone-simple"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Hero Header & Call to Action</h3>
                        <p class="text-xs text-slate-500">Above the fold headlines, subtext, and primary action buttons</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Top Announcement Badge</label>
                        <input type="text" name="hero_badge" value="{{ $cms['hero_badge'] ?? 'Introducing Invoicy by RedMaple • Automated Payment Reminders & Multi-Currency GAAP Accounting' }}"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Hero Main Title (Headline)</label>
                        <input type="text" name="hero_title" value="{{ $cms['hero_title'] ?? 'Get Paid Faster. Automate Reminders. Invoice Worldwide.' }}"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Hero Subtitle Paragraph</label>
                        <textarea name="hero_subtitle" rows="3" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs">{{ $cms['hero_subtitle'] ?? 'Invoicy by RedMaple gives modern agencies, freelancers, and businesses real-time VAT calculations, automated milestone payment reminders (45, 30, 15, 7, 3, 2 days before due), password-protected client PDFs, and seamless one-click billing.' }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Primary CTA Button Text</label>
                            <input type="text" name="hero_cta_primary" value="{{ $cms['hero_cta_primary'] ?? 'Start Free with Invoicy' }}"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Secondary CTA Button Text</label>
                            <input type="text" name="hero_cta_secondary" value="{{ $cms['hero_cta_secondary'] ?? 'Launch App / Login' }}"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Future SaaS Products Copy -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-sparkle"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">RedMaple SaaS Suite Roadmap Copy</h3>
                        <p class="text-xs text-slate-500">Descriptions for upcoming software modules</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">CRM Pro Description</label>
                        <textarea name="crm_desc" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">{{ $cms['crm_desc'] ?? 'Visual Kanban pipelines, deal stages, client activity tracking, email sync, and automated proposal-to-invoice conversion.' }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">HRMS & Payroll Description</label>
                        <textarea name="hrms_desc" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">{{ $cms['hrms_desc'] ?? 'Employee directories, attendance, vacation requests, digital payslips, and compliance withholding calculation.' }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Project Desk Description</label>
                        <textarea name="project_desc" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">{{ $cms['project_desc'] ?? 'Project milestones, billable hours tracking, client collaborative boards, and instant time-log conversion into Invoicy line items.' }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">POS Description</label>
                        <textarea name="pos_desc" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">{{ $cms['pos_desc'] ?? 'Barcode scanning, receipt printers, cash drawers, multi-location stock replenishment, and instant receipt dispatch.' }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end pt-3">
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-xs shadow-md transition">
                        <i class="ph ph-floppy-disk text-base"></i> Save Landing Sections
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- TAB 4: CUSTOM WEBSITE PAGES -->
    <div x-show="currentTab === 'pages'" class="space-y-6">
        <!-- Create New Page Form -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                    <i class="ph ph-file-plus"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Create New Custom Website Page</h3>
                    <p class="text-xs text-slate-500">Publish custom pages (e.g. Terms, Privacy, FAQ, About Us, Landing promos) with public URLs at /p/{slug}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('builder.pages.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Page Title *</label>
                        <input type="text" name="title" required placeholder="e.g. Terms of Service"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Custom URL Slug (Optional)</label>
                        <div class="flex items-center">
                            <span class="px-3 py-2.5 rounded-l-xl bg-slate-100 border border-r-0 border-slate-300 text-slate-500 text-xs">/p/</span>
                            <input type="text" name="slug" placeholder="terms-of-service"
                                   class="w-full px-3.5 py-2.5 rounded-r-xl border border-slate-300 text-xs">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">SEO Meta Description</label>
                    <input type="text" name="meta_description" placeholder="Brief summary of this page for search engines and social cards..."
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Page Content (HTML / Text) *</label>
                    <textarea name="content" rows="6" required placeholder="<h2>Your Page Heading</h2><p>Your custom content here...</p>"
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-mono"></textarea>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                    <div class="flex items-center gap-5">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                            <input type="checkbox" name="is_published" value="1" checked class="w-4 h-4 text-emerald-600 rounded">
                            <span>Published (Publicly Viewable)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-blue-700">
                            <input type="checkbox" name="show_in_nav" value="1" class="w-4 h-4 text-blue-600 rounded">
                            <span>Show in Header Navigation</span>
                        </label>
                    </div>

                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md transition">
                        <i class="ph ph-plus-circle text-base"></i> Publish Website Page
                    </button>
                </div>
            </form>
        </div>

        <!-- Existing Pages List -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Published Website Pages</h3>
                    <p class="text-xs text-slate-500">Manage, edit, or preview all custom website pages</p>
                </div>
                <span class="text-xs bg-slate-100 text-slate-700 font-bold px-3 py-1 rounded-full">
                    {{ $pages->count() }} Pages
                </span>
            </div>

            @if($pages->isEmpty())
                <div class="p-8 text-center text-slate-400 text-sm">
                    No custom pages created yet.
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($pages as $page)
                        <div x-data="{ openEdit: false }" class="p-5 hover:bg-slate-50/50 transition">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-bold text-slate-900">{{ $page->title }}</h4>
                                        @if($page->is_published)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Published</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">Draft</span>
                                        @endif
                                        @if($page->show_in_nav)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">In Header Nav</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="text-xs text-blue-600 hover:underline flex items-center gap-1 font-mono">
                                            <span>/p/{{ $page->slug }}</span>
                                            <i class="ph ph-arrow-up-right text-[10px]"></i>
                                        </a>
                                        @if($page->meta_description)
                                            <span class="text-slate-300">&bull;</span>
                                            <span class="text-xs text-slate-500 line-clamp-1">{{ $page->meta_description }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <a href="{{ route('pages.show', $page->slug) }}" target="_blank" 
                                       class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-100 flex items-center gap-1">
                                        <i class="ph ph-eye text-sm text-slate-500"></i> View
                                    </a>
                                    <button @click="openEdit = !openEdit"
                                            class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition flex items-center gap-1">
                                        <i class="ph ph-pencil-simple text-sm"></i> Edit
                                    </button>
                                    <form method="POST" action="{{ route('builder.pages.destroy', $page->id) }}" onsubmit="return confirm('Delete page {{ addslashes($page->title) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition" title="Delete">
                                            <i class="ph ph-trash text-base"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Expandable Edit Form -->
                            <div x-show="openEdit" x-cloak class="mt-4 pt-4 border-t border-slate-200 bg-slate-50 p-4 rounded-xl space-y-4">
                                <form method="POST" action="{{ route('builder.pages.update', $page->id) }}" class="space-y-4">
                                    @csrf
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Title</label>
                                            <input type="text" name="title" value="{{ $page->title }}" required class="w-full px-3 py-2 rounded-lg border text-xs font-bold bg-white">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Slug</label>
                                            <input type="text" name="slug" value="{{ $page->slug }}" required class="w-full px-3 py-2 rounded-lg border text-xs bg-white">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Meta Description</label>
                                        <input type="text" name="meta_description" value="{{ $page->meta_description }}" class="w-full px-3 py-2 rounded-lg border text-xs bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Content</label>
                                        <textarea name="content" rows="6" required class="w-full px-3 py-2 rounded-lg border text-xs font-mono bg-white">{{ $page->content }}</textarea>
                                    </div>
                                    <div class="flex items-center justify-between pt-2">
                                        <div class="flex items-center gap-4">
                                            <label class="flex items-center gap-1.5 cursor-pointer text-xs font-bold text-slate-700">
                                                <input type="checkbox" name="is_published" value="1" {{ $page->is_published ? 'checked' : '' }} class="w-4 h-4 text-emerald-600 rounded">
                                                <span>Published</span>
                                            </label>
                                            <label class="flex items-center gap-1.5 cursor-pointer text-xs font-bold text-blue-700">
                                                <input type="checkbox" name="show_in_nav" value="1" {{ $page->show_in_nav ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                                                <span>Show in Header Nav</span>
                                            </label>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="openEdit = false" class="px-4 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100">
                                                Cancel
                                            </button>
                                            <button type="submit" class="px-4 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-lg text-xs shadow">
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
