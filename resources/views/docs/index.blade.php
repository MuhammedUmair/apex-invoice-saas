<!DOCTYPE html>
<html lang="{{ $currentLang }}" dir="{{ in_array($currentLang, ['ur', 'ar']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentation & Multi-Language Guide - Invoicy by RedMaple</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/maple-leaf.svg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c',
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <style>
        [dir="rtl"] { text-align: right; }
        [dir="rtl"] .ltr-text { direction: ltr; text-align: left; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col">

    <!-- Top Navigation Bar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-red-600/10 border border-red-500/20 flex items-center justify-center p-1.5 shrink-0 shadow-sm">
                        <svg class="w-full h-full text-red-600 drop-shadow-sm" viewBox="0 0 900 900" fill="currentColor">
                            <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                        </svg>
                    </div>
                    <div>
                        <span class="font-black text-slate-900 text-sm tracking-tight flex items-center gap-1">
                            Red<span class="text-red-600">Maple</span>
                        </span>
                        <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block -mt-1">User Manual & API</span>
                    </div>
                </a>
                <span class="hidden sm:inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 border border-red-200">
                    v1.0 Production
                </span>
            </div>

            <!-- Language Switcher & App Links -->
            <div class="flex items-center gap-3">
                <!-- Language Selector Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" type="button" class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-semibold text-slate-700 transition">
                        <span>{{ $languages[$currentLang]['flag'] }}</span>
                        <span>{{ $languages[$currentLang]['name'] }} ({{ $languages[$currentLang]['native'] }})</span>
                        <i class="ph ph-caret-down text-slate-400"></i>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-xl border border-slate-200 py-1.5 z-50">
                        <div class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Select Documentation Language</div>
                        @foreach($languages as $code => $meta)
                            <a href="{{ route('docs.index', ['lang' => $code]) }}" class="flex items-center justify-between px-3 py-2 text-xs hover:bg-red-50 hover:text-red-700 transition {{ $currentLang === $code ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-700' }}">
                                <span class="flex items-center gap-2">
                                    <span>{{ $meta['flag'] }}</span>
                                    <span>{{ $meta['name'] }}</span>
                                </span>
                                <span class="text-slate-400 text-[11px] font-medium">{{ $meta['native'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                        <i class="ph ph-arrow-left"></i> Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                        Sign In <i class="ph ph-arrow-right"></i>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Container with Sidebar + Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 flex flex-col md:flex-row gap-8 w-full">
        <!-- Sidebar Navigation -->
        <aside class="w-full md:w-72 shrink-0">
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm sticky top-24 max-h-[calc(100vh-7rem)] overflow-y-auto">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider px-3 mb-3">Tenant Documentation Guide</h4>
                <nav class="space-y-1 text-xs font-medium">
                    <a href="#overview" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
                        <i class="ph ph-shield-check text-sm text-slate-400 shrink-0"></i> 1. Platform & Multi-Tenancy
                    </a>
                    <a href="#team-roles" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
                        <i class="ph ph-users-three text-sm text-slate-400 shrink-0"></i> 2. Team Members & Roles
                    </a>
                    <a href="#invoicing-engine" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
                        <i class="ph ph-receipt text-sm text-slate-400 shrink-0"></i> 3. Invoicing & Sequences
                    </a>
                    <a href="#tracking-dispatches" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
                        <i class="ph ph-envelope-open text-sm text-slate-400 shrink-0"></i> 4. Email & Open Tracking
                    </a>
                    <a href="#tax-and-security" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
                        <i class="ph ph-lock-key text-sm text-slate-400 shrink-0"></i> 5. Tax & PDF Protection
                    </a>
                    <a href="#recurring-retainers" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
                        <i class="ph ph-arrows-clockwise text-sm text-slate-400 shrink-0"></i> 6. Recurring Retainers
                    </a>
                    <a href="#credit-notes" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
                        <i class="ph ph-arrow-u-up-left text-sm text-slate-400 shrink-0"></i> 7. Credit Notes & Refunds
                    </a>
                    <a href="#reminders-scheduler" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
                        <i class="ph ph-bell-ringing text-sm text-slate-400 shrink-0"></i> 8. Reminders & Scheduler
                    </a>
                    <a href="#client-crm" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
                        <i class="ph ph-users text-sm text-slate-400 shrink-0"></i> 9. Client CRM & Locales
                    </a>
                    <a href="#catalog-presets" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
                        <i class="ph ph-package text-sm text-slate-400 shrink-0"></i> 10. Catalog & Presets
                    </a>
                    <a href="#payments-webhooks" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
                        <i class="ph ph-credit-card text-sm text-slate-400 shrink-0"></i> 11. Payment Gateways
                    </a>
                    <a href="#branding-smtp" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
                        <i class="ph ph-gear text-sm text-slate-400 shrink-0"></i> 12. Branding & SMTP Setup
                    </a>
                    <a href="#bulk-actions" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
                        <i class="ph ph-check-square-offset text-sm text-slate-400 shrink-0"></i> 13. Bulk Operations Suite
                    </a>
                </nav>

                <div class="mt-6 pt-4 border-t border-slate-100 text-xs text-slate-500">
                    <p class="font-semibold text-slate-700">Need Live Support?</p>
                    <p class="text-[11px] mt-0.5">Contact <a href="mailto:support@redmapleagency.com" class="text-red-600 hover:underline">support@redmapleagency.com</a></p>
                </div>
            </div>
        </aside>

        <!-- Main Content Area with Localized Content -->
        <main class="flex-1 space-y-10 min-w-0">

            <!-- Language Notice Banner -->
            <div class="bg-gradient-to-r from-red-600 to-rose-700 rounded-2xl p-6 text-white shadow-md flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold flex items-center gap-2">
                        <span>{{ $languages[$currentLang]['flag'] }}</span>
                        @if($currentLang === 'ur')
                            <span>ریڈ میپل انوائس جنریٹر - مکمل اردو رہنمائی</span>
                        @elseif($currentLang === 'ar')
                            <span>مولد فواتير ريد مابل - الدليل الشامل باللغة العربية</span>
                        @elseif($currentLang === 'hi')
                            <span>रेडमेपल इनवॉइस जेनरेटर - सम्पूर्ण हिंदी गाइड</span>
                        @elseif($currentLang === 'tr')
                            <span>RedMaple Fatura Oluşturucu - Türkçe Kullanım Kılavuzu</span>
                        @else
                            <span>RedMaple Invoice Generator - Multilingual User Manual</span>
                        @endif
                    </h2>
                    <p class="text-red-100 text-sm mt-1">
                        @if($currentLang === 'ur')
                            یہ دستاویز آپ کو ملٹی ٹیننٹ انوائسنگ، کلائنٹ ڈائرکٹری، ادائیگیوں، ٹیکس، اور پی ڈی ایف سیکیورٹی کے طریقہ کار کی مکمل رہنمائی فراہم کرتی ہے۔
                        @elseif($currentLang === 'ar')
                            يوفر هذا الدليل شرحاً تفصيلياً لإدارة الفواتير متعددة المستأجرين، دليل العملاء، بوابات الدفع، والضرائب الإقليمية.
                        @elseif($currentLang === 'hi')
                            यह दस्तावेज़ आपको बहु-किरायेदार इनवॉइसिंग, क्लाइंट डायरेक्टरी, भुगतान गेटवे और टैक्स नीतियों का संपूर्ण मार्गदर्शन प्रदान करता है।
                        @elseif($currentLang === 'tr')
                            Bu kılavuz, çok kiracılı faturalama, müşteri rehberi, ödeme entegrasyonları ve vergi yapılandırmasını açıklar.
                        @else
                            Complete guide for multi-tenant SaaS invoicing, client CRM, Stripe/PayPal checkout, localized taxes, and secure PDF delivery.
                        @endif
                    </p>
                </div>
            </div>

            <!-- Section 1: Overview & Tenant Isolation -->
            <section id="overview" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-shield-check"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">1. Platform Architecture & Multi-Tenancy</h3>
                        <p class="text-xs text-slate-500">Cryptographically isolated agency workspaces and tenant scopes</p>
                    </div>
                </div>

                <div class="text-sm leading-relaxed text-slate-700 space-y-3">
                    <p>RedMaple is built on a strict multi-tenant schema where every agency operates in an isolated workspace container:</p>
                    <ul class="list-disc list-inside space-y-1 text-slate-600">
                        <li><strong>Tenant Scoping:</strong> All database queries are automatically scoped using <code class="bg-slate-100 px-1 py-0.5 rounded text-red-600">WorkspaceScope</code>, guaranteeing zero data leakage between agencies.</li>
                        <li><strong>Enterprise Authentication:</strong> Strong Bcrypt credential hashing and CSRF token verification.</li>
                        <li><strong>Role-Based Access Control (RBAC):</strong> Permissions segregated by Owner, Admin, and Staff tiers.</li>
                    </ul>

                    <!-- Visual Architecture Card -->
                    <div class="mt-4 p-4 rounded-xl bg-slate-900 text-white font-mono text-xs space-y-2 border border-slate-800">
                        <div class="text-red-400 font-bold">// Multi-Tenant Data Isolation Model</div>
                        <div class="text-slate-300">Tenant Workspace [/agency-slug] &rarr; Isolated CRM Clients, Invoices, Retainers, SMTP & Bank Keys</div>
                        <div class="text-emerald-400">&check; Zero cross-tenant leakage &bull; Row-level workspace_id enforcement &bull; Cryptographic portal tokens</div>
                    </div>
                </div>
            </section>

            <!-- Section 2: Team Members & Role Privileges -->
            <section id="team-roles" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-users-three"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">2. Team Members & Granular Role Privileges</h3>
                        <p class="text-xs text-slate-500">Seat allocations, activation invites, and permission enforcement</p>
                    </div>
                </div>

                <div class="text-sm leading-relaxed text-slate-700 space-y-3">
                    <p>Agency teams are managed under <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-800">Agency Settings &rarr; Team Members</code>. Each team member is assigned one of three granular roles:</p>

                    <!-- Permission Matrix Table -->
                    <div class="overflow-x-auto rounded-xl border border-slate-200 mt-2">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[10px] border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3">Privilege / Capability</th>
                                    <th class="px-3 py-3 text-center text-red-700">Primary Owner</th>
                                    <th class="px-3 py-3 text-center text-blue-700">Workspace Admin</th>
                                    <th class="px-3 py-3 text-center text-emerald-700">Staff Member</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-2.5 font-medium">Create & Manage Invoices</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; Full</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; Full</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; Full</td>
                                </tr>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-2.5 font-medium">Client CRM & Service Catalog</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; Full</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; Full</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; Full</td>
                                </tr>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-2.5 font-medium">Credit Notes & Reminders</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; Full</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; Full</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; Full</td>
                                </tr>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-2.5 font-medium">Agency Settings & SMTP Config</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; Full</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; Full</td>
                                    <td class="px-3 py-2.5 text-center text-rose-600 font-bold">&cross; Restricted (Hidden)</td>
                                </tr>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-2.5 font-medium">Plan & Billing Upgrades</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; Full</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; Full</td>
                                    <td class="px-3 py-2.5 text-center text-rose-600 font-bold">&cross; Restricted (Hidden)</td>
                                </tr>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-2.5 font-medium">Invite & Change Team Roles</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; Full</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; Full</td>
                                    <td class="px-3 py-2.5 text-center text-rose-600 font-bold">&cross; Restricted</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3.5 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900 flex items-start gap-2.5">
                        <i class="ph ph-shield-check text-base text-amber-600 shrink-0 mt-0.5"></i>
                        <div>
                            <strong>Email Verification Required:</strong> Newly invited team members receive a signed activation email. Until their email address is verified via the cryptographic link, their login remains locked to prevent account spoofing.
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section 3: Invoicing Engine -->
            <section id="invoicing-engine" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-receipt"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">3. Invoicing Engine & Sequential Numbering Lock</h3>
                        <p class="text-xs text-slate-500">Atomic gap-less sequence locking per workspace</p>
                    </div>
                </div>

                <div class="text-sm leading-relaxed text-slate-700 space-y-3">
                    <p>
                        Every issued invoice is assigned a concurrency-safe sequential code (e.g., <code class="bg-slate-100 px-1 py-0.5 rounded text-red-600">INV-2026-0001</code>). An atomic database sequence counter prevents duplicate numbering even under simultaneous checkout attempts.
                    </p>

                    <!-- Visual Invoice Status Flowchart -->
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-xs font-bold text-slate-800 uppercase tracking-wider block mb-3">Invoice Lifecycle Flow</span>
                        <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
                            <span class="px-3 py-1.5 rounded-lg bg-slate-200 text-slate-700">1. Draft</span>
                            <i class="ph ph-arrow-right text-slate-400"></i>
                            <span class="px-3 py-1.5 rounded-lg bg-blue-100 text-blue-800">2. Issued</span>
                            <i class="ph ph-arrow-right text-slate-400"></i>
                            <span class="px-3 py-1.5 rounded-lg bg-sky-100 text-sky-800">3. Sent / Emailed</span>
                            <i class="ph ph-arrow-right text-slate-400"></i>
                            <span class="px-3 py-1.5 rounded-lg bg-amber-100 text-amber-800">4. Partially Paid</span>
                            <i class="ph ph-arrow-right text-slate-400"></i>
                            <span class="px-3 py-1.5 rounded-lg bg-emerald-100 text-emerald-800">5. Fully Paid</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section 4: Email Dispatch & Open Tracking -->
            <section id="tracking-dispatches" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-envelope-open"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">4. Email Dispatches, Resend Confirmation & Open Tracking</h3>
                        <p class="text-xs text-slate-500">Real-time engagement telemetry, beacon tracking, and duplicate prevention</p>
                    </div>
                </div>

                <div class="text-sm leading-relaxed text-slate-700 space-y-3">
                    <p>Invoicy provides real-time client engagement analytics for every invoice dispatched to a client email address:</p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-xs font-bold text-sky-700 flex items-center gap-1.5 mb-1">
                                <i class="ph ph-paper-plane-tilt"></i> Dispatch Status
                            </span>
                            <p class="text-xs text-slate-600">Dispatches via authenticated SMTP. Re-send safety dialog asks confirmation if invoice was previously dispatched.</p>
                        </div>
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-xs font-bold text-emerald-700 flex items-center gap-1.5 mb-1">
                                <i class="ph ph-envelope-open"></i> Email Open Tracking
                            </span>
                            <p class="text-xs text-slate-600">Embedded 1x1 transparent beacon telemetry logs exact timestamps and count when recipient opens the email in their client.</p>
                        </div>
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-xs font-bold text-indigo-700 flex items-center gap-1.5 mb-1">
                                <i class="ph ph-eye"></i> Portal / PDF Views
                            </span>
                            <p class="text-xs text-slate-600">Logs every view of the secure web payment portal or PDF download with date and IP telemetry.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section 5: Taxation & PDF Passwords -->
            <section id="tax-and-security" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-lock-key"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">5. Country Taxation Rules & PDF Security</h3>
                        <p class="text-xs text-slate-500">GAAP/IFRS pre-tax discounts and optional client PIN protection</p>
                    </div>
                </div>

                <div class="text-sm leading-relaxed text-slate-700 space-y-3">
                    <ul class="list-disc list-inside space-y-1 text-slate-600">
                        <li><strong>Pre-Tax Discount Calculation:</strong> Taxes are computed strictly after deducting discounts: <code class="bg-slate-100 px-1 py-0.5 rounded">Taxable Amount = Subtotal - Discount</code>.</li>
                        <li><strong>Regional Tax Presets:</strong> UK VAT (20%), DE MwSt (19%), UAE VAT (5%), Saudi Arabia VAT (15%), India GST (18%), Turkey KDV (20%), Pakistan Sales Tax (16%).</li>
                        <li><strong>PDF Security PIN:</strong> When creating sensitive commercial invoices, enable "Password Protect PDF". The client must enter the assigned PIN before downloading the document.</li>
                    </ul>
                </div>
            </section>

            <!-- Section 6: Recurring Retainers -->
            <section id="recurring-retainers" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-arrows-clockwise"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">6. Recurring Retainers & Auto-Billing</h3>
                        <p class="text-xs text-slate-500">Automated monthly billing cycles and recurring client retainers</p>
                    </div>
                </div>

                <div class="text-sm leading-relaxed text-slate-700 space-y-3">
                    <p>Agencies with monthly retainer agreements can configure automated recurring billing under <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-800">Recurring Retainers</code>:</p>
                    <ul class="list-disc list-inside space-y-1 text-slate-600">
                        <li><strong>Billing Cycles:</strong> Weekly, Monthly, Quarterly, Semi-Annually, or Annually.</li>
                        <li><strong>Generation Mode:</strong> Automated dispatch or Draft queue requiring admin approval before delivery.</li>
                        <li><strong>Auto-Numbering:</strong> Each generation pulls from the next sequential invoice number.</li>
                    </ul>
                </div>
            </section>

            <!-- Section 7: Credit Notes & Refunds -->
            <section id="credit-notes" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-arrow-u-up-left"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">7. Credit Notes & Ledger Refunds</h3>
                        <p class="text-xs text-slate-500">Accounting integrity and immutable reversal trail</p>
                    </div>
                </div>

                <div class="text-sm leading-relaxed text-slate-700 space-y-3">
                    <p>
                        In commercial accounting, issued invoices must never be deleted. To correct errors, billing mistakes, or cancelled projects, an agency issues a <strong>Credit Note</strong> (<code class="bg-slate-100 px-1 py-0.5 rounded">CN-YYYY-XXXX</code>).
                    </p>
                    <p class="text-xs text-slate-600">
                        Credit notes automatically adjust outstanding balances, update the financial audit trail, and maintain strict accounting compliance.
                    </p>
                </div>
            </section>

            <!-- Section 8: Reminders & Scheduler -->
            <section id="reminders-scheduler" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-bell-ringing"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">8. Reminders & Automated Scheduler</h3>
                        <p class="text-xs text-slate-500">Automated receivables follow-up before and after invoice due dates</p>
                    </div>
                </div>

                <div class="text-sm leading-relaxed text-slate-700 space-y-3">
                    <p>Configure automated payment reminder rules to minimize overdue invoices:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-xs font-bold text-slate-800 block mb-1">Pre-Due Notice</span>
                            <span class="text-xs text-slate-600 block">Sends gentle reminder 3 days prior to due date.</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-xs font-bold text-slate-800 block mb-1">Due Date Alert</span>
                            <span class="text-xs text-slate-600 block">Sends notification on the exact due date.</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-xs font-bold text-rose-700 block mb-1">Overdue Follow-up</span>
                            <span class="text-xs text-slate-600 block">Escalates notification at 7, 14, and 30 days overdue.</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section 9: Client CRM & Languages -->
            <section id="client-crm" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-users"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">9. Client Directory & CRM</h3>
                        <p class="text-xs text-slate-500">Multi-currency billing and localized customer preferences</p>
                    </div>
                </div>

                <div class="text-sm leading-relaxed text-slate-700 space-y-3">
                    <p>When registering a client in the CRM, you can configure their specific language and billing rules:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-xs font-bold text-slate-900 block mb-1">Supported Languages</span>
                            <span class="text-xs text-slate-600 leading-normal block">English, Urdu (اردو), Hindi (हिन्दी), Turkish (Türkçe), Arabic (العربية), Spanish, French, German, Chinese.</span>
                        </div>
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-xs font-bold text-slate-900 block mb-1">Supported Currencies</span>
                            <span class="text-xs text-slate-600 leading-normal block">USD ($), EUR (€), GBP (£), AED (د.إ), SAR (﷼), CAD (C$), AUD (A$), INR (₹), TRY (₺), PKR (Rs).</span>
                        </div>
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-xs font-bold text-slate-900 block mb-1">Payment Terms</span>
                            <span class="text-xs text-slate-600 leading-normal block">Due on Receipt, Net 7 Days, Net 14 Days, Net 30 Days, Net 60 Days.</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section 10: Catalog & Presets -->
            <section id="catalog-presets" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-package"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">10. Service Catalog & Line Item Presets</h3>
                        <p class="text-xs text-slate-500">Pre-saved products, hourly deliverables, and standard rates</p>
                    </div>
                </div>

                <div class="text-sm leading-relaxed text-slate-700 space-y-3">
                    <p>Save recurring service items under <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-800">Catalog & Presets</code>. When drafting new invoices, simply select a preset from the autocomplete dropdown to instantly populate unit rate, description, and tax category.</p>
                </div>
            </section>

            <!-- Section 11: Payments & Webhooks -->
            <section id="payments-webhooks" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-credit-card"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">11. Stripe, PayPal & Merchant Gateways</h3>
                        <p class="text-xs text-slate-500">Direct client payments deposit directly into your agency merchant account</p>
                    </div>
                </div>

                <div class="text-sm leading-relaxed text-slate-700 space-y-3">
                    <p>Connect your own Stripe and PayPal accounts in Agency Settings:</p>
                    <ul class="list-disc list-inside space-y-1 text-slate-600">
                        <li><strong>Direct Merchant Settlement:</strong> Client payments flow directly into your agency merchant balance. Invoicy charges 0% transaction commission.</li>
                        <li><strong>Free Trial Evaluation Mode:</strong> In trial mode, gateway input is locked to prevent live payments on test accounts. Upgrading to a paid subscription unlocks live Stripe and PayPal integrations immediately.</li>
                        <li><strong>Idempotent Webhooks:</strong> Webhook events are deduplicated to prevent double-crediting.</li>
                    </ul>
                </div>
            </section>

            <!-- Section 12: Branding & SMTP Setup -->
            <section id="branding-smtp" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-gear"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">12. Agency Branding, Wire Transfer & Custom SMTP</h3>
                        <p class="text-xs text-slate-500">Custom logos, brand colors, whitelabel PDFs, and custom outgoing email servers</p>
                    </div>
                </div>

                <div class="text-sm leading-relaxed text-slate-700 space-y-3">
                    <p>Customize your agency's visual identity and communications under <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-800">Agency Settings</code>:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-xs font-bold text-slate-800 block mb-1">Custom Logo & Colors</span>
                            <span class="text-xs text-slate-600 block">Upload PNG, JPG, or SVG logos and configure primary brand hex colors.</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-xs font-bold text-slate-800 block mb-1">Wire Transfer Details</span>
                            <span class="text-xs text-slate-600 block">Add Bank Name, IBAN, Swift code, and routing numbers automatically stamped on PDF invoices.</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-xs font-bold text-slate-800 block mb-1">Custom Outgoing SMTP</span>
                            <span class="text-xs text-slate-600 block">Dispatch invoices from your own corporate domain email address with live connection test.</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section 13: Bulk Operations Suite -->
            <section id="bulk-actions" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-check-square-offset"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">13. Bulk Operations Suite</h3>
                        <p class="text-xs text-slate-500">High-velocity multi-record batch processing</p>
                    </div>
                </div>

                <div class="text-sm leading-relaxed text-slate-700 space-y-3">
                    <p>Invoicy provides bulk selection toolbars across key management modules:</p>
                    <ul class="list-disc list-inside space-y-1 text-slate-600">
                        <li><strong>Invoices Bulk Actions:</strong> Select multiple invoices to batch <em>Mark as Paid</em>, batch <em>Mark as Issued</em>, dispatch <em>Batch Email Invoices</em>, or <em>Delete Drafts</em> in a single click.</li>
                        <li><strong>Tenant Workspaces (Admin):</strong> Batch <em>Activate</em>, <em>Suspend</em>, grant <em>+14 Days Trial Extensions</em>, or <em>Renew for 1 Month</em> across multiple subscribed agencies.</li>
                        <li><strong>Promotional Coupons & Offers (Admin):</strong> Batch <em>Activate</em>, <em>Pause</em>, or <em>Delete</em> discount offers.</li>
                    </ul>
                </div>
            </section>

            <!-- Dynamic Articles Published via CMS -->
            @if(isset($dynamicArticles) && $dynamicArticles->isNotEmpty())
                @foreach($dynamicArticles as $art)
                    <section id="doc-{{ $art->slug }}" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
                        <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                            <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-xl shrink-0">
                                <i class="ph ph-book-open"></i>
                            </div>
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider text-red-600 bg-red-50 px-2 py-0.5 rounded">{{ $art->category }}</span>
                                <h3 class="text-lg font-bold text-slate-900 mt-1">{{ $art->getLocalizedTitle($currentLang) }}</h3>
                            </div>
                        </div>
                        <div class="text-sm leading-relaxed text-slate-700 whitespace-pre-line" dir="{{ in_array($currentLang, ['ur', 'ar']) ? 'rtl' : 'ltr' }}">
                            {{ $art->getLocalizedContent($currentLang) }}
                        </div>
                    </section>
                @endforeach
            @endif

        </main>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500 mt-auto">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-800">Invoicy <span class="text-xs font-normal text-slate-400">by</span> <span class="text-red-600">RedMaple</span></span>
                <span>&bull;</span>
                <span>Multilingual SaaS Documentation</span>
            </div>
            <div>
                &copy; {{ date('Y') }} RedMaple. Production Ready on Hostinger Cloud.
            </div>
        </div>
    </footer>

</body>
</html>
