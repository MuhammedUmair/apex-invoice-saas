@php
    $currentLocale = app()->getLocale();
    $isRtl = in_array($currentLocale, ['ur', 'ar']);
    $localesList = [
        'en' => ['name' => 'English', 'native' => 'English', 'flag' => '🇺🇸'],
        'ur' => ['name' => 'Urdu', 'native' => 'اردو', 'flag' => '🇵🇰'],
        'hi' => ['name' => 'Hindi', 'native' => 'हिन्दी', 'flag' => '🇮🇳'],
        'tr' => ['name' => 'Turkish', 'native' => 'Türkçe', 'flag' => '🇹🇷'],
        'ar' => ['name' => 'Arabic', 'native' => 'العربية', 'flag' => '🇦🇪'],
        'es' => ['name' => 'Spanish', 'native' => 'Español', 'flag' => '🇪🇸'],
        'fr' => ['name' => 'French', 'native' => 'Français', 'flag' => '🇫🇷'],
        'de' => ['name' => 'German', 'native' => 'Deutsch', 'flag' => '🇩🇪'],
    ];
    $workspace = auth()->check() ? auth()->user()->workspace : null;
    $customLogo = $workspace->logo_url ?? null;
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLocale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Invoicy by RedMaple' }} - Commercial SaaS Invoicing</title>
    <!-- Favicon with Red Maple Leaf -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/maple-leaf.svg') }}">
    <!-- Tailwind CSS CDN for zero-build Hostinger deployment -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            200: '#fecaca',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c',
                            800: '#991b1b',
                            900: '#7f1d1d',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Phosphor Icons CDN -->
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <style>
        [dir="rtl"] { text-align: right; }
        [dir="rtl"] .ltr-text { direction: ltr; text-align: left; }
    </style>
    @livewireStyles
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased">
    <div class="min-h-screen flex" x-data="{ sidebarCollapsed: localStorage.getItem('sidebar_collapsed') === 'true' }">
        <!-- Sidebar Navigation (Expand/Collapse Supported) -->
        <aside :class="sidebarCollapsed ? 'w-20' : 'w-64'" class="bg-slate-900 text-slate-200 flex flex-col shrink-0 transition-all duration-300 relative">
            <!-- Brand Header -->
            <div class="h-16 flex items-center px-4 border-b border-slate-800 justify-between gap-2 overflow-hidden">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-xl bg-red-600/10 border border-red-500/20 flex items-center justify-center p-1.5 shrink-0 shadow-sm overflow-hidden">
                        @if($customLogo)
                            <img src="{{ $customLogo }}" alt="Logo" class="w-full h-full object-contain">
                        @else
                            <!-- Red Maple Leaf Vector -->
                            <svg class="w-full h-full text-red-600 drop-shadow-sm" viewBox="0 0 900 900" fill="currentColor">
                                <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                            </svg>
                        @endif
                    </div>
                    <div x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms class="truncate">
                        <h1 class="font-black text-white text-sm tracking-tight flex items-center gap-1">
                            Invoicy <span class="text-xs font-normal text-slate-400">by</span> <span class="text-red-500 font-bold">RedMaple</span>
                        </h1>
                        <p class="text-[10px] uppercase font-bold tracking-wider text-slate-400">SaaS Suite</p>
                    </div>
                </div>

                <!-- Sidebar Collapse / Expand Toggle Button -->
                <button @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('sidebar_collapsed', sidebarCollapsed)"
                        type="button"
                        class="text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-800 transition shrink-0"
                        :title="sidebarCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'">
                    <i :class="sidebarCollapsed ? 'ph-caret-right' : 'ph-caret-left'" class="ph text-base font-bold"></i>
                </button>
            </div>

            <!-- Nav Links -->
            <nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto">
                <a href="{{ route('dashboard') }}" 
                   :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'gap-3 px-3 py-2'"
                   class="flex items-center rounded-lg text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-red-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                   :title="sidebarCollapsed ? '{{ __('Dashboard') }}' : ''">
                    <i class="ph ph-squares-four text-lg shrink-0"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">{{ __('Dashboard') }}</span>
                </a>

                <a href="{{ route('invoices.index') }}" 
                   :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'gap-3 px-3 py-2'"
                   class="flex items-center rounded-lg text-sm font-medium transition {{ request()->routeIs('invoices.*') ? 'bg-red-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                   :title="sidebarCollapsed ? '{{ __('Invoices') }}' : ''">
                    <i class="ph ph-receipt text-lg shrink-0"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">{{ __('Invoices') }}</span>
                </a>

                <a href="{{ route('recurring.index') }}" 
                   :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'gap-3 px-3 py-2'"
                   class="flex items-center rounded-lg text-sm font-medium transition {{ request()->routeIs('recurring.*') ? 'bg-red-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                   :title="sidebarCollapsed ? '{{ __('Recurring Retainers') }}' : ''">
                    <i class="ph ph-arrows-clockwise text-lg text-cyan-400 shrink-0"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">{{ __('Recurring Retainers') }}</span>
                </a>

                <a href="{{ route('credit-notes.index') }}" 
                   :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'gap-3 px-3 py-2'"
                   class="flex items-center rounded-lg text-sm font-medium transition {{ request()->routeIs('credit-notes.*') ? 'bg-red-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                   :title="sidebarCollapsed ? '{{ __('Credit Notes') }}' : ''">
                    <i class="ph ph-arrow-u-up-left text-lg text-rose-400 shrink-0"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">{{ __('Credit Notes') }}</span>
                </a>

                <a href="{{ route('reminders.index') }}" 
                   :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'gap-3 px-3 py-2'"
                   class="flex items-center rounded-lg text-sm font-medium transition {{ request()->routeIs('reminders.*') ? 'bg-red-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                   :title="sidebarCollapsed ? '{{ __('Reminders & Scheduler') }}' : ''">
                    <i class="ph ph-bell-ringing text-lg text-amber-400 shrink-0"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">{{ __('Reminders & Scheduler') }}</span>
                </a>

                <a href="{{ route('clients.index') }}" 
                   :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'gap-3 px-3 py-2'"
                   class="flex items-center rounded-lg text-sm font-medium transition {{ request()->routeIs('clients.*') ? 'bg-red-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                   :title="sidebarCollapsed ? '{{ __('Client CRM') }}' : ''">
                    <i class="ph ph-users text-lg shrink-0"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">{{ __('Client CRM') }}</span>
                </a>

                <a href="{{ route('services.index') }}" 
                   :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'gap-3 px-3 py-2'"
                   class="flex items-center rounded-lg text-sm font-medium transition {{ request()->routeIs('services.*') ? 'bg-red-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                   :title="sidebarCollapsed ? '{{ __('Catalog & Presets') }}' : ''">
                    <i class="ph ph-package text-lg shrink-0"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">{{ __('Catalog & Presets') }}</span>
                </a>

                <a href="{{ route('membership.index') }}" 
                   :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'gap-3 px-3 py-2'"
                   class="flex items-center rounded-lg text-sm font-medium transition {{ request()->routeIs('membership.*') ? 'bg-red-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                   :title="sidebarCollapsed ? '{{ __('Plan & Billing') }}' : ''">
                    <i class="ph ph-crown text-lg text-amber-400 shrink-0"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">{{ __('Plan & Billing') }}</span>
                </a>

                <div class="pt-3 pb-1 border-t border-slate-800/80 my-2">
                    <span x-show="!sidebarCollapsed" class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">System & Admin</span>
                </div>

                @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
                <a href="{{ route('cms.index') }}" 
                   :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'gap-3 px-3 py-2'"
                   class="flex items-center rounded-lg text-sm font-medium transition {{ request()->routeIs('cms.*') ? 'bg-red-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                   :title="sidebarCollapsed ? '{{ __('Website & Docs CMS') }}' : ''">
                    <i class="ph ph-note-pencil text-lg text-emerald-400 shrink-0"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">{{ __('Website & Docs CMS') }}</span>
                </a>

                <a href="{{ route('admin.subscriptions.index') }}" 
                   :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'gap-3 px-3 py-2'"
                   class="flex items-center rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.subscriptions.*') ? 'bg-red-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                   :title="sidebarCollapsed ? '{{ __('SaaS Subscriptions') }}' : ''">
                    <i class="ph ph-shield-check text-lg text-amber-400 shrink-0"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">{{ __('SaaS Subscriptions') }}</span>
                </a>

                <a href="{{ route('admin.coupons.index') }}" 
                   :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'gap-3 px-3 py-2'"
                   class="flex items-center rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.coupons.*') ? 'bg-red-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                   :title="sidebarCollapsed ? '{{ __('Coupons & Offers') }}' : ''">
                    <i class="ph ph-ticket text-lg text-fuchsia-400 shrink-0"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">{{ __('Coupons & Offers') }}</span>
                </a>
                @endif

                <a href="{{ route('docs.index') }}" target="_blank" 
                   :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'gap-3 px-3 py-2'"
                   class="flex items-center rounded-lg text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition"
                   :title="sidebarCollapsed ? '{{ __('Documentation') }}' : ''">
                    <i class="ph ph-book-open text-lg text-sky-400 shrink-0"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">{{ __('Documentation') }}</span>
                </a>

                <a href="{{ route('settings.index') }}" 
                   :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'gap-3 px-3 py-2'"
                   class="flex items-center rounded-lg text-sm font-medium transition {{ request()->routeIs('settings.*') ? 'bg-red-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                   :title="sidebarCollapsed ? '{{ __('Agency Settings') }}' : ''">
                    <i class="ph ph-gear text-lg text-slate-400 shrink-0"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">{{ __('Agency Settings') }}</span>
                </a>
            </nav>

            <!-- Advertisement Section on Left Sidebar (Item 2) -->
            <div class="px-3 py-2" x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>
                <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700/80 p-3 text-white shadow-xs">
                    <div class="flex items-center justify-between mb-1">
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-red-600/30 text-red-400 font-bold text-[9px] uppercase tracking-wider">
                            <i class="ph ph-sparkle text-amber-300"></i> PRO SUITE
                        </span>
                        <a href="https://redmapleagency.com" target="_blank" class="text-slate-400 hover:text-white text-[10px]" title="Visit RedMaple Studio">
                            <i class="ph ph-arrow-up-right text-xs"></i>
                        </a>
                    </div>
                    <h5 class="font-bold text-xs text-white leading-tight">
                        Grow with RedMaple Studio
                    </h5>
                    <p class="text-[10px] text-slate-400 mt-1 leading-snug">
                        Custom Apps, CRM & Dedicated Developer Pods for agencies.
                    </p>
                    <a href="{{ route('membership.index') }}" class="mt-2.5 w-full block text-center py-1.5 bg-red-600 hover:bg-red-700 text-white font-bold text-[11px] rounded-lg transition shadow-2xs">
                        Upgrade & Save 20%
                    </a>
                </div>
            </div>

            <!-- Compact Icon Ad for Collapsed State -->
            <div class="px-3 py-2 flex justify-center" x-show="sidebarCollapsed" x-cloak>
                <a href="{{ route('membership.index') }}" class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-600 to-rose-700 flex items-center justify-center text-white hover:scale-105 transition shadow-sm" title="Upgrade to Pro & Special Offers">
                    <i class="ph ph-crown text-lg text-amber-300"></i>
                </a>
            </div>

            <!-- Bottom User Profile & Sign Out -->
            <div class="p-4 border-t border-slate-800">
                <div class="flex items-center justify-between" :class="sidebarCollapsed ? 'justify-center flex-col gap-2' : ''">
                    <div class="flex items-center gap-2 overflow-hidden">
                        <div class="w-8 h-8 rounded-full bg-red-600 flex items-center justify-center text-xs font-bold text-white shadow-sm shrink-0">
                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="text-xs truncate" x-show="!sidebarCollapsed">
                            <p class="font-medium text-white truncate max-w-[100px]">{{ auth()->user()->name }}</p>
                            <p class="text-slate-400 capitalize">{{ auth()->user()->role }}</p>
                        </div>
                    </div>
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                       class="text-slate-400 hover:text-rose-400 p-1.5 transition cursor-pointer" title="{{ __('Sign Out') }}">
                        <i class="ph ph-sign-out text-lg"></i>
                    </a>
                    <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
                        @csrf
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col overflow-y-auto">
            <!-- Top Header -->
            <header class="h-16 bg-white border-b border-slate-200 px-6 sm:px-8 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <!-- Topbar Expand/Collapse Sidebar Toggle Button -->
                    <button @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('sidebar_collapsed', sidebarCollapsed)"
                            type="button"
                            class="p-2 rounded-xl border border-slate-200 text-slate-600 hover:text-red-600 hover:bg-slate-50 transition shadow-2xs"
                            title="Toggle Sidebar Expand / Collapse">
                        <i class="ph ph-sidebar-simple text-lg"></i>
                    </button>
                    <h2 class="text-lg font-bold text-slate-800">{{ $header ?? __('Overview') }}</h2>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Topbar Language Switcher Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-medium text-slate-700 transition">
                            <span>{{ $localesList[$currentLocale]['flag'] ?? '🌐' }}</span>
                            <span class="hidden sm:inline font-semibold">{{ $localesList[$currentLocale]['native'] ?? __('Language') }}</span>
                            <i class="ph ph-caret-down text-slate-400"></i>
                        </button>
                        <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-200 py-1.5 z-50">
                            <div class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('Switch App Language') }}</div>
                            @foreach($localesList as $code => $data)
                                <a href="{{ route('locale.switch', $code) }}" class="flex items-center justify-between px-3 py-1.5 text-xs hover:bg-red-50 hover:text-red-700 transition {{ $currentLocale === $code ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-700' }}">
                                    <span class="flex items-center gap-2">
                                        <span>{{ $data['flag'] }}</span>
                                        <span>{{ $data['name'] }}</span>
                                    </span>
                                    <span class="text-slate-400 text-[10px]">{{ $data['native'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Concurrency-Safe Ledger Lock Badge (Mockup) -->
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        <i class="ph ph-shield-check text-emerald-600 text-sm"></i> Concurrency-Safe Ledger Lock
                    </span>

                    <!-- Documentation Quick Link -->
                    <a href="{{ route('docs.index') }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition" title="{{ __('Documentation') }}">
                        <i class="ph ph-book-open text-sky-600 text-sm"></i>
                        <span class="hidden md:inline">{{ __('Documentation') }}</span>
                    </a>

                    <!-- Create Invoice Action -->
                    <a href="{{ route('invoices.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                        <i class="ph ph-plus-circle text-base"></i>
                        {{ __('Create Invoice') }}
                    </a>
                </div>
            </header>

            <!-- Notifications & Alert Banners -->
            <div class="px-8 pt-4">
                @if(session('success'))
                    <div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3">
                        <i class="ph ph-check-circle text-lg text-emerald-600"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if(session('error'))
                    <div class="p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-3">
                        <i class="ph ph-warning-circle text-lg text-rose-600"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
            </div>

            <!-- Page Body -->
            <div class="p-8">
                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </main>
    </div>

    @livewireScripts
</body>
</html>
