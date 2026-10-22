<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'RedMaple Invoice Generator' }} - Commercial SaaS Invoicing</title>
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
    @livewireStyles
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased">
    <div class="min-h-screen flex">
        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-slate-900 text-slate-200 flex flex-col shrink-0">
            <!-- Brand Header -->
            <div class="h-16 flex items-center px-5 border-b border-slate-800 gap-3">
                <div class="w-9 h-9 rounded-xl bg-red-600/10 border border-red-500/20 flex items-center justify-center p-1.5 shrink-0 shadow-sm">
                    <!-- Red Maple Leaf Vector -->
                    <svg class="w-full h-full text-red-600 drop-shadow-sm" viewBox="0 0 900 900" fill="currentColor">
                        <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                    </svg>
                </div>
                <div>
                    <h1 class="font-black text-white text-sm tracking-tight flex items-center gap-1">
                        Red<span class="text-red-500">Maple</span>
                    </h1>
                    <p class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Invoice Generator</p>
                </div>
            </div>

            <!-- Nav Links -->
            <nav class="flex-1 px-4 py-6 space-y-1">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-red-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="ph ph-squares-four text-lg"></i>
                    Dashboard
                </a>

                <a href="{{ route('invoices.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('invoices.*') ? 'bg-red-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="ph ph-receipt text-lg"></i>
                    Invoices
                </a>

                <a href="{{ route('clients.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('clients.*') ? 'bg-red-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="ph ph-users text-lg"></i>
                    Client CRM
                </a>

                <a href="{{ route('services.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('services.*') ? 'bg-red-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="ph ph-package text-lg"></i>
                    Catalog & Presets
                </a>

                <a href="{{ route('membership.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('membership.*') ? 'bg-red-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="ph ph-crown text-lg text-amber-400"></i>
                    Plan & Billing
                </a>
            </nav>

            <!-- Bottom User Profile & Sign Out -->
            <div class="p-4 border-t border-slate-800">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-xs font-bold text-white">
                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="text-xs">
                            <p class="font-medium text-white truncate max-w-[100px]">{{ auth()->user()->name }}</p>
                            <p class="text-slate-400 capitalize">{{ auth()->user()->role }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-slate-400 hover:text-rose-400 p-1.5 transition" title="Logout">
                            <i class="ph ph-sign-out text-lg"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col overflow-y-auto">
            <!-- Top Header -->
            <header class="h-16 bg-white border-b border-slate-200 px-8 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <h2 class="text-lg font-bold text-slate-800">{{ $header ?? 'Overview' }}</h2>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('invoices.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg shadow-sm transition">
                        <i class="ph ph-plus-circle text-base"></i>
                        Create Invoice
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
