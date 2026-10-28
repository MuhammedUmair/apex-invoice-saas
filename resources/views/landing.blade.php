@php
    $websiteLogo = \App\Models\CmsContent::getWebsiteLogo();
    $faviconUrl = \App\Models\CmsContent::getFaviconUrl();
    $brandName = \App\Models\CmsContent::getVal('website_brand_name', 'Invoicy');
    $brandSub = \App\Models\CmsContent::getVal('website_brand_sub', 'RedMaple');
    $navItems = \App\Models\CmsContent::getNavItems();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $brandName }} by {{ $brandSub }} — Next-Generation Global Invoicing & Financial Accounting Suite</title>
    
    <!-- Meta tags for SEO & Social Cards -->
    <meta name="description" content="{{ $brandName }} by {{ $brandSub }} is an enterprise-grade global invoicing, automated payment reminders, multi-currency ledger, and client billing SaaS platform for modern agencies and businesses.">
    <!-- Dynamic Favicon -->
    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
    @else
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 900 900'><path fill='%23dc2626' d='M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z'/></svg>">
    @endif

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css"/>

    <style>
        .gradient-brand {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #7f1d1d 100%);
        }
        .hero-glow {
            background: radial-gradient(circle at 50% 30%, rgba(220, 38, 38, 0.15), transparent 70%);
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>
<body class="bg-white text-slate-800 antialiased selection:bg-red-500 selection:text-white">

    <!-- Top Announcement Bar -->
    <div class="bg-slate-900 text-white text-xs py-2.5 px-4 text-center font-medium border-b border-slate-800 flex items-center justify-center gap-2">
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-600 text-white uppercase tracking-wider">New</span>
        <span>Introducing <strong>Invoicy by RedMaple</strong> &bull; Automated Payment Reminders & Multi-Currency GAAP Accounting</span>
        <a href="{{ route('register') }}" class="underline hover:text-red-400 font-bold ml-1">Get Started Free &rarr;</a>
    </div>

    <!-- Navigation Header -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="/" class="flex items-center gap-3 group">
                @if($websiteLogo)
                    <div class="h-10 flex items-center">
                        <img src="{{ $websiteLogo }}" alt="{{ $brandName }}" class="max-h-10 max-w-[200px] object-contain">
                    </div>
                @else
                    <div class="w-10 h-10 rounded-xl bg-red-50 border border-red-200 flex items-center justify-center p-2 shadow-xs group-hover:scale-105 transition">
                        <svg class="w-full h-full text-red-600 drop-shadow-sm" viewBox="0 0 900 900" fill="currentColor">
                            <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-xl font-black text-slate-900 tracking-tight">{{ $brandName }} <span class="text-xs font-semibold text-slate-500">by</span> <span class="text-red-600">{{ $brandSub }}</span></span>
                        <span class="block text-[10px] uppercase font-bold tracking-widest text-slate-400">Enterprise SaaS Suite</span>
                    </div>
                @endif
            </a>

            <!-- Dynamic Navigation Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                @foreach($navItems as $nav)
                    <a href="{{ $nav['url'] }}" target="{{ $nav['target'] ?? '_self' }}" class="hover:text-red-600 transition">{{ $nav['label'] }}</a>
                @endforeach
            </nav>

            <!-- Actions -->
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold shadow-md shadow-red-500/20 transition">
                        <i class="ph ph-squares-four text-base"></i>
                        Open Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-bold text-slate-700 hover:text-red-600 px-3 py-2 transition">
                        Log In
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold shadow-md shadow-red-500/20 transition">
                        Get Started Free
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative pt-16 pb-24 overflow-hidden hero-glow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-red-50 border border-red-200 text-red-700 text-xs font-bold mb-8">
                <i class="ph ph-lightning text-sm text-red-600"></i>
                {{ \App\Models\CmsContent::getVal('hero_badge', 'SaaS Invoicing Engineered for Global Agility') }}
            </div>

            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-slate-900 tracking-tight max-w-5xl mx-auto leading-tight">
                {{ \App\Models\CmsContent::getVal('hero_title', 'Get Paid Faster. Automate Reminders. Invoice Worldwide.') }}
            </h1>

            <p class="mt-6 text-lg sm:text-xl text-slate-600 max-w-3xl mx-auto leading-relaxed">
                {{ \App\Models\CmsContent::getVal('hero_subtitle', 'Invoicy by RedMaple gives modern agencies, freelancers, and businesses real-time VAT calculations, automated milestone payment reminders (45, 30, 15, 7, 3, 2 days before due), password-protected client PDFs, and seamless one-click billing.') }}
            </p>

            <!-- CTA Buttons -->
            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-base shadow-lg shadow-red-600/30 transition transform hover:-translate-y-0.5">
                    <i class="ph ph-rocket-launch text-xl"></i>
                    {{ \App\Models\CmsContent::getVal('hero_cta_primary', 'Start Free with Invoicy') }}
                </a>
                <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-base shadow transition">
                    <i class="ph ph-arrow-square-out text-xl"></i>
                    {{ \App\Models\CmsContent::getVal('hero_cta_secondary', 'Launch App / Login') }}
                </a>
            </div>

            <!-- Trust Stats -->
            <div class="mt-14 grid grid-cols-2 md:grid-cols-4 gap-6 max-w-4xl mx-auto border-t border-slate-200/80 pt-10 text-left">
                <div>
                    <h3 class="text-3xl font-black text-slate-900">10+</h3>
                    <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mt-1">Global Currencies</p>
                </div>
                <div>
                    <h3 class="text-3xl font-black text-slate-900">6 Milestones</h3>
                    <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mt-1">Automated Reminders</p>
                </div>
                <div>
                    <h3 class="text-3xl font-black text-slate-900">100% GAAP</h3>
                    <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mt-1">Ledger Balancing</p>
                </div>
                <div>
                    <h3 class="text-3xl font-black text-slate-900">0% Friction</h3>
                    <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mt-1">Walk-in Billing</p>
                </div>
            </div>

            <!-- Hero Interactive Mockup -->
            <div class="mt-16 max-w-5xl mx-auto rounded-3xl border border-slate-300/80 shadow-2xl overflow-hidden bg-slate-900 text-left">
                <!-- Browser Bar -->
                <div class="h-11 bg-slate-800/90 px-4 flex items-center justify-between border-b border-slate-700">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                        <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                    </div>
                    <div class="text-[11px] font-mono text-slate-400 bg-slate-900 px-4 py-1 rounded-md border border-slate-700">
                        https://invoicy.redmapleagency.com/invoices/create
                    </div>
                    <div class="text-xs text-slate-400 flex items-center gap-1 font-semibold">
                        <i class="ph ph-shield-check text-emerald-400"></i> TLS Encrypted
                    </div>
                </div>

                <!-- Mockup Content -->
                <div class="p-8 sm:p-10 bg-slate-950 text-white space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-red-600/20 border border-red-500/30 flex items-center justify-center p-2">
                                <svg class="w-full h-full text-red-500" viewBox="0 0 900 900" fill="currentColor">
                                    <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-lg text-white">Invoice #INV-2026-0042</h4>
                                <p class="text-xs text-slate-400">Billed to: Acme Global Corp &bull; Due: Nov 15, 2026</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                45d, 30d, 15d, 7d Reminders Active
                            </span>
                        </div>
                    </div>

                    <!-- Items Preview -->
                    <div class="space-y-3">
                        <div class="flex justify-between items-center bg-slate-900 p-4 rounded-xl border border-slate-800">
                            <div>
                                <p class="font-semibold text-sm text-slate-200">Full-Stack SaaS Cloud Architecture</p>
                                <p class="text-xs text-slate-500">1 x Milestone 1 Sprint Delivery</p>
                            </div>
                            <span class="font-mono font-bold text-emerald-400 text-base">$3,500.00 USD</span>
                        </div>
                        <div class="flex justify-between items-center bg-slate-900 p-4 rounded-xl border border-slate-800">
                            <div>
                                <p class="font-semibold text-sm text-slate-200">Enterprise Database Cluster & Auto-Failover</p>
                                <p class="text-xs text-slate-500">Dedicated Setup & Hardening</p>
                            </div>
                            <span class="font-mono font-bold text-emerald-400 text-base">$1,200.00 USD</span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-800 flex justify-between items-center">
                        <span class="text-sm text-slate-400">Total Balance (Auto Calculated):</span>
                        <span class="text-3xl font-black text-red-500">$4,700.00 USD</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Core Features Grid -->
    <section id="features" class="py-24 bg-slate-50 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs font-bold uppercase tracking-wider text-red-600 mb-2">Designed For Demanding Businesses</h2>
                <h3 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Everything You Need for Enterprise Invoicing</h3>
                <p class="text-base text-slate-600 mt-3">From one-click generic billing to comprehensive audit trails and automated late-payment mitigation.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm card-hover transition">
                    <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 border border-red-200 flex items-center justify-center mb-5">
                        <i class="ph ph-bell-ringing text-2xl"></i>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-2">Multi-Tier Payment Reminders</h4>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Never chase late clients manually. Invoicy automatically triggers customer email reminders at 45, 30, 15, 7, 3, and 2 days before due date, simultaneously alerting you.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm card-hover transition">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 border border-blue-200 flex items-center justify-center mb-5">
                        <i class="ph ph-calendar-check text-2xl"></i>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-2">Future Invoice Scheduling</h4>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Plan your upcoming billing cycles in advance. Schedule future invoices today that auto-publish, notify the customer, and alert your finance desk on the exact date.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm card-hover transition">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center mb-5">
                        <i class="ph ph-globe text-2xl"></i>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-2">Real-Time Global Tax Engine</h4>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Instant compliant tax rules for US Sales Tax, UK VAT (20%), EU MwSt/TVA, Saudi ZATCA VAT (15%), UAE VAT (5%), Canada GST/HST, and India GST (18%).
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm card-hover transition">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 border border-purple-200 flex items-center justify-center mb-5">
                        <i class="ph ph-user-check text-2xl"></i>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-2">Walk-in & Quick Customers</h4>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Need to generate an invoice right now without opening a CRM record? Toggle Walk-in mode to type customer info on the fly and generate instantaneous documents.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm card-hover transition">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center mb-5">
                        <i class="ph ph-lock-key text-2xl"></i>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-2">Encrypted Customer PDFs</h4>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Protect sensitive commercial terms. Enable password encryption on customer invoice PDFs with salted cryptographic hashing to restrict unauthorized viewing.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm card-hover transition">
                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center mb-5">
                        <i class="ph ph-arrow-u-up-left text-2xl"></i>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-2">Credit Notes & Revision Logs</h4>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Maintain audit perfection. Revise draft invoices before sending, or issue partial and full Credit Notes with automated ledger balancing and reason tracking.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Future RedMaple SaaS Suite Roadmap -->
    <section id="saas-suite" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold mb-3">
                    <i class="ph ph-sparkle text-red-600"></i> The RedMaple Ecosystem
                </div>
                <h2 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Our Expanding SaaS Suite</h2>
                <p class="text-base text-slate-600 mt-3">
                    Invoicy is the foundational financial pillar of RedMaple Software Studio. We are engineering a cohesive suite of business applications to power every department of your enterprise.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- App 1: Invoicy (Live Now) -->
                <div class="p-8 rounded-2xl border-2 border-red-500 bg-red-50/20 relative shadow-md">
                    <span class="absolute top-4 right-4 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-600 text-white">
                        Available Now &bull; Live
                    </span>
                    <div class="w-12 h-12 rounded-xl bg-red-600 text-white flex items-center justify-center p-2 mb-4 shadow">
                        <svg class="w-full h-full text-white" viewBox="0 0 900 900" fill="currentColor">
                            <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-900">Invoicy by RedMaple</h3>
                    <p class="text-xs text-red-600 font-bold uppercase tracking-wider mt-0.5">Global Invoicing & Billing Engine</p>
                    <p class="text-sm text-slate-600 mt-3 leading-relaxed">
                        Multi-currency invoices, credit notes, customer portals, automated milestone reminders, and tax calculations.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('register') }}" class="text-xs font-bold text-red-600 hover:text-red-700 inline-flex items-center gap-1">
                            Launch Invoicy App &rarr;
                        </a>
                    </div>
                </div>

                <!-- App 2: RedMaple CRM Pro -->
                <div class="p-8 rounded-2xl border border-slate-200 bg-white relative card-hover transition">
                    <span class="absolute top-4 right-4 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600">
                        Coming Q1 2027
                    </span>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 border border-blue-200 flex items-center justify-center mb-4">
                        <i class="ph ph-users-three text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900">RedMaple CRM Pro</h3>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mt-0.5">Sales Pipeline & Deal Flow</p>
                    <p class="text-sm text-slate-600 mt-3 leading-relaxed">
                        Visual Kanban pipelines, deal stages, client activity tracking, email sync, and automated proposal-to-invoice conversion.
                    </p>
                </div>

                <!-- App 3: RedMaple HRMS & Payroll -->
                <div class="p-8 rounded-2xl border border-slate-200 bg-white relative card-hover transition">
                    <span class="absolute top-4 right-4 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600">
                        In Development
                    </span>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center mb-4">
                        <i class="ph ph-identification-card text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900">RedMaple HRMS</h3>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mt-0.5">Team & Payroll Management</p>
                    <p class="text-sm text-slate-600 mt-3 leading-relaxed">
                        Employee directories, attendance, vacation requests, digital payslips, and compliance withholding calculation.
                    </p>
                </div>

                <!-- App 4: RedMaple Project Desk -->
                <div class="p-8 rounded-2xl border border-slate-200 bg-white relative card-hover transition">
                    <span class="absolute top-4 right-4 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600">
                        In Development
                    </span>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center mb-4">
                        <i class="ph ph-kanban text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900">RedMaple Project Desk</h3>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mt-0.5">Task Boards & Time Tracking</p>
                    <p class="text-sm text-slate-600 mt-3 leading-relaxed">
                        Project milestones, billable hours tracking, client collaborative boards, and instant time-log conversion into Invoicy line items.
                    </p>
                </div>

                <!-- App 5: RedMaple POS & Retail -->
                <div class="p-8 rounded-2xl border border-slate-200 bg-white relative card-hover transition">
                    <span class="absolute top-4 right-4 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600">
                        Roadmap
                    </span>
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 border border-purple-200 flex items-center justify-center mb-4">
                        <i class="ph ph-storefront text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900">RedMaple POS</h3>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mt-0.5">Retail & Counter Checkout</p>
                    <p class="text-sm text-slate-600 mt-3 leading-relaxed">
                        Barcode scanning, receipt printers, cash drawers, multi-location stock replenishment, and instant receipt dispatch.
                    </p>
                </div>

                <!-- App 6: RedMaple AI Contracts -->
                <div class="p-8 rounded-2xl border border-slate-200 bg-white relative card-hover transition">
                    <span class="absolute top-4 right-4 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600">
                        Roadmap
                    </span>
                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center mb-4">
                        <i class="ph ph-file-lock text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900">RedMaple AI Contracts</h3>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mt-0.5">e-Signatures & Retainer Agreements</p>
                    <p class="text-sm text-slate-600 mt-3 leading-relaxed">
                        Cryptographically verified client signatures, automated retainer renewals, and legal boilerplate generation.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-24 bg-slate-50 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs font-bold uppercase tracking-wider text-red-600 mb-2">Transparent Pricing</h2>
                <h3 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Scale From Solo Freelancer to Global Agency</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <!-- Tier 1 -->
                <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <h4 class="font-bold text-slate-900 text-lg">Starter</h4>
                        <p class="text-xs text-slate-500 mt-1">For freelancers and sole proprietors</p>
                        <div class="mt-4 mb-6">
                            <span class="text-4xl font-black text-slate-900">$0</span>
                            <span class="text-xs text-slate-400 font-semibold">/ month</span>
                        </div>
                        <ul class="space-y-3 text-xs text-slate-600">
                            <li class="flex items-center gap-2"><i class="ph ph-check text-emerald-600 font-bold"></i> Up to 15 Invoices / month</li>
                            <li class="flex items-center gap-2"><i class="ph ph-check text-emerald-600 font-bold"></i> Client CRM & Walk-in Mode</li>
                            <li class="flex items-center gap-2"><i class="ph ph-check text-emerald-600 font-bold"></i> Multi-Currency Tax Engine</li>
                            <li class="flex items-center gap-2"><i class="ph ph-check text-emerald-600 font-bold"></i> Standard PDF Downloads</li>
                        </ul>
                    </div>
                    <a href="{{ route('register') }}" class="mt-8 block text-center py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl transition">
                        Get Started Free
                    </a>
                </div>

                <!-- Tier 2 (Featured) -->
                <div class="bg-slate-900 text-white p-8 rounded-2xl border-2 border-red-600 shadow-xl flex flex-col justify-between relative">
                    <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-600 text-white shadow-sm">
                        Most Popular
                    </span>
                    <div>
                        <h4 class="font-bold text-white text-lg">Agency Pro</h4>
                        <p class="text-xs text-slate-400 mt-1">For growing design, dev & consulting agencies</p>
                        <div class="mt-4 mb-6">
                            <span class="text-4xl font-black text-white">$29</span>
                            <span class="text-xs text-slate-400 font-semibold">/ month</span>
                        </div>
                        <ul class="space-y-3 text-xs text-slate-300">
                            <li class="flex items-center gap-2"><i class="ph ph-check text-red-500 font-bold"></i> Unlimited Invoices & Credit Notes</li>
                            <li class="flex items-center gap-2"><i class="ph ph-check text-red-500 font-bold"></i> <strong>Automated Reminders (45, 30, 15, 7, 3, 2d)</strong></li>
                            <li class="flex items-center gap-2"><i class="ph ph-check text-red-500 font-bold"></i> <strong>Future Invoice Scheduler & Vendor Alerts</strong></li>
                            <li class="flex items-center gap-2"><i class="ph ph-check text-red-500 font-bold"></i> Custom Logo & Agency Branding</li>
                            <li class="flex items-center gap-2"><i class="ph ph-check text-red-500 font-bold"></i> Password-Protected Customer PDFs</li>
                        </ul>
                    </div>
                    <a href="{{ route('register') }}" class="mt-8 block text-center py-2.5 px-4 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow-md shadow-red-500/20 transition">
                        Start 14-Day Free Trial
                    </a>
                </div>

                <!-- Tier 3 -->
                <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <h4 class="font-bold text-slate-900 text-lg">Enterprise Studio</h4>
                        <p class="text-xs text-slate-500 mt-1">For multinational companies & franchises</p>
                        <div class="mt-4 mb-6">
                            <span class="text-4xl font-black text-slate-900">$79</span>
                            <span class="text-xs text-slate-400 font-semibold">/ month</span>
                        </div>
                        <ul class="space-y-3 text-xs text-slate-600">
                            <li class="flex items-center gap-2"><i class="ph ph-check text-emerald-600 font-bold"></i> Everything in Agency Pro</li>
                            <li class="flex items-center gap-2"><i class="ph ph-check text-emerald-600 font-bold"></i> Unlimited Team Workspaces</li>
                            <li class="flex items-center gap-2"><i class="ph ph-check text-emerald-600 font-bold"></i> Dedicated Account Manager</li>
                            <li class="flex items-center gap-2"><i class="ph ph-check text-emerald-600 font-bold"></i> High-Throughput REST API Access</li>
                        </ul>
                    </div>
                    <a href="{{ route('register') }}" class="mt-8 block text-center py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl transition">
                        Contact Enterprise
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-950 text-slate-400 py-16 border-t border-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 pb-12 border-b border-slate-900">
                <div class="md:col-span-1 space-y-3">
                    <div class="flex items-center gap-2.5">
                        @if($websiteLogo)
                            <div class="h-8 flex items-center">
                                <img src="{{ $websiteLogo }}" alt="{{ $brandName }}" class="max-h-8 max-w-[160px] object-contain">
                            </div>
                        @else
                            <div class="w-8 h-8 rounded-lg bg-red-600/10 border border-red-500/20 flex items-center justify-center p-1.5">
                                <svg class="w-full h-full text-red-500" viewBox="0 0 900 900" fill="currentColor">
                                    <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                                </svg>
                            </div>
                            <span class="text-lg font-black text-white">{{ $brandName }} <span class="text-xs text-red-500">by {{ $brandSub }}</span></span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        RedMaple Agency &bull; High-precision financial billing software and SaaS digital products engineering.
                    </p>
                </div>

                <div>
                    <h5 class="text-xs font-bold text-white uppercase tracking-wider mb-4">Invoicy Suite</h5>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ route('invoices.create') }}" class="hover:text-white transition">Invoice Builder</a></li>
                        <li><a href="{{ route('reminders.index') }}" class="hover:text-white transition">Milestone Reminders</a></li>
                        <li><a href="{{ route('credit-notes.index') }}" class="hover:text-white transition">Credit Notes</a></li>
                        <li><a href="{{ route('clients.index') }}" class="hover:text-white transition">Client CRM</a></li>
                    </ul>
                </div>

                <div>
                    <h5 class="text-xs font-bold text-white uppercase tracking-wider mb-4">Future SaaS Ecosystem</h5>
                    <ul class="space-y-2 text-xs text-slate-500">
                        <li>RedMaple CRM Pro (Q1 2027)</li>
                        <li>RedMaple HRMS & Payroll</li>
                        <li>RedMaple Project Desk</li>
                        <li>RedMaple POS & Retail</li>
                    </ul>
                </div>

                <div>
                    <h5 class="text-xs font-bold text-white uppercase tracking-wider mb-4">Account & Access</h5>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">Portal Login</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition">Create Account</a></li>
                        <li><a href="{{ route('docs.index') }}" class="hover:text-white transition">Documentation</a></li>
                    </ul>
                </div>
            </div>

            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-600 gap-4">
                <p>{{ \App\Models\CmsContent::getVal('footer_copyright', '© ' . date('Y') . " $brandName Software Studio. All rights reserved. $brandName is a proprietary product of $brandSub.") }}</p>
                <div class="flex items-center gap-4">
                    <a href="{{ url('/p/privacy-policy') }}" class="hover:text-slate-400">Privacy Policy</a>
                    <span>&bull;</span>
                    <a href="{{ url('/p/terms-of-service') }}" class="hover:text-slate-400">Terms of Service</a>
                    <span>&bull;</span>
                    <a href="{{ url('/p/about-us') }}" class="hover:text-slate-400">About Us</a>
                    <span>&bull;</span>
                    <a href="https://redmapleagency.com" target="_blank" class="hover:text-slate-400">redmapleagency.com</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
