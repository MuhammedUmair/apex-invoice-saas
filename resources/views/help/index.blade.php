@extends('layouts.app')

@section('content')
<div class="w-full space-y-8" x-data="{
    activeTab: '{{ $activeTab ?? 'overview' }}',
    searchQuery: '',
    matchesSearch(title, text) {
        if (!this.searchQuery) return true;
        const q = this.searchQuery.toLowerCase();
        return title.toLowerCase().includes(q) || text.toLowerCase().includes(q);
    }
}">
    <!-- Header Banner (Full Width) -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-red-950 text-white rounded-3xl p-8 sm:p-10 shadow-xl relative overflow-hidden w-full">
        <div class="absolute -right-10 -bottom-10 w-96 h-96 bg-red-600/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 max-w-4xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-600/30 border border-red-500/30 text-red-300 text-xs font-bold uppercase tracking-wider mb-3">
                <i class="ph ph-book-open text-sm"></i> {{ __('Help Center & Complete System Manual') }}
                <span class="mx-1 opacity-50">&bull;</span>
                <kbd class="px-1.5 py-0.2 bg-black/40 rounded text-[10px] font-mono border border-white/20">F1</kbd>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black tracking-tight mb-2">{{ __('Invoicy Knowledge Base & Guide') }}</h1>
            <p class="text-slate-300 text-sm leading-relaxed mb-6">
                {{ __('Master all enterprise modules of Invoicy by RedMaple. Explore detailed feature breakdowns, business benefits, step-by-step usage guides, tenant architecture documentation, developer REST APIs, and interactive visual walkthroughs.') }}
            </p>

            <!-- Search Filter Bar -->
            <div class="relative max-w-2xl">
                <i class="ph ph-magnifying-glass text-slate-400 absolute left-4 top-1/2 -translate-y-1/2 text-lg"></i>
                <input type="text" x-model="searchQuery" placeholder="{{ __('Search guides, modules, or features (e.g., QR code, Recall, Taxes, Payments, Credit Note, API)...') }}"
                       class="w-full pl-11 pr-4 py-3 bg-white/10 border border-white/20 backdrop-blur-md rounded-2xl text-white placeholder-slate-400 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-red-500 transition">
            </div>
        </div>
    </div>

    <!-- Navigation Tabs & Content Layout (Full Width Grid) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start w-full">
        
        <!-- Left Sidebar Navigation -->
        <div class="lg:col-span-3 xl:col-span-3 bg-white rounded-3xl border border-slate-200 p-4 shadow-xs sticky top-6 space-y-1">
            <div class="px-3 py-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                {{ __('Core Billing Modules') }}
            </div>

            <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-squares-four text-base text-red-600"></i>
                    <span>1. {{ __('Dashboard & Workspace') }}</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'invoices'" :class="activeTab === 'invoices' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-receipt text-base text-red-600"></i>
                    <span>2. {{ __('Invoices & Live Builder') }}</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'verification'" :class="activeTab === 'verification' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-qr-code text-base text-red-600"></i>
                    <span>3. {{ __('QR Code & Verification') }}</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'recall'" :class="activeTab === 'recall' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-arrow-counter-clockwise text-base text-amber-600"></i>
                    <span>4. {{ __('Invoice Recall & Pull Back') }}</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'tracking'" :class="activeTab === 'tracking' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-eye text-base text-sky-600"></i>
                    <span>5. {{ __('Live View Alerts & Portal') }}</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'payments'" :class="activeTab === 'payments' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-hand-coins text-base text-emerald-600"></i>
                    <span>6. {{ __('Payments & Receipts') }}</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'retainers'" :class="activeTab === 'retainers' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-arrows-clockwise text-base text-emerald-600"></i>
                    <span>7. {{ __('Recurring Retainers') }}</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'credit_notes'" :class="activeTab === 'credit_notes' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-credit-card text-base text-rose-600"></i>
                    <span>8. {{ __('Credit Notes & Refunds') }}</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <div class="px-3 pt-3 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                {{ __('Management & Operations') }}
            </div>

            <button @click="activeTab = 'clients'" :class="activeTab === 'clients' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-users text-base text-indigo-600"></i>
                    <span>9. {{ __('Client CRM & CRM Directory') }}</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'services'" :class="activeTab === 'services' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-package text-base text-amber-600"></i>
                    <span>10. {{ __('Catalog & Presets') }}</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'taxes'" :class="activeTab === 'taxes' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-calculator text-base text-emerald-600"></i>
                    <span>11. {{ __('Canadian & Global Taxes') }}</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'reminders'" :class="activeTab === 'reminders' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-bell text-base text-amber-600"></i>
                    <span>12. {{ __('Reminders & Scheduler') }}</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'smtp'" :class="activeTab === 'smtp' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-paper-plane text-base text-blue-600"></i>
                    <span>13. {{ __('Custom SMTP & Email') }}</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'billing'" :class="activeTab === 'billing' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-credit-card text-base text-purple-600"></i>
                    <span>14. {{ __('Plan, Billing & Coupons') }}</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'ai'" :class="activeTab === 'ai' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-sparkle text-base text-red-600"></i>
                    <span>15. {{ __('AI Agent Assistant') }}</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'support'" :class="activeTab === 'support' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-lifebuoy text-base text-rose-600"></i>
                    <span>16. {{ __('Support Desk (24h SLA)') }}</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <div class="px-3 pt-3 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                {{ __('Tenant Documentation & Architecture') }}
            </div>

            <button @click="activeTab = 'tenant_docs'" :class="activeTab === 'tenant_docs' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-shield-check text-base text-emerald-600"></i>
                    <span>17. {{ __('Multi-Tenancy & RBAC') }}</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'developer_api'" :class="activeTab === 'developer_api' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-code text-base text-sky-600"></i>
                    <span>18. {{ __('Developer REST API & Webhooks') }}</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <!-- Quick Action Box -->
            <div class="pt-4 border-t border-slate-100 px-2">
                <div class="p-3 bg-red-50/70 rounded-2xl border border-red-100 text-xs">
                    <div class="font-bold text-red-900 flex items-center gap-1.5 mb-1">
                        <i class="ph ph-headset text-sm text-red-600"></i> {{ __('Dedicated Support Desk') }}
                    </div>
                    <p class="text-slate-600 text-[11px] mb-2">{{ __('Need direct engineering assistance? Submit a ticket under our 24h SLA guarantee.') }}</p>
                    <a href="{{ route('support.create') }}" class="block text-center w-full py-1.5 bg-red-600 text-white font-bold rounded-lg text-[11px] hover:bg-red-700 transition">
                        {{ __('Open Support Ticket') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Content Area (Takes remaining width: 9 of 12 columns) -->
        <div class="lg:col-span-9 xl:col-span-9 space-y-6 w-full">

            <!-- Section 1: Overview & Workspace -->
            <div x-show="activeTab === 'overview'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6 w-full">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-squares-four"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">1. {{ __('Dashboard & Workspace Overview') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('Your commercial central command for billing intelligence and cash flow.') }}</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        {{ __('The Invoicy Dashboard provides real-time financial tracking, overdue monitoring, and quick action shortcuts designed to give agency operators complete clarity over accounts receivable.') }}
                    </p>

                    <!-- Real Feature Screenshot: Dashboard Overview -->
                    <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-xs bg-slate-900">
                        <img src="{{ asset('images/docs/dashboard-overview.png') }}" alt="{{ __('Invoicy Dashboard Overview Screenshot') }}" class="w-full object-contain max-h-[500px]">
                        <div class="p-3 bg-slate-50 text-[11px] text-slate-500 border-t border-slate-200 flex items-center gap-2">
                            <i class="ph ph-image text-slate-400"></i>
                            <span>{{ __('Figure 1.1: Live Invoicy Workspace Dashboard displaying financial KPIs, recent transactions, and quick action shortcuts.') }}</span>
                        </div>
                    </div>

                    <h4 class="font-bold text-slate-900 text-sm mt-4">{{ __('Business Benefits & Value:') }}</h4>
                    <ul class="list-disc list-inside space-y-1.5 pl-2">
                        <li><strong>{{ __('Instant Receivables Visibility:') }}</strong> {{ __('Know your total collected revenue, outstanding invoices, and overdue amounts at a glance.') }}</li>
                        <li><strong>{{ __('Reduced Days Sales Outstanding (DSO):') }}</strong> {{ __('Track client payment velocity and take immediate action on aging accounts.') }}</li>
                        <li><strong>{{ __('Multi-Tenant Isolation:') }}</strong> {{ __('Your business records, tax filings, and client databases are strictly partitioned and confidential.') }}</li>
                    </ul>

                    <h4 class="font-bold text-slate-900 text-sm mt-4">{{ __('How to Use the Dashboard:') }}</h4>
                    <ol class="list-decimal list-inside space-y-1.5 pl-2">
                        <li><strong>{{ __('Review Top KPIs:') }}</strong> {{ __('Total Revenue, Outstanding Balance, Overdue Count, and Active Retainers.') }}</li>
                        <li><strong>{{ __('Action Shortcuts:') }}</strong> {{ __('Use the primary buttons to instantly create an invoice, add a customer, or start a recurring retainer.') }}</li>
                        <li><strong>{{ __('Monitor Activity Stream:') }}</strong> {{ __('Review live status badges showing when customers view, pay, or dispute invoices.') }}</li>
                    </ol>
                </div>
            </div>

            <!-- Section 2: Invoices & Live Builder -->
            <div x-show="activeTab === 'invoices'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6 w-full">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-receipt"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">2. {{ __('Invoices Studio & Live Builder') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('Fast, compliant commercial invoice creation with automatic safeguards.') }}</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        {{ __('The Invoicy Invoice Builder combines fluid Livewire reactivity with automated tax calculation, sequential numbering, and pre-dispatch quality verification.') }}
                    </p>

                    <!-- Real Feature Screenshot: Invoice Builder -->
                    <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-xs bg-slate-900">
                        <img src="{{ asset('images/docs/invoice-builder.jpg') }}" alt="{{ __('Invoicy Invoice Builder Screenshot') }}" class="w-full object-contain max-h-[600px]">
                        <div class="p-3 bg-slate-50 text-[11px] text-slate-500 border-t border-slate-200 flex items-center gap-2">
                            <i class="ph ph-image text-slate-400"></i>
                            <span>{{ __('Figure 2.1: Complete Invoicy Invoice Builder with client selector, line items, 1-click presets, tax calculations, and options.') }}</span>
                        </div>
                    </div>

                    <!-- Second Feature Screenshot: Invoices Table -->
                    <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-xs bg-slate-900 mt-4">
                        <img src="{{ asset('images/docs/invoices-list.png') }}" alt="{{ __('Invoices Table and Bulk Management Screenshot') }}" class="w-full object-contain max-h-[400px]">
                        <div class="p-3 bg-slate-50 text-[11px] text-slate-500 border-t border-slate-200 flex items-center gap-2">
                            <i class="ph ph-image text-slate-400"></i>
                            <span>{{ __('Figure 2.2: Commercial Invoices Ledger with status filters (All, Issued, Paid, Partially Paid, Drafts) and bulk actions.') }}</span>
                        </div>
                    </div>

                    <h4 class="font-bold text-slate-900 text-sm mt-4">{{ __('Business Benefits & Value:') }}</h4>
                    <ul class="list-disc list-inside space-y-1.5 pl-2">
                        <li><strong>{{ __('Zero Calculation Errors:') }}</strong> {{ __('Real-time automated math backed by integer-cent precision prevents rounding discrepancies.') }}</li>
                        <li><strong>{{ __('Compliant Tax Filing:') }}</strong> {{ __('Automatically adjusts to regional taxes (Canadian HST/QST, European VAT, UAE/Saudi VAT).') }}</li>
                        <li><strong>{{ __('Brand Excellence:') }}</strong> {{ __('Delivers clean, professional invoice designs customized with your logo, brand colors, and payment terms.') }}</li>
                    </ul>

                    <h4 class="font-bold text-slate-900 text-sm mt-4">{{ __('Step-by-Step Invoice Creation Guide:') }}</h4>
                    <ol class="list-decimal list-inside space-y-1.5 pl-2">
                        <li><strong>{{ __('Select Customer:') }}</strong> {{ __('Choose an existing client from your directory, or click "+ Add New Client" to create one inline.') }}</li>
                        <li><strong>{{ __('Set Dates & Currency:') }}</strong> {{ __('Choose the issue date, payment due date (e.g. Net 14, Net 30), and billing currency.') }}</li>
                        <li><strong>{{ __('Configure Line Items:') }}</strong> {{ __('Add products or consulting services. Specify quantities, rates, and unit types (hours, units, months).') }}</li>
                        <li><strong>{{ __('Apply Discounts:') }}</strong> {{ __('Enter an item-specific discount or overall percentage promo code.') }}</li>
                        <li><strong>{{ __('Review Taxes:') }}</strong> {{ __('Verify the automated tax rate or select tax exemption for qualified non-profit clients.') }}</li>
                        <li><strong>{{ __('Click "Issue & Publish":') }}</strong> {{ __('Review the Pre-Dispatch summary, choose whether to notify the client via email, and confirm.') }}</li>
                    </ol>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                        <div class="text-[11px] font-bold text-slate-900 flex items-center gap-2">
                            <i class="ph ph-shield-check text-red-600 text-base"></i> {{ __('Safeguard Notice: Draft & Empty Protection') }}
                        </div>
                        <p class="text-[11px] text-slate-500">
                            {{ __('Saved draft invoices and invoices without line items have their public customer portals and PDF downloads automatically locked. Customers can only view the invoice once you officially Issue and Publish it.') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Section 3: QR Code & Verification -->
            <div x-show="activeTab === 'verification'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6 w-full">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-qr-code"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">3. {{ __('QR Code & Authentic Verification') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('Cryptographically verifiable digital proof on every invoice.') }}</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        {{ __('Every commercial invoice generated by Invoicy automatically includes an authentic 2D QR code and cryptographic fingerprint seal.') }}
                    </p>

                    <!-- Real Feature Screenshot: QR Code & Verification -->
                    <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-xs bg-slate-900">
                        <img src="{{ asset('images/docs/invoice-verification-qr.png') }}" alt="{{ __('Invoice Verification & QR Code Screenshot') }}" class="w-full object-contain max-h-[500px]">
                        <div class="p-3 bg-slate-50 text-[11px] text-slate-500 border-t border-slate-200 flex items-center gap-2">
                            <i class="ph ph-image text-slate-400"></i>
                            <span>{{ __('Figure 3.1: Issued invoice screen highlighting the Authentic Verification QR Code seal, automated reminder tracker, and pay portal.') }}</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row items-center gap-4">
                        <div class="w-16 h-16 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-3xl text-slate-800 shadow-inner shrink-0">
                            <i class="ph ph-qr-code"></i>
                        </div>
                        <div>
                            <div class="font-bold text-slate-900 text-sm">{{ __('Instant Smartphone Authenticity Scan') }}</div>
                            <p class="text-slate-500 text-[11px] mt-1">
                                {{ __('When a customer, auditor, or bank scans the QR code with any camera, they are routed to the official Invoicy Verification Portal displaying the uncompromised issuer details, current payment status, and cryptographic SHA-256 fingerprint.') }}
                            </p>
                        </div>
                    </div>

                    <h4 class="font-bold text-slate-900 text-sm">{{ __('Where does the QR Code appear?') }}</h4>
                    <ul class="list-disc list-inside space-y-1.5 pl-2">
                        <li><strong>{{ __('Official PDF Downloads:') }}</strong> {{ __('Displayed alongside the official seal and totals table.') }}</li>
                        <li><strong>{{ __('Invoice Details View:') }}</strong> {{ __('In the agency admin console with direct copy & verification links.') }}</li>
                        <li><strong>{{ __('Customer Payment Portal:') }}</strong> {{ __('Visible to clients when paying online.') }}</li>
                    </ul>
                </div>
            </div>

            <!-- Section 4: Invoice Recall & Pull Back -->
            <div x-show="activeTab === 'recall'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6 w-full">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-arrow-counter-clockwise"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">4. {{ __('Invoice Recall & Pull Back Option') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('Instantly retract an issued invoice if mistakes or amendments are required.') }}</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        {{ __('Made a mistake on an issued invoice? Invoicy allows you to perform an immediate Recall / Pull Back on any invoice that has not yet been paid.') }}
                    </p>

                    <div class="p-5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 space-y-3">
                        <div class="font-bold text-sm flex items-center gap-2">
                            <i class="ph ph-warning-diamond text-lg text-amber-700"></i> {{ __('What happens during an Invoice Recall:') }}
                        </div>
                        <ul class="list-disc list-inside space-y-2 text-xs pl-1">
                            <li><strong>{{ __('Instant Access Invalidation:') }}</strong> {{ __('The previous customer public URL and PDF download tokens are immediately revoked. Anyone visiting the old link is redirected to a secure retracted notice.') }}</li>
                            <li><strong>{{ __('Status Reversion to Draft:') }}</strong> {{ __('The invoice status automatically reverts to Draft, unlocking all fields so you can edit line items, discounts, or dates freely.') }}</li>
                            <li><strong>{{ __('Customer Retraction Notification:') }}</strong> {{ __('Optionally dispatches a courteous retraction notice to the customer email to notify them that a revised invoice is being prepared.') }}</li>
                        </ul>
                    </div>

                    <h4 class="font-bold text-slate-900 text-sm mt-4">{{ __('How to Recall an Invoice:') }}</h4>
                    <ol class="list-decimal list-inside space-y-1.5 pl-2">
                        <li>{{ __('Open any unpaid invoice in your Invoices dashboard.') }}</li>
                        <li>{{ __('Click the "Recall / Pull Back" button in the upper action bar.') }}</li>
                        <li>{{ __('Confirm the security dialog. The invoice reverts to Draft instantly.') }}</li>
                        <li>{{ __('Make your modifications and click "Issue & Publish" to generate a fresh, secure document.') }}</li>
                    </ol>
                </div>
            </div>

            <!-- Section 5: Live View Alerts & Portal -->
            <div x-show="activeTab === 'tracking'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6 w-full">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-eye"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">5. {{ __('Live Client View Alerts & Portal Engagement') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('Know the exact minute your client opens their invoice.') }}</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        {{ __('Never wonder whether a client received your bill. Invoicy includes high-precision dual-channel engagement tracking:') }}
                    </p>

                    <!-- Real Feature Screenshot: Email Notification -->
                    <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-xs bg-slate-900">
                        <img src="{{ asset('images/docs/email-dispatch.png') }}" alt="{{ __('Email Dispatch Notification Screenshot') }}" class="w-full object-contain max-h-[500px]">
                        <div class="p-3 bg-slate-50 text-[11px] text-slate-500 border-t border-slate-200 flex items-center gap-2">
                            <i class="ph ph-image text-slate-400"></i>
                            <span>{{ __('Figure 5.1: Real invoice email dispatch delivered to client with View & Pay Online button, PDF download, and authentic security seal.') }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50">
                            <div class="font-bold text-slate-900 text-xs mb-1 flex items-center gap-1.5">
                                <i class="ph ph-envelope-open text-sky-600"></i> {{ __('Email Open Tracking') }}
                            </div>
                            <p class="text-[11px] text-slate-500">{{ __('An invisible tracking beacon records the precise timestamp when the client opens your email.') }}</p>
                        </div>
                        <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50">
                            <div class="font-bold text-slate-900 text-xs mb-1 flex items-center gap-1.5">
                                <i class="ph ph-globe text-emerald-600"></i> {{ __('Portal View Tracking') }}
                            </div>
                            <p class="text-[11px] text-slate-500">{{ __('Records when the customer visits the online portal or downloads the PDF.') }}</p>
                        </div>
                    </div>
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-[11px] font-semibold flex items-center gap-2">
                        <i class="ph ph-bell-ringing text-lg text-emerald-600 shrink-0"></i>
                        <span>{{ __('On first view, an automated alert email is dispatched directly to the invoice creator with the client name and timestamp!') }}</span>
                    </div>
                </div>
            </div>

            <!-- Section 6: Payments & Receipts -->
            <div x-show="activeTab === 'payments'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6 w-full">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-hand-coins"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">6. {{ __('Payments, Partial Settlements & Receipts') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('Record settlements, track partial balances, and issue automated receipts.') }}</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        {{ __('The Invoicy payment ledger supports both online card settlements (via Stripe / PayPal) and manual offline settlements (Bank Transfer, Cash, Check, Wire).') }}
                    </p>

                    <!-- Real Feature Screenshot: Payment Gateway Configuration -->
                    <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-xs bg-slate-900">
                        <img src="{{ asset('images/docs/payment-gateways.png') }}" alt="{{ __('Payment Gateways Configuration Screenshot') }}" class="w-full object-contain max-h-[450px]">
                        <div class="p-3 bg-slate-50 text-[11px] text-slate-500 border-t border-slate-200 flex items-center gap-2">
                            <i class="ph ph-image text-slate-400"></i>
                            <span>{{ __('Figure 6.1: Agency Settings Payment Gateway API Keys configuration for Stripe and PayPal merchant accounts.') }}</span>
                        </div>
                    </div>

                    <h4 class="font-bold text-slate-900 text-sm mt-4">{{ __('Key Payment Features:') }}</h4>
                    <ul class="list-disc list-inside space-y-1.5 pl-2">
                        <li><strong>{{ __('Partial Payment Handling:') }}</strong> {{ __('Clients can pay installments or deposits. The invoice status automatically shifts to Partially Paid, and the remaining balance is tracked down to the exact cent.') }}</li>
                        <li><strong>{{ __('Overpayment Protection:') }}</strong> {{ __('Built-in financial guards reject payment entries that exceed the outstanding balance.') }}</li>
                        <li><strong>{{ __('Automated PDF Receipts:') }}</strong> {{ __('Every recorded payment automatically issues an official payment receipt with transaction reference.') }}</li>
                        <li><strong>{{ __('Idempotent Webhook Processing:') }}</strong> {{ __('Online gateway webhooks are protected by cryptographic idempotency keys to eliminate double-charging.') }}</li>
                    </ul>

                    <h4 class="font-bold text-slate-900 text-sm mt-4">{{ __('How to Record a Payment:') }}</h4>
                    <ol class="list-decimal list-inside space-y-1.5 pl-2">
                        <li>{{ __('Open the target invoice from the Invoices directory.') }}</li>
                        <li>{{ __('Click "Record Payment" in the action bar.') }}</li>
                        <li>{{ __('Enter the received amount, payment method, date, and external transaction reference (e.g. Wire transfer number).') }}</li>
                        <li>{{ __('Click "Confirm Payment". Balance due and status badges update instantaneously.') }}</li>
                    </ol>
                </div>
            </div>

            <!-- Section 7: Recurring Retainers -->
            <div x-show="activeTab === 'retainers'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6 w-full">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-arrows-clockwise"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">7. {{ __('Recurring Retainers & Subscriptions') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('Automate recurring client billings on monthly, quarterly, or annual schedules.') }}</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        {{ __('Retainers automate your cash flow by auto-generating invoices on your defined billing cycle without manual intervention.') }}
                    </p>
                    <ul class="list-disc list-inside space-y-1.5 pl-2">
                        <li><strong>{{ __('Flexible Frequencies:') }}</strong> {{ __('Weekly, Monthly, Quarterly, Bi-Annually, or Yearly.') }}</li>
                        <li><strong>{{ __('Pre-Send Vendor Review Alert:') }}</strong> {{ __('On the 1st of each month, an alert prompts the vendor to check for extra work before the invoice is finalized and emailed to the customer.') }}</li>
                        <li><strong>{{ __('Pause / Resume:') }}</strong> {{ __('Put retainers on hold during project pauses with single-click reactivation.') }}</li>
                    </ul>
                </div>
            </div>

            <!-- Section 8: Credit Notes & Refunds -->
            <div x-show="activeTab === 'credit_notes'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6 w-full">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-credit-card"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">8. {{ __('Credit Notes & Ledger Balancing') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('Issue commercial credit notes to offset invoices or record customer refunds.') }}</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        {{ __('When adjustments, project scope changes, or partial refunds occur, Invoicy allows you to issue compliant Credit Notes rather than altering paid accounting records.') }}
                    </p>
                    <ul class="list-disc list-inside space-y-1.5 pl-2">
                        <li><strong>{{ __('Invoice-Linked Credit Notes:') }}</strong> {{ __('Issue credit directly against an issued invoice to immediately reduce its outstanding balance.') }}</li>
                        <li><strong>{{ __('Standalone Credit Notes:') }}</strong> {{ __('Maintain open customer credit that can be applied to future invoices.') }}</li>
                        <li><strong>{{ __('Audit Compliant:') }}</strong> {{ __('Maintains immutable historical records to satisfy accounting audits and tax authorities.') }}</li>
                    </ul>
                </div>
            </div>

            <!-- Section 9: Client Directory & CRM -->
            <div x-show="activeTab === 'clients'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6 w-full">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-users"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">9. {{ __('Client CRM Directory & Financial Ledgers') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('Manage client contacts, billing preferences, tax exemption, and account balances.') }}</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        {{ __('The Client Directory is your agency customer database. Each client profile tracks contact information, preferred billing currency, tax status, and lifetime billing history.') }}
                    </p>
                    <ul class="list-disc list-inside space-y-1.5 pl-2">
                        <li><strong>{{ __('Tax Exemption Toggle:') }}</strong> {{ __('Mark registered non-profits or international clients as tax-exempt to automatically suppress sales taxes.') }}</li>
                        <li><strong>{{ __('Billing Currency Binding:') }}</strong> {{ __('Assign dedicated billing currencies (e.g. USD, EUR, CAD, GBP) per client.') }}</li>
                        <li><strong>{{ __('Statement of Account:') }}</strong> {{ __('Generate comprehensive client statements summarizing billed, paid, and outstanding balances across any date range.') }}</li>
                    </ul>
                </div>
            </div>

            <!-- Section 10: Services Catalog & Price Books -->
            <div x-show="activeTab === 'services'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6 w-full">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-package"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">10. {{ __('Services Catalog & Price Books') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('Standardize pricing and insert recurring consulting packages in one click.') }}</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        {{ __('Avoid re-typing item descriptions and consulting rates. Create catalog items for standard deliverables, consulting tiers, or hosting packages.') }}
                    </p>
                    <ul class="list-disc list-inside space-y-1.5 pl-2">
                        <li><strong>{{ __('Single-Click Insertion:') }}</strong> {{ __('In the Invoice Builder, simply select the service from the dropdown; description, unit price, and default tax rate populate instantly.') }}</li>
                        <li><strong>{{ __('45+ 1-Click Industry Presets:') }}</strong> {{ __('Pre-priced line items across 7 sectors (Computer Shop, Transportation, Limousine Taxi, Construction, Industrial, Distributor, and Marketing).') }}</li>
                    </ul>
                </div>
            </div>

            <!-- Section 11: Canadian & Global Taxes -->
            <div x-show="activeTab === 'taxes'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6 w-full">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-calculator"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">11. {{ __('Canadian Place-of-Supply & Global Tax Engine') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('Automated multi-jurisdiction tax calculation for Canada, UK, Europe, and the Middle East.') }}</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        {{ __('Invoicy features an intelligent Place-of-Supply tax calculation service that automatically resolves statutory tax rates based on client location:') }}
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                            <span class="font-bold text-slate-900 block">Ontario (ON): 13.00% HST</span>
                            <span class="text-slate-500 text-[11px]">{{ __('Harmonized Sales Tax single rate.') }}</span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                            <span class="font-bold text-slate-900 block">Quebec (QC): 14.975% Dual Tax</span>
                            <span class="text-slate-500 text-[11px]">{{ __('5.00% Federal GST + 9.975% Provincial QST.') }}</span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                            <span class="font-bold text-slate-900 block">British Columbia (BC): 12.00%</span>
                            <span class="text-slate-500 text-[11px]">{{ __('5.00% Federal GST + 7.00% Provincial PST.') }}</span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                            <span class="font-bold text-slate-900 block">Alberta & Territories: 5.00% GST</span>
                            <span class="text-slate-500 text-[11px]">{{ __('Federal GST only; 0% provincial tax.') }}</span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                            <span class="font-bold text-slate-900 block">United Kingdom: 20.00% VAT</span>
                            <span class="text-slate-500 text-[11px]">{{ __('Standard HMRC Value Added Tax.') }}</span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                            <span class="font-bold text-slate-900 block">Saudi Arabia & UAE: 15% / 5% VAT</span>
                            <span class="text-slate-500 text-[11px]">{{ __('Regional Middle East VAT with QR compliance.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 12: Automated Reminders -->
            <div x-show="activeTab === 'reminders'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6 w-full">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-bell"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">12. {{ __('Automated Payment Reminders') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('Prevent late payments with multi-stage automated email reminders.') }}</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        {{ __('The Reminders engine runs automated checks every hour to dispatch courteous reminders according to six standardized milestones:') }}
                    </p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-center text-xs">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="font-black text-slate-900 block text-base">45d</span>
                            <span class="text-slate-500 text-[10px]">{{ __('Early Notice') }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="font-black text-slate-900 block text-base">30d</span>
                            <span class="text-slate-500 text-[10px]">{{ __('Monthly Check') }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="font-black text-slate-900 block text-base">15d</span>
                            <span class="text-slate-500 text-[10px]">{{ __('Midterm Alert') }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900">
                            <span class="font-black text-amber-800 block text-base">7d</span>
                            <span class="text-amber-700 text-[10px]">{{ __('1 Week Left') }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900">
                            <span class="font-black text-amber-800 block text-base">3d</span>
                            <span class="text-amber-700 text-[10px]">{{ __('Urgent Due') }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-900">
                            <span class="font-black text-rose-800 block text-base">2d</span>
                            <span class="text-rose-700 text-[10px]">{{ __('Final Call') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 13: SMTP Configuration -->
            <div x-show="activeTab === 'smtp'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6 w-full">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-paper-plane"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">13. {{ __('Custom Outgoing SMTP & Live Testing') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('Send invoices from your own authenticated agency domain with 100% deliverability.') }}</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        {{ __('Subscribed agencies can configure their own authenticated Outgoing SMTP mail server in Agency Settings:') }}
                    </p>

                    <!-- Real Feature Screenshot: Agency Settings -->
                    <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-xs bg-slate-900">
                        <img src="{{ asset('images/docs/agency-settings.png') }}" alt="{{ __('Agency Settings and SMTP Screenshot') }}" class="w-full object-contain max-h-[500px]">
                        <div class="p-3 bg-slate-50 text-[11px] text-slate-500 border-t border-slate-200 flex items-center gap-2">
                            <i class="ph ph-image text-slate-400"></i>
                            <span>{{ __('Figure 13.1: Agency Settings console for Owner profile, Company details, and Outgoing SMTP server setup.') }}</span>
                        </div>
                    </div>

                    <ul class="list-disc list-inside space-y-1.5 pl-2">
                        <li><strong>{{ __('Host:') }}</strong> {{ __('Your mail server (e.g. mail.youragency.com, smtp.gmail.com).') }}</li>
                        <li><strong>{{ __('Port & Encryption:') }}</strong> {{ __('Port 465 with SSL or Port 587 with TLS.') }}</li>
                        <li><strong>{{ __('Authentication:') }}</strong> {{ __('Full encrypted storage of SMTP credentials.') }}</li>
                        <li><strong>{{ __('Live Test Delivery:') }}</strong> {{ __('Sends an official branded test email confirming operational status.') }}</li>
                    </ul>
                </div>
            </div>

            <!-- Section 14: Plan & Billing -->
            <div x-show="activeTab === 'billing'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6 w-full">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-credit-card"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">14. {{ __('Plan, Billing & Promotional Coupons') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('Manage agency subscription tiers, promo codes, and upgrade requests.') }}</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        {{ __('Manage your agency plan inside Plan & Billing. You can submit upgrade or downgrade requests with real-time status banners:') }}
                    </p>

                    <!-- Real Feature Screenshot: Subscriptions Management -->
                    <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-xs bg-slate-900">
                        <img src="{{ asset('images/docs/workspaces-subscriptions.png') }}" alt="{{ __('Tenant Workspaces & Subscriptions Screenshot') }}" class="w-full object-contain max-h-[450px]">
                        <div class="p-3 bg-slate-50 text-[11px] text-slate-500 border-t border-slate-200 flex items-center gap-2">
                            <i class="ph ph-image text-slate-400"></i>
                            <span>{{ __('Figure 14.1: Tenant Workspaces and Subscriptions live roster, showing plan tiers, renewal dates, and status.') }}</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-purple-50 border border-purple-200 text-purple-950 text-xs">
                        <strong>{{ __('Promotional Codes:') }}</strong> {{ __('Enter codes such as WELCOME50 or VIP2026 during checkout or registration to instantly unlock full service or discount perks.') }}
                    </div>
                </div>
            </div>

            <!-- Section 15: AI Agent -->
            <div x-show="activeTab === 'ai'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6 w-full">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-sparkle"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">15. {{ __('AI Agent Assistant') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('Your intelligent financial assistant ready 24/7.') }}</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        {{ __('Invoicy includes an enterprise AI Agent with both text and hands-free voice commands supporting English, Urdu, Roman Urdu, Arabic, Hindi, and Turkish.') }}
                    </p>

                    <!-- Real Feature Screenshot: AI Assistant -->
                    <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-xs bg-slate-900">
                        <img src="{{ asset('images/docs/ai-assistant.png') }}" alt="{{ __('AI Financial Assistant Screenshot') }}" class="w-full object-contain max-h-[500px]">
                        <div class="p-3 bg-slate-50 text-[11px] text-slate-500 border-t border-slate-200 flex items-center gap-2">
                            <i class="ph ph-image text-slate-400"></i>
                            <span>{{ __('Figure 15.1: Multi-lingual AI Copilot interface answering financial questions, providing shortcuts, and executing actions.') }}</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-900 text-white space-y-2">
                        <div class="text-[11px] font-bold text-red-400 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="ph ph-microphone"></i> {{ __('Try Saying These Voice Commands:') }}
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                            <div class="p-2.5 rounded-xl bg-slate-800 border border-slate-700 font-mono">"Create a new invoice"</div>
                            <div class="p-2.5 rounded-xl bg-slate-800 border border-slate-700 font-mono">"Go to settings"</div>
                            <div class="p-2.5 rounded-xl bg-slate-800 border border-slate-700 font-mono">"How do I recall an invoice?"</div>
                            <div class="p-2.5 rounded-xl bg-slate-800 border border-slate-700 font-mono">"What is authentic QR verification?"</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 16: Support Desk (24h SLA) -->
            <div x-show="activeTab === 'support'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6 w-full">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-lifebuoy"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">16. {{ __('Support Desk & 24h SLA Guarantee') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('Direct engineering assistance with guaranteed 24-hour turnaround and zero data loss.') }}</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        {{ __('Need assistance with a complex tax configuration, payment gateway setup, or custom invoicing feature? Our dedicated engineering support team operates directly within the application.') }}
                    </p>

                    <h4 class="font-bold text-slate-900 text-sm mt-4">{{ __('Our Service Level Commitments:') }}</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-950">
                            <div class="font-bold text-xs flex items-center gap-1.5 mb-1">
                                <i class="ph ph-clock-countdown text-emerald-600 text-base"></i> {{ __('24-Hour Turnaround SLA') }}
                            </div>
                            <p class="text-[11px] text-emerald-800">{{ __('Every support ticket is logged, triaged, and answered by senior engineers within 24 business hours.') }}</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-950">
                            <div class="font-bold text-xs flex items-center gap-1.5 mb-1">
                                <i class="ph ph-shield-check text-indigo-600 text-base"></i> {{ __('Zero Data Loss Architecture') }}
                            </div>
                            <p class="text-[11px] text-indigo-800">{{ __('All updates, schema migrations, and bug resolutions preserve existing invoices, ledgers, and client records intact.') }}</p>
                        </div>
                    </div>

                    <h4 class="font-bold text-slate-900 text-sm mt-4">{{ __('How to Open a Support Ticket:') }}</h4>
                    <ol class="list-decimal list-inside space-y-1.5 pl-2">
                        <li>{{ __('Navigate to Support Desk in the main navigation menu.') }}</li>
                        <li>{{ __('Click "Create New Ticket".') }}</li>
                        <li>{{ __('Select the category (Billing, Gateway, Invoicing, Tax Configuration, Feature Request).') }}</li>
                        <li>{{ __('Choose Priority (Low, Medium, High, Urgent).') }}</li>
                        <li>{{ __('Provide detailed context or screenshots, and click "Submit Ticket".') }}</li>
                    </ol>
                </div>
            </div>

            <!-- Section 17: Tenant Multi-Tenancy & RBAC Matrix (Moved from Documentation) -->
            <div x-show="activeTab === 'tenant_docs'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6 w-full">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-shield-check"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">17. {{ __('Platform Architecture & Multi-Tenancy') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('Cryptographically isolated agency workspaces and tenant scopes.') }}</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        {{ __('RedMaple is built on a strict multi-tenant schema where every agency operates in an isolated workspace container:') }}
                    </p>
                    <ul class="list-disc list-inside space-y-1.5 text-slate-600 pl-2">
                        <li><strong>{{ __('Tenant Scoping:') }}</strong> {{ __('All database queries are automatically scoped using WorkspaceScope, guaranteeing zero data leakage between agencies.') }}</li>
                        <li><strong>{{ __('Enterprise Authentication:') }}</strong> {{ __('Strong Bcrypt credential hashing and CSRF token verification on all mutation operations.') }}</li>
                        <li><strong>{{ __('Role-Based Access Control (RBAC):') }}</strong> {{ __('Permissions segregated by Owner, Admin, and Staff tiers.') }}</li>
                    </ul>

                    <!-- Visual Architecture Card -->
                    <div class="p-4 rounded-xl bg-slate-900 text-white font-mono text-xs space-y-2 border border-slate-800">
                        <div class="text-red-400 font-bold">// {{ __('Multi-Tenant Data Isolation Model') }}</div>
                        <div class="text-slate-300">Tenant Workspace [/agency-slug] &rarr; Isolated CRM Clients, Invoices, Retainers, SMTP & Bank Keys</div>
                        <div class="text-emerald-400">&check; Zero cross-tenant leakage &bull; Row-level workspace_id enforcement &bull; Cryptographic portal tokens</div>
                    </div>

                    <h4 class="font-bold text-slate-900 text-sm mt-4">{{ __('Role-Based Access Privileges Matrix:') }}</h4>
                    <div class="overflow-x-auto rounded-2xl border border-slate-200 mt-2">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[10px] border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3">{{ __('Privilege / Capability') }}</th>
                                    <th class="px-3 py-3 text-center text-red-700">{{ __('Primary Owner') }}</th>
                                    <th class="px-3 py-3 text-center text-blue-700">{{ __('Workspace Admin') }}</th>
                                    <th class="px-3 py-3 text-center text-emerald-700">{{ __('Staff Member') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-2.5 font-medium">{{ __('Create & Manage Invoices') }}</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; {{ __('Full') }}</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; {{ __('Full') }}</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; {{ __('Full') }}</td>
                                </tr>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-2.5 font-medium">{{ __('Client CRM & Service Catalog') }}</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; {{ __('Full') }}</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; {{ __('Full') }}</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; {{ __('Full') }}</td>
                                </tr>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-2.5 font-medium">{{ __('Record Customer Payments') }}</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; {{ __('Full') }}</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; {{ __('Full') }}</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; {{ __('Full') }}</td>
                                </tr>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-2.5 font-medium">{{ __('Recall / Pull Back Invoices') }}</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; {{ __('Full') }}</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; {{ __('Full') }}</td>
                                    <td class="px-3 py-2.5 text-center text-slate-400 font-bold">&mdash;</td>
                                </tr>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-2.5 font-medium">{{ __('Agency SMTP & Payment Keys') }}</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; {{ __('Full') }}</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; {{ __('Full') }}</td>
                                    <td class="px-3 py-2.5 text-center text-slate-400 font-bold">&mdash;</td>
                                </tr>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-2.5 font-medium">{{ __('Manage Team Members & Seats') }}</td>
                                    <td class="px-3 py-2.5 text-center text-emerald-600 font-bold">&check; {{ __('Full') }}</td>
                                    <td class="px-3 py-2.5 text-center text-slate-400 font-bold">&mdash;</td>
                                    <td class="px-3 py-2.5 text-center text-slate-400 font-bold">&mdash;</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Section 18: Developer REST API & Webhooks (Moved from Documentation) -->
            <div x-show="activeTab === 'developer_api'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6 w-full">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-code"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">18. {{ __('Developer REST API & Webhooks') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('Integrate programmatic invoicing and listen to real-time events.') }}</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        {{ __('The Invoicy platform exposes secure RESTful JSON APIs and HMAC-authenticated Webhook feeds for automated enterprise integration.') }}
                    </p>

                    <h4 class="font-bold text-slate-900 text-sm mt-4">{{ __('Authentication:') }}</h4>
                    <p>
                        {{ __('Authenticate all requests by including your secret Workspace API Bearer Token in the HTTP Authorization header:') }}
                    </p>
                    <div class="p-3 bg-slate-900 text-emerald-400 font-mono text-xs rounded-xl">
                        Authorization: Bearer rm_live_sk_893247fba98234c98234ef
                    </div>

                    <h4 class="font-bold text-slate-900 text-sm mt-4">{{ __('Primary Endpoints:') }}</h4>
                    <div class="space-y-3 font-mono text-[11px]">
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold">POST</span>
                                <span class="text-slate-800 font-bold">/api/v1/invoices</span>
                            </div>
                            <p class="font-sans text-slate-600 text-xs">{{ __('Creates a new invoice in draft or issued state with automatic tax and line item calculation.') }}</p>
                        </div>

                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold">GET</span>
                                <span class="text-slate-800 font-bold">/api/v1/invoices/{id}</span>
                            </div>
                            <p class="font-sans text-slate-600 text-xs">{{ __('Retrieves comprehensive invoice payload, payment status, view timestamps, and public tokens.') }}</p>
                        </div>

                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-2 py-0.5 rounded bg-purple-100 text-purple-800 font-bold">POST</span>
                                <span class="text-slate-800 font-bold">/api/v1/invoices/{id}/recall</span>
                            </div>
                            <p class="font-sans text-slate-600 text-xs">{{ __('Executes an immediate cryptographic recall on an unpaid issued invoice.') }}</p>
                        </div>
                    </div>

                    <h4 class="font-bold text-slate-900 text-sm mt-4">{{ __('Supported Webhook Events:') }}</h4>
                    <ul class="list-disc list-inside space-y-1.5 pl-2">
                        <li><code>invoice.issued</code> &mdash; {{ __('Fired when an invoice is officially published and customer token is generated.') }}</li>
                        <li><code>invoice.viewed</code> &mdash; {{ __('Fired on first client open of the email, portal, or PDF.') }}</li>
                        <li><code>payment.recorded</code> &mdash; {{ __('Fired when a payment is processed via Stripe, PayPal, or manual ledger entry.') }}</li>
                        <li><code>invoice.recalled</code> &mdash; {{ __('Fired when an invoice is pulled back and security tokens are destroyed.') }}</li>
                    </ul>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
