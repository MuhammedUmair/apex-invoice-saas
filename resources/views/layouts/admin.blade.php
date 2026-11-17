<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Super Admin Control Centre' }} - Invoicy Platform</title>

    <!-- Google Fonts & Phosphor Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c',
                        },
                        admin: {
                            navy: '#0b132b',
                            dark: '#1c2541',
                            accent: '#f59e0b',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }

        /* Light Theme Overrides */
        body.theme-light {
            background-color: #f8fafc !important;
            color: #0f172a !important;
        }
        body.theme-light aside {
            background-color: #ffffff !important;
            border-color: #e2e8f0 !important;
        }
        body.theme-light aside .border-slate-800 {
            border-color: #f1f5f9 !important;
        }
        body.theme-light header {
            background-color: #ffffff !important;
            border-color: #e2e8f0 !important;
        }
        body.theme-light main {
            background-color: #f8fafc !important;
        }
        body.theme-light .bg-slate-950 {
            background-color: #f8fafc !important;
            color: #0f172a !important;
        }
        body.theme-light .bg-slate-900 {
            background-color: #ffffff !important;
            border-color: #e2e8f0 !important;
            color: #0f172a !important;
        }
        body.theme-light .bg-slate-800 {
            background-color: #f1f5f9 !important;
            border-color: #cbd5e1 !important;
            color: #1e293b !important;
        }
        body.theme-light .text-white {
            color: #0f172a !important;
        }
        body.theme-light .text-slate-100, body.theme-light .text-slate-200, body.theme-light .text-slate-300 {
            color: #334155 !important;
        }
        body.theme-light .text-slate-400, body.theme-light .text-slate-500 {
            color: #64748b !important;
        }
        body.theme-light .border-slate-800, body.theme-light .border-slate-700 {
            border-color: #e2e8f0 !important;
        }
        body.theme-light input, body.theme-light select, body.theme-light textarea {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border-color: #cbd5e1 !important;
        }
        body.theme-light table thead {
            background-color: #f1f5f9 !important;
            color: #475569 !important;
        }
        body.theme-light table tbody tr:hover {
            background-color: #f8fafc !important;
        }
    </style>
</head>
<body class="h-full font-sans antialiased bg-slate-950 text-slate-100 flex flex-col selection:bg-amber-500 selection:text-slate-950"
      :class="theme === 'light' ? 'theme-light' : ''"
      x-data="{ 
          sidebarOpen: false,
          sidebarCollapsed: localStorage.getItem('admin_sidebar_collapsed') === 'true',
          theme: localStorage.getItem('admin_theme') || 'dark',
          toggleSidebar() {
              this.sidebarCollapsed = !this.sidebarCollapsed;
              localStorage.setItem('admin_sidebar_collapsed', this.sidebarCollapsed);
          },
          toggleTheme() {
              this.theme = this.theme === 'dark' ? 'light' : 'dark';
              localStorage.setItem('admin_theme', this.theme);
              if (this.theme === 'light') {
                  document.body.classList.add('theme-light');
              } else {
                  document.body.classList.remove('theme-light');
              }
          },
          init() {
              if (this.theme === 'light') {
                  document.body.classList.add('theme-light');
              }
          }
      }">

    <!-- Persistent Assisted Session / Impersonation Banner (Section 11) -->
    @if(session('super_admin_assisted_session'))
    <div class="bg-gradient-to-r from-amber-600 via-rose-600 to-amber-700 text-white px-4 py-2 text-xs font-bold flex items-center justify-between shadow-lg sticky top-0 z-50">
        <div class="flex items-center gap-2">
            <span class="animate-pulse bg-white text-rose-700 px-2 py-0.5 rounded text-[10px] uppercase font-black tracking-wider">
                Privileged Support Mode
            </span>
            <span>
                Assisted Session Active: Viewing workspace <strong>{{ session('assisted_workspace_name') }}</strong> (Operator: {{ session('assisted_operator_email') }})
            </span>
        </div>
        <form method="POST" action="{{ route('admin.impersonate.stop') }}" class="inline">
            @csrf
            <button type="submit" class="px-3 py-1 bg-white hover:bg-slate-100 text-slate-900 rounded font-bold transition flex items-center gap-1 shadow-xs">
                <i class="ph ph-sign-out font-bold"></i> Exit Assisted Session
            </button>
        </form>
    </div>
    @endif

    <div class="flex h-full flex-1 overflow-hidden">
        <!-- Sidebar Navigation -->
        <aside :class="sidebarCollapsed ? 'w-20' : 'w-72'"
               class="bg-slate-900 border-r border-slate-800 shrink-0 flex flex-col transition-all duration-300 ease-in-out z-40 select-none">
            
            <!-- Branding Header -->
            <div class="h-16 flex items-center justify-between px-4 border-b border-slate-800">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 overflow-hidden" title="Super Admin Control Centre">
                    @php
                        $adminLogo = \App\Models\CmsContent::getLoginLogo();
                    @endphp
                    @if($adminLogo)
                        <div class="w-10 h-10 rounded-xl bg-slate-800/80 p-1.5 flex items-center justify-center shrink-0 border border-slate-700">
                            <img src="{{ $adminLogo }}" alt="RedMaple" class="max-h-full max-w-full object-contain">
                        </div>
                    @else
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-red-600 via-rose-600 to-red-700 p-2 flex items-center justify-center text-white font-black shadow-md shrink-0">
                            <!-- Official Canadian Red Maple Leaf Vector -->
                            <svg class="w-full h-full text-white drop-shadow-sm" viewBox="0 0 900 900" fill="currentColor">
                                <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                            </svg>
                        </div>
                    @endif
                    <div x-show="!sidebarCollapsed" class="transition-opacity duration-200">
                        <div class="font-black text-sm tracking-tight text-white flex items-center gap-1">
                            <span class="text-red-500 font-black">REDMAPLE</span> <span class="font-black tracking-tight text-white">INVOICY</span>
                        </div>
                        <div class="text-[10px] font-semibold text-slate-400 tracking-wider uppercase flex items-center gap-1.5">
                            <span>Control Centre</span>
                            <span class="px-1 py-0.2 rounded text-[8px] bg-red-600 text-white font-black uppercase">SAAS</span>
                        </div>
                    </div>
                </a>
                <button @click="toggleSidebar()" type="button" class="text-slate-400 hover:text-white p-1 rounded-lg transition cursor-pointer" title="Toggle Sidebar">
                    <i :class="sidebarCollapsed ? 'ph ph-arrow-line-right' : 'ph ph-arrow-line-left'" class="text-lg"></i>
                </button>
            </div>

            <!-- Navigation Links Scroll Area -->
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6 text-xs">
                
                <!-- GROUP 1: OVERVIEW -->
                <div class="space-y-1">
                    <div x-show="!sidebarCollapsed" class="px-3 pb-1 text-[10px] font-bold tracking-wider uppercase text-slate-400">
                        Overview
                    </div>
                    <a href="{{ route('admin.dashboard') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'Dashboard' : ''">
                        <i class="ph ph-squares-four text-lg shrink-0 {{ request()->routeIs('admin.dashboard') ? 'text-slate-950' : 'text-amber-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Dashboard</span>
                    </a>
                    <a href="{{ route('admin.analytics') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.analytics') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'Analytics & Reports' : ''">
                        <i class="ph ph-chart-line-up text-lg shrink-0 {{ request()->routeIs('admin.analytics') ? 'text-slate-950' : 'text-emerald-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Analytics & Reports</span>
                    </a>
                </div>

                <!-- GROUP 2: CUSTOMER MANAGEMENT -->
                <div class="space-y-1">
                    <div x-show="!sidebarCollapsed" class="px-3 pb-1 text-[10px] font-bold tracking-wider uppercase text-slate-400">
                        Customer Management
                    </div>
                    <a href="{{ route('admin.workspaces.index') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.workspaces.*') && !request()->routeIs('admin.workspaces.suspended') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'Workspaces / Tenants' : ''">
                        <i class="ph ph-buildings text-lg shrink-0 {{ request()->routeIs('admin.workspaces.*') && !request()->routeIs('admin.workspaces.suspended') ? 'text-slate-950' : 'text-sky-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Workspaces / Tenants</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.users.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'All Users' : ''">
                        <i class="ph ph-users-three text-lg shrink-0 {{ request()->routeIs('admin.users.*') ? 'text-slate-950' : 'text-indigo-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">All Users</span>
                    </a>
                    <a href="{{ route('admin.workspaces.suspended') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.workspaces.suspended') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'Suspended Accounts' : ''">
                        <i class="ph ph-prohibit text-lg shrink-0 {{ request()->routeIs('admin.workspaces.suspended') ? 'text-slate-950' : 'text-rose-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Suspended Accounts</span>
                    </a>
                </div>

                <!-- GROUP 3: SUBSCRIPTION MANAGEMENT -->
                <div class="space-y-1">
                    <div x-show="!sidebarCollapsed" class="px-3 pb-1 text-[10px] font-bold tracking-wider uppercase text-slate-400">
                        Subscriptions & Plans
                    </div>
                    <a href="{{ route('admin.subscriptions.index') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.subscriptions.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'Plans & Subscriptions' : ''">
                        <i class="ph ph-crown text-lg shrink-0 {{ request()->routeIs('admin.subscriptions.*') ? 'text-slate-950' : 'text-amber-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Plans & Subscriptions</span>
                    </a>
                    <a href="{{ route('admin.coupons.index') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.coupons.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'Coupons & Discounts' : ''">
                        <i class="ph ph-tag text-lg shrink-0 {{ request()->routeIs('admin.coupons.*') ? 'text-slate-950' : 'text-teal-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Coupons & Discounts</span>
                    </a>
                </div>

                <!-- GROUP 4: FINANCIAL MANAGEMENT -->
                <div class="space-y-1">
                    <div x-show="!sidebarCollapsed" class="px-3 pb-1 text-[10px] font-bold tracking-wider uppercase text-slate-400">
                        Financial Management
                    </div>
                    <a href="{{ route('admin.finance.revenue') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.finance.revenue') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'SaaS Revenue' : ''">
                        <i class="ph ph-currency-dollar text-lg shrink-0 {{ request()->routeIs('admin.finance.revenue') ? 'text-slate-950' : 'text-emerald-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">SaaS Revenue</span>
                    </a>
                    <a href="{{ route('admin.finance.transactions') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.finance.transactions') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'Payment Transactions' : ''">
                        <i class="ph ph-receipt text-lg shrink-0 {{ request()->routeIs('admin.finance.transactions') ? 'text-slate-950' : 'text-cyan-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Payment Transactions</span>
                    </a>
                    <a href="{{ route('admin.finance.failed_payments') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.finance.failed_payments') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'Failed Payments' : ''">
                        <i class="ph ph-x-circle text-lg shrink-0 {{ request()->routeIs('admin.finance.failed_payments') ? 'text-slate-950' : 'text-rose-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Failed Payments</span>
                    </a>
                    <a href="{{ route('admin.finance.refunds') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.finance.refunds') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'Refunds' : ''">
                        <i class="ph ph-arrow-counter-clockwise text-lg shrink-0 {{ request()->routeIs('admin.finance.refunds') ? 'text-slate-950' : 'text-amber-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Refunds</span>
                    </a>
                    <a href="{{ route('admin.finance.invoices') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.finance.invoices') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'Subscription Invoices' : ''">
                        <i class="ph ph-file-text text-lg shrink-0 {{ request()->routeIs('admin.finance.invoices') ? 'text-slate-950' : 'text-slate-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Subscription Invoices</span>
                    </a>
                    <a href="{{ route('admin.finance.tax_reports') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.finance.tax_reports') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'Tax & Revenue Reports' : ''">
                        <i class="ph ph-calculator text-lg shrink-0 {{ request()->routeIs('admin.finance.tax_reports') ? 'text-slate-950' : 'text-purple-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Tax & Reports</span>
                    </a>
                </div>

                <!-- GROUP 5: PLATFORM MANAGEMENT -->
                <div class="space-y-1">
                    <div x-show="!sidebarCollapsed" class="px-3 pb-1 text-[10px] font-bold tracking-wider uppercase text-slate-400">
                        Platform Management
                    </div>
                    <a href="{{ route('admin.platform.features') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.platform.features') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'Feature Management' : ''">
                        <i class="ph ph-sliders text-lg shrink-0 {{ request()->routeIs('admin.platform.features') ? 'text-slate-950' : 'text-blue-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Feature Management</span>
                    </a>
                    <a href="{{ route('admin.platform.usage') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.platform.usage') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'Usage & Limits' : ''">
                        <i class="ph ph-gauge text-lg shrink-0 {{ request()->routeIs('admin.platform.usage') ? 'text-slate-950' : 'text-emerald-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Usage & Limits</span>
                    </a>
                    <a href="{{ route('admin.platform.announcements') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.platform.announcements') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'System Announcements' : ''">
                        <i class="ph ph-megaphone text-lg shrink-0 {{ request()->routeIs('admin.platform.announcements') ? 'text-slate-950' : 'text-amber-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Announcements</span>
                    </a>
                    <a href="{{ route('cms.index') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('cms.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'Documentation & CMS' : ''">
                        <i class="ph ph-book-open text-lg shrink-0 {{ request()->routeIs('cms.*') ? 'text-slate-950' : 'text-sky-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Docs CMS</span>
                    </a>
                    <a href="{{ route('builder.index') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('builder.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'Website Builder' : ''">
                        <i class="ph ph-paint-brush text-lg shrink-0 {{ request()->routeIs('builder.*') ? 'text-slate-950' : 'text-pink-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Website Builder</span>
                    </a>
                    <a href="{{ route('admin.ads.index') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.ads.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'Advertisements' : ''">
                        <i class="ph ph-broadcast text-lg shrink-0 {{ request()->routeIs('admin.ads.*') ? 'text-slate-950' : 'text-violet-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Advertisements</span>
                    </a>
                </div>

                <!-- GROUP 6: SUPPORT & MONITORING -->
                <div class="space-y-1">
                    <div x-show="!sidebarCollapsed" class="px-3 pb-1 text-[10px] font-bold tracking-wider uppercase text-slate-400">
                        Support & Health
                    </div>
                    <a href="{{ route('admin.support.tickets') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.support.tickets') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'Support Tickets' : ''">
                        <i class="ph ph-ticket text-lg shrink-0 {{ request()->routeIs('admin.support.tickets') ? 'text-slate-950' : 'text-cyan-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Support Tickets</span>
                    </a>
                    <a href="{{ route('admin.support.feedback') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.support.feedback') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'User Feedback' : ''">
                        <i class="ph ph-chat-centered-text text-lg shrink-0 {{ request()->routeIs('admin.support.feedback') ? 'text-slate-950' : 'text-amber-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">User Feedback</span>
                    </a>
                    <a href="{{ route('admin.monitoring.health') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.monitoring.health') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'System Health' : ''">
                        <i class="ph ph-heartbeat text-lg shrink-0 {{ request()->routeIs('admin.monitoring.health') ? 'text-slate-950' : 'text-emerald-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">System Health</span>
                    </a>
                    <a href="{{ route('admin.monitoring.errors') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.monitoring.errors') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'Error Logs' : ''">
                        <i class="ph ph-warning-circle text-lg shrink-0 {{ request()->routeIs('admin.monitoring.errors') ? 'text-slate-950' : 'text-rose-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Error Logs</span>
                    </a>
                </div>

                <!-- GROUP 7: SECURITY -->
                <div class="space-y-1">
                    <div x-show="!sidebarCollapsed" class="px-3 pb-1 text-[10px] font-bold tracking-wider uppercase text-slate-400">
                        Security & Audit
                    </div>
                    <a href="{{ route('admin.security.admins') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.security.admins') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'Admin Users & Roles' : ''">
                        <i class="ph ph-shield-star text-lg shrink-0 {{ request()->routeIs('admin.security.admins') ? 'text-slate-950' : 'text-amber-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Admin Users & Roles</span>
                    </a>
                    <a href="{{ route('admin.security.logins') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.security.logins') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'Login History' : ''">
                        <i class="ph ph-sign-in text-lg shrink-0 {{ request()->routeIs('admin.security.logins') ? 'text-slate-950' : 'text-blue-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Login History</span>
                    </a>
                    <a href="{{ route('admin.security.audit_logs') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.security.audit_logs') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'Audit Logs' : ''">
                        <i class="ph ph-fingerprint text-lg shrink-0 {{ request()->routeIs('admin.security.audit_logs') ? 'text-slate-950' : 'text-violet-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Audit Logs</span>
                    </a>
                    <a href="{{ route('admin.security.events') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.security.events') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'Security Events & Assisted Access' : ''">
                        <i class="ph ph-lock-key text-lg shrink-0 {{ request()->routeIs('admin.security.events') ? 'text-slate-950' : 'text-rose-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Security Events</span>
                    </a>
                </div>

                <!-- GROUP 8: CONFIGURATION -->
                <div class="space-y-1">
                    <div x-show="!sidebarCollapsed" class="px-3 pb-1 text-[10px] font-bold tracking-wider uppercase text-slate-400">
                        Configuration
                    </div>
                    <a href="{{ route('admin.config.general') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.config.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                       :title="sidebarCollapsed ? 'System Configuration' : ''">
                        <i class="ph ph-gear text-lg shrink-0 {{ request()->routeIs('admin.config.*') ? 'text-slate-950' : 'text-slate-400' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">System Settings</span>
                    </a>
                </div>
            </nav>

            <!-- Bottom Operator Badge -->
            <div class="p-3 border-t border-slate-800 bg-slate-950/60">
                <div class="flex items-center justify-between" :class="sidebarCollapsed ? 'justify-center flex-col gap-2' : ''">
                    <div class="flex items-center gap-2.5 overflow-hidden">
                        <div class="w-8 h-8 rounded-full bg-amber-500 text-slate-950 font-black flex items-center justify-center text-xs shrink-0 shadow-xs">
                            {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                        </div>
                        <div class="text-xs truncate" x-show="!sidebarCollapsed">
                            <div class="font-bold text-white truncate max-w-[130px]">{{ auth()->user()->name }}</div>
                            <div class="text-[10px] text-amber-400 font-semibold uppercase tracking-wider flex items-center gap-1">
                                <i class="ph ph-shield-check text-xs"></i> {{ auth()->user()->getAdminRoleLabel() }}
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('admin.logout') }}" onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();"
                       class="text-slate-400 hover:text-rose-400 p-1.5 transition" title="Sign Out">
                        <i class="ph ph-sign-out text-base"></i>
                    </a>
                    <form id="admin-logout-form" method="POST" action="{{ route('admin.logout') }}" class="hidden">
                        @csrf
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Body Area -->
        <main class="flex-1 flex flex-col overflow-y-auto bg-slate-950">
            <!-- Super Admin Topbar -->
            <header class="h-16 bg-slate-900 border-b border-slate-800 px-6 sm:px-8 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-base font-bold text-white flex items-center gap-2">
                            <span>{{ $header ?? 'Platform Control Centre' }}</span>
                        </h2>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                            <i class="ph ph-shield-check text-xs"></i> Invoicy Platform Authority
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Quick System Health Status Pill -->
                    <div class="hidden md:flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800 border border-slate-700/80 text-xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-slate-300 font-medium">All Services Normal</span>
                    </div>

                    <!-- 2FA Verified Badge -->
                    <div class="hidden sm:flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold">
                        <i class="ph ph-lock-key-open"></i> 2FA Secured
                    </div>

                    <!-- Theme Switcher (Light / Dark) -->
                    <button @click="toggleTheme()" type="button" 
                            class="p-2 rounded-xl border border-slate-700 bg-slate-800 hover:bg-slate-700 text-amber-400 transition flex items-center justify-center cursor-pointer shadow-xs"
                            :title="theme === 'dark' ? 'Switch to Light Theme' : 'Switch to Dark Theme'">
                        <i class="ph text-base" :class="theme === 'dark' ? 'ph-sun' : 'ph-moon'"></i>
                    </button>

                    <!-- Switch to Tenant Workspace Link -->
                    <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-xl border border-slate-700 bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-200 transition flex items-center gap-1.5">
                        <i class="ph ph-buildings"></i> Tenant App
                    </a>

                    <!-- Operator Profile Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 focus:outline-none">
                            <div class="w-8 h-8 rounded-full bg-amber-500 text-slate-950 font-bold flex items-center justify-center text-xs">
                                {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                            </div>
                            <i class="ph ph-caret-down text-slate-400 text-xs"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute right-0 mt-2 w-56 rounded-xl bg-slate-900 border border-slate-800 py-2 shadow-2xl z-50 text-xs">
                            <div class="px-4 py-2 border-b border-slate-800">
                                <div class="font-bold text-white">{{ auth()->user()->name }}</div>
                                <div class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email }}</div>
                            </div>
                            <a href="{{ route('admin.security.admins') }}" class="block px-4 py-2 text-slate-300 hover:bg-slate-800 hover:text-white transition">
                                <i class="ph ph-shield-star mr-2 text-amber-400"></i> Admin Roles & Access
                            </a>
                            <a href="{{ route('admin.security.audit_logs') }}" class="block px-4 py-2 text-slate-300 hover:bg-slate-800 hover:text-white transition">
                                <i class="ph ph-fingerprint mr-2 text-violet-400"></i> Platform Audit Trail
                            </a>
                            <div class="border-t border-slate-800 my-1"></div>
                            <a href="{{ route('admin.logout') }}" onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();"
                               class="block px-4 py-2 text-rose-400 hover:bg-rose-500/10 transition">
                                <i class="ph ph-sign-out mr-2"></i> Sign Out
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Flash Notifications -->
            <div class="px-6 sm:px-8 pt-4">
                @if(session('success'))
                    <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs font-semibold flex items-center justify-between mb-4 shadow-sm" x-data="{ show: true }" x-show="show">
                        <div class="flex items-center gap-2">
                            <i class="ph ph-check-circle text-lg shrink-0"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" class="text-emerald-400 hover:text-white"><i class="ph ph-x"></i></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-semibold flex items-center justify-between mb-4 shadow-sm" x-data="{ show: true }" x-show="show">
                        <div class="flex items-center gap-2">
                            <i class="ph ph-warning-octagon text-lg shrink-0"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button @click="show = false" class="text-rose-400 hover:text-white"><i class="ph ph-x"></i></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-semibold mb-4 shadow-sm">
                        <div class="font-bold flex items-center gap-2 mb-1">
                            <i class="ph ph-warning"></i> Action could not be processed:
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 text-[11px] text-rose-400">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Page Content -->
            <div class="flex-1 p-6 sm:p-8 space-y-8">
                @yield('content')
            </div>

            <!-- Super Admin Footer -->
            <footer class="mt-auto border-t border-slate-900 bg-slate-950/80 px-6 sm:px-8 py-4 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2">
                <div>
                    Invoicy SaaS Platform &bull; RedMaple Studio &copy; {{ date('Y') }}. High-security control panel.
                </div>
                <div class="flex items-center gap-4 text-[11px]">
                    <span class="text-slate-400">Version 2.4.0-PRO</span>
                    <span class="text-emerald-400 flex items-center gap-1"><i class="ph ph-lock-key"></i> Fail-Closed Isolation</span>
                </div>
            </footer>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
