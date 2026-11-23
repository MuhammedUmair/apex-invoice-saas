@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-8" x-data="{
    activeTab: '{{ $activeTab ?? 'overview' }}',
    searchQuery: '',
    matchesSearch(title, text) {
        if (!this.searchQuery) return true;
        const q = this.searchQuery.toLowerCase();
        return title.toLowerCase().includes(q) || text.toLowerCase().includes(q);
    }
}">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-red-950 text-white rounded-3xl p-8 sm:p-10 shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-red-600/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-600/30 border border-red-500/30 text-red-300 text-xs font-bold uppercase tracking-wider mb-3">
                <i class="ph ph-book-open text-sm"></i> Help Center & Complete System Manual
            </div>
            <h1 class="text-3xl sm:text-4xl font-black tracking-tight mb-2">Invoicy Knowledge Base & Guide</h1>
            <p class="text-slate-300 text-sm leading-relaxed mb-6">
                Master all 16 enterprise modules of Invoicy by RedMaple. Explore detailed feature breakdowns, business benefits, step-by-step usage guides, and interactive visual walkthroughs.
            </p>

            <!-- Search Filter Bar -->
            <div class="relative">
                <i class="ph ph-magnifying-glass text-slate-400 absolute left-4 top-1/2 -translate-y-1/2 text-lg"></i>
                <input type="text" x-model="searchQuery" placeholder="Search guides, modules, or features (e.g., 'QR code', 'Recall', 'Taxes', 'Payments', 'Credit Note')..."
                       class="w-full pl-11 pr-4 py-3 bg-white/10 border border-white/20 backdrop-blur-md rounded-2xl text-white placeholder-slate-400 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-red-500 transition">
            </div>
        </div>
    </div>

    <!-- Navigation Tabs & Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left Sidebar Navigation -->
        <div class="lg:col-span-4 bg-white rounded-3xl border border-slate-200 p-4 shadow-xs sticky top-6 space-y-1">
            <div class="px-3 py-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                Core Billing Modules
            </div>

            <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-squares-four text-base text-red-600"></i>
                    <span>1. Dashboard & Workspace</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'invoices'" :class="activeTab === 'invoices' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-receipt text-base text-red-600"></i>
                    <span>2. Invoices & Live Builder</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'verification'" :class="activeTab === 'verification' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-qr-code text-base text-red-600"></i>
                    <span>3. QR Code & Verification</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'recall'" :class="activeTab === 'recall' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-arrow-counter-clockwise text-base text-amber-600"></i>
                    <span>4. Invoice Recall & Pull Back</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'tracking'" :class="activeTab === 'tracking' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-eye text-base text-sky-600"></i>
                    <span>5. Live View Alerts & Portal</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'payments'" :class="activeTab === 'payments' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-hand-coins text-base text-emerald-600"></i>
                    <span>6. Payments & Receipts</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'retainers'" :class="activeTab === 'retainers' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-arrows-clockwise text-base text-emerald-600"></i>
                    <span>7. Recurring Retainers</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'credit_notes'" :class="activeTab === 'credit_notes' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-credit-card text-base text-rose-600"></i>
                    <span>8. Credit Notes & Refunds</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <div class="px-3 pt-3 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                Management & Operations
            </div>

            <button @click="activeTab = 'clients'" :class="activeTab === 'clients' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-users text-base text-indigo-600"></i>
                    <span>9. Client Directory & CRM</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'services'" :class="activeTab === 'services' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-package text-base text-amber-600"></i>
                    <span>10. Services & Price Books</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'taxes'" :class="activeTab === 'taxes' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-calculator text-base text-emerald-600"></i>
                    <span>11. Canadian & Global Taxes</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'reminders'" :class="activeTab === 'reminders' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-bell text-base text-amber-600"></i>
                    <span>12. Automated Reminders</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'smtp'" :class="activeTab === 'smtp' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-paper-plane text-base text-blue-600"></i>
                    <span>13. Custom SMTP & Email</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'billing'" :class="activeTab === 'billing' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-credit-card text-base text-purple-600"></i>
                    <span>14. Plan, Billing & Coupons</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'ai'" :class="activeTab === 'ai' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-sparkle text-base text-red-600"></i>
                    <span>15. AI Agent Assistant</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'support'" :class="activeTab === 'support' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-lifebuoy text-base text-rose-600"></i>
                    <span>16. Support Desk (24h SLA)</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <!-- Quick Action Box -->
            <div class="pt-4 border-t border-slate-100 px-2">
                <div class="p-3 bg-red-50/70 rounded-2xl border border-red-100 text-xs">
                    <div class="font-bold text-red-900 flex items-center gap-1.5 mb-1">
                        <i class="ph ph-headset text-sm text-red-600"></i> Dedicated Support Desk
                    </div>
                    <p class="text-slate-600 text-[11px] mb-2">Need direct engineering assistance? Submit a ticket under our 24h SLA guarantee.</p>
                    <a href="{{ route('support.create') }}" class="block text-center w-full py-1.5 bg-red-600 text-white font-bold rounded-lg text-[11px] hover:bg-red-700 transition">
                        Open Support Ticket
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Content Area -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Section 1: Overview & Workspace -->
            <div x-show="activeTab === 'overview'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-squares-four"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">1. Dashboard & Workspace Overview</h2>
                        <p class="text-xs text-slate-500">Your commercial central command for billing intelligence and cash flow.</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        The Invoicy Dashboard provides real-time financial tracking, overdue monitoring, and quick action shortcuts designed to give agency operators complete clarity over accounts receivable.
                    </p>

                    <!-- Real Screenshot Showcase -->
                    <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
                        <img src="{{ asset('images/docs/dashboard-overview.png') }}" alt="Invoicy Dashboard Overview Screenshot" class="w-full object-cover">
                        <div class="p-3 bg-slate-50 text-[11px] text-slate-500 border-t border-slate-200 flex items-center gap-2">
                            <i class="ph ph-image text-slate-400"></i>
                            <span>Figure 1.1: Live Invoicy Workspace Dashboard displaying financial KPIs, recent transactions, and quick action shortcuts.</span>
                        </div>
                    </div>

                    <h4 class="font-bold text-slate-900 text-sm mt-4">Business Benefits & Value:</h4>
                    <ul class="list-disc list-inside space-y-1.5 pl-2">
                        <li><strong>Instant Receivables Visibility:</strong> Know your total collected revenue, outstanding invoices, and overdue amounts at a glance.</li>
                        <li><strong>Reduced Days Sales Outstanding (DSO):</strong> Track client payment velocity and take immediate action on aging accounts.</li>
                        <li><strong>Multi-Tenant Isolation:</strong> Your business records, tax filings, and client databases are strictly partitioned and confidential.</li>
                    </ul>

                    <h4 class="font-bold text-slate-900 text-sm mt-4">How to Use the Dashboard:</h4>
                    <ol class="list-decimal list-inside space-y-1.5 pl-2">
                        <li><strong>Review Top KPIs:</strong> Total Revenue, Outstanding Balance, Overdue Count, and Active Retainers.</li>
                        <li><strong>Action Shortcuts:</strong> Use the primary buttons to instantly create an invoice, add a customer, or start a recurring retainer.</li>
                        <li><strong>Monitor Activity Stream:</strong> Review live status badges showing when customers view, pay, or dispute invoices.</li>
                    </ol>
                </div>
            </div>

            <!-- Section 2: Invoices & Live Builder -->
            <div x-show="activeTab === 'invoices'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-receipt"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">2. Invoices Studio & Live Builder</h2>
                        <p class="text-xs text-slate-500">Fast, compliant commercial invoice creation with automatic safeguards.</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        The Invoicy Invoice Builder combines fluid Livewire reactivity with automated tax calculation, sequential numbering, and pre-dispatch quality verification.
                    </p>

                    <!-- Real Screenshot Showcase -->
                    <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
                        <img src="{{ asset('images/docs/invoice-builder.png') }}" alt="Invoicy Invoice Builder Screenshot" class="w-full object-cover">
                        <div class="p-3 bg-slate-50 text-[11px] text-slate-500 border-t border-slate-200 flex items-center gap-2">
                            <i class="ph ph-image text-slate-400"></i>
                            <span>Figure 2.1: Invoicy Livewire Invoice Builder featuring real-time tax breakdowns, line item rows, and pre-dispatch controls.</span>
                        </div>
                    </div>

                    <h4 class="font-bold text-slate-900 text-sm mt-4">Business Benefits & Value:</h4>
                    <ul class="list-disc list-inside space-y-1.5 pl-2">
                        <li><strong>Zero Calculation Errors:</strong> Real-time automated math backed by integer-cent precision prevents rounding discrepancies.</li>
                        <li><strong>Compliant Tax Filing:</strong> Automatically adjusts to regional taxes (Canadian HST/QST, European VAT, UAE/Saudi VAT).</li>
                        <li><strong>Brand Excellence:</strong> Delivers clean, professional invoice designs customized with your logo, brand colors, and payment terms.</li>
                    </ul>

                    <h4 class="font-bold text-slate-900 text-sm mt-4">Step-by-Step Invoice Creation Guide:</h4>
                    <ol class="list-decimal list-inside space-y-1.5 pl-2">
                        <li><strong>Select Customer:</strong> Choose an existing client from your directory, or click <em>"+ Add New Client"</em> to create one inline.</li>
                        <li><strong>Set Dates & Currency:</strong> Choose the issue date, payment due date (e.g. Net 14, Net 30), and billing currency.</li>
                        <li><strong>Configure Line Items:</strong> Add products or consulting services. Specify quantities, rates, and unit types (hours, units, months).</li>
                        <li><strong>Apply Discounts:</strong> Enter an item-specific discount or overall percentage promo code.</li>
                        <li><strong>Review Taxes:</strong> Verify the automated tax rate or select tax exemption for qualified non-profit clients.</li>
                        <li><strong>Click "Issue & Publish":</strong> Review the Pre-Dispatch summary, choose whether to notify the client via email, and confirm.</li>
                    </ol>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                        <div class="text-[11px] font-bold text-slate-900 flex items-center gap-2">
                            <i class="ph ph-shield-check text-red-600 text-base"></i> Safeguard Notice: Draft & Empty Protection
                        </div>
                        <p class="text-[11px] text-slate-500">
                            Saved draft invoices and invoices without line items have their public customer portals and PDF downloads automatically locked. Customers can only view the invoice once you officially Issue and Publish it.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Section 3: QR Code & Verification -->
            <div x-show="activeTab === 'verification'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-qr-code"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">3. QR Code & Authentic Verification</h2>
                        <p class="text-xs text-slate-500">Cryptographically verifiable digital proof on every invoice.</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        Every commercial invoice generated by Invoicy automatically includes an authentic 2D QR code and cryptographic fingerprint seal.
                    </p>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row items-center gap-4">
                        <div class="w-20 h-20 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-3xl text-slate-800 shadow-inner shrink-0">
                            <i class="ph ph-qr-code"></i>
                        </div>
                        <div>
                            <div class="font-bold text-slate-900 text-sm">Instant Smartphone Authenticity Scan</div>
                            <p class="text-slate-500 text-[11px] mt-1">
                                When a customer, auditor, or bank scans the QR code with any camera, they are routed to the official Invoicy Verification Portal displaying the uncompromised issuer details, current payment status, and cryptographic SHA-256 fingerprint.
                            </p>
                        </div>
                    </div>

                    <h4 class="font-bold text-slate-900 text-sm">Where does the QR Code appear?</h4>
                    <ul class="list-disc list-inside space-y-1.5 pl-2">
                        <li><strong>Official PDF Downloads:</strong> Displayed alongside the official seal and totals table.</li>
                        <li><strong>Invoice Details View:</strong> In the agency admin console with direct copy & verification links.</li>
                        <li><strong>Customer Payment Portal:</strong> Visible to clients when paying online.</li>
                    </ul>
                </div>
            </div>

            <!-- Section 4: Invoice Recall & Pull Back -->
            <div x-show="activeTab === 'recall'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-arrow-counter-clockwise"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">4. Invoice Recall & Pull Back Option</h2>
                        <p class="text-xs text-slate-500">Instantly retract an issued invoice if mistakes or amendments are required.</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        Made a mistake on an issued invoice? Invoicy allows you to perform an immediate <strong>Recall / Pull Back</strong> on any invoice that has not yet been paid.
                    </p>

                    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 space-y-2">
                        <div class="font-bold text-sm flex items-center gap-1.5">
                            <i class="ph ph-warning"></i> What happens during a Recall:
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-[11px] pl-1">
                            <li><strong>Access Revocation:</strong> The previous customer public URL and PDF download tokens are instantly destroyed. Anyone clicking old email links will see an access restricted notice.</li>
                            <li><strong>Status Reversion:</strong> The invoice status reverts back to <strong>Draft</strong>, allowing you to freely modify line items, prices, and terms.</li>
                            <li><strong>Client Notification:</strong> If checked, an automated branded retraction notice is emailed to the client explaining the update.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Section 5: Live View Alerts & Portal -->
            <div x-show="activeTab === 'tracking'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-eye"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">5. Live Client View Alerts & Portal Engagement</h2>
                        <p class="text-xs text-slate-500">Know the exact minute your client opens their invoice.</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        Never wonder whether a client received your bill. Invoicy includes high-precision dual-channel engagement tracking:
                    </p>

                    <!-- Real Screenshot Showcase -->
                    <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
                        <img src="{{ asset('images/docs/invoice-portal.png') }}" alt="Customer Invoice Portal Screenshot" class="w-full object-cover">
                        <div class="p-3 bg-slate-50 text-[11px] text-slate-500 border-t border-slate-200 flex items-center gap-2">
                            <i class="ph ph-image text-slate-400"></i>
                            <span>Figure 5.1: Customer Public Portal view allowing clients to view invoice line items, download official PDFs, and pay balances.</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50">
                            <div class="font-bold text-slate-900 text-xs mb-1 flex items-center gap-1.5">
                                <i class="ph ph-envelope-open text-sky-600"></i> Email Open Tracking
                            </div>
                            <p class="text-[11px] text-slate-500">An invisible tracking beacon records the precise timestamp when the client opens your email.</p>
                        </div>
                        <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50">
                            <div class="font-bold text-slate-900 text-xs mb-1 flex items-center gap-1.5">
                                <i class="ph ph-globe text-emerald-600"></i> Portal View Tracking
                            </div>
                            <p class="text-[11px] text-slate-500">Records when the customer visits the online portal or downloads the PDF.</p>
                        </div>
                    </div>
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-[11px] font-semibold flex items-center gap-2">
                        <i class="ph ph-bell-ringing text-lg text-emerald-600 shrink-0"></i>
                        <span>On first view, an automated alert email is dispatched directly to the invoice creator with the client's name and timestamp!</span>
                    </div>
                </div>
            </div>

            <!-- Section 6: Payments & Receipts -->
            <div x-show="activeTab === 'payments'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-hand-coins"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">6. Payments, Partial Settlements & Receipts</h2>
                        <p class="text-xs text-slate-500">Record settlements, track partial balances, and issue automated receipts.</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        The Invoicy payment ledger supports both online card settlements (via Stripe / PayPal) and manual offline settlements (Bank Transfer, Cash, Check, Wire).
                    </p>

                    <h4 class="font-bold text-slate-900 text-sm mt-4">Key Payment Features:</h4>
                    <ul class="list-disc list-inside space-y-1.5 pl-2">
                        <li><strong>Partial Payment Handling:</strong> Clients can pay installments or deposits. The invoice status automatically shifts to <code>Partially Paid</code>, and the remaining balance is tracked down to the exact cent.</li>
                        <li><strong>Overpayment Protection:</strong> Built-in financial guards reject payment entries that exceed the outstanding balance.</li>
                        <li><strong>Automated PDF Receipts:</strong> Every recorded payment automatically issues an official payment receipt with transaction reference.</li>
                        <li><strong>Idempotent Webhook Processing:</strong> Online gateway webhooks are protected by cryptographic idempotency keys to eliminate double-charging.</li>
                    </ul>

                    <h4 class="font-bold text-slate-900 text-sm mt-4">How to Record a Payment:</h4>
                    <ol class="list-decimal list-inside space-y-1.5 pl-2">
                        <li>Open the target invoice from the Invoices directory.</li>
                        <li>Click <strong>"Record Payment"</strong> in the action bar.</li>
                        <li>Enter the received amount, payment method, date, and external transaction reference (e.g. Wire transfer number).</li>
                        <li>Click <strong>"Confirm Payment"</strong>. Balance due and status badges update instantaneously.</li>
                    </ol>
                </div>
            </div>

            <!-- Section 7: Recurring Retainers -->
            <div x-show="activeTab === 'retainers'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-arrows-clockwise"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">7. Recurring Retainers & Subscriptions</h2>
                        <p class="text-xs text-slate-500">Automate recurring client billings on monthly, quarterly, or annual schedules.</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        Retainers automate your cash flow by auto-generating invoices on your defined billing cycle without manual intervention.
                    </p>
                    <ul class="list-disc list-inside space-y-1.5 pl-2">
                        <li><strong>Flexible Frequencies:</strong> Weekly, Monthly, Quarterly, Bi-Annually, or Yearly.</li>
                        <li><strong>Auto-Email Dispatch:</strong> Automatically sends invoice to client when cycle generates.</li>
                        <li><strong>Pause / Resume:</strong> Put retainers on hold during project pauses with single-click reactivation.</li>
                    </ul>
                </div>
            </div>

            <!-- Section 8: Credit Notes & Refunds -->
            <div x-show="activeTab === 'credit_notes'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-credit-card"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">8. Credit Notes & Ledger Balancing</h2>
                        <p class="text-xs text-slate-500">Issue commercial credit notes to offset invoices or record customer refunds.</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        When adjustments, project scope changes, or partial refunds occur, Invoicy allows you to issue compliant Credit Notes rather than altering paid accounting records.
                    </p>
                    <ul class="list-disc list-inside space-y-1.5 pl-2">
                        <li><strong>Invoice-Linked Credit Notes:</strong> Issue credit directly against an issued invoice to immediately reduce its outstanding balance.</li>
                        <li><strong>Standalone Credit Notes:</strong> Maintain open customer credit that can be applied to future invoices.</li>
                        <li><strong>Audit Compliant:</strong> Maintains immutable historical records to satisfy accounting audits and tax authorities.</li>
                    </ul>
                </div>
            </div>

            <!-- Section 9: Client Directory & CRM -->
            <div x-show="activeTab === 'clients'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-users"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">9. Client CRM Directory & Financial Ledgers</h2>
                        <p class="text-xs text-slate-500">Manage client contacts, billing preferences, tax exemption, and account balances.</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        The Client Directory is your agency customer database. Each client profile tracks contact information, preferred billing currency, tax status, and lifetime billing history.
                    </p>
                    <ul class="list-disc list-inside space-y-1.5 pl-2">
                        <li><strong>Tax Exemption Toggle:</strong> Mark registered non-profits or international clients as tax-exempt to automatically suppress sales taxes.</li>
                        <li><strong>Billing Currency Binding:</strong> Assign dedicated billing currencies (e.g. USD, EUR, CAD, GBP) per client.</li>
                        <li><strong>Statement of Account:</strong> Generate comprehensive client statements summarizing billed, paid, and outstanding balances across any date range.</li>
                    </ul>
                </div>
            </div>

            <!-- Section 10: Services Catalog & Price Books -->
            <div x-show="activeTab === 'services'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-package"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">10. Services Catalog & Price Books</h2>
                        <p class="text-xs text-slate-500">Standardize pricing and insert recurring consulting packages in one click.</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        Avoid re-typing item descriptions and consulting rates. Create catalog items for standard deliverables, consulting tiers, or hosting packages.
                    </p>
                    <ul class="list-disc list-inside space-y-1.5 pl-2">
                        <li><strong>Single-Click Insertion:</strong> In the Invoice Builder, simply select the service from the dropdown; description, unit price, and default tax rate populate instantly.</li>
                        <li><strong>Price Tiering:</strong> Set fixed rates, hourly rates, or monthly retainer package rates.</li>
                    </ul>
                </div>
            </div>

            <!-- Section 11: Canadian & Global Taxes -->
            <div x-show="activeTab === 'taxes'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-calculator"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">11. Canadian Place-of-Supply & Global Tax Engine</h2>
                        <p class="text-xs text-slate-500">Automated multi-jurisdiction tax calculation for Canada, UK, Europe, and the Middle East.</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        Invoicy features an intelligent Place-of-Supply tax calculation service that automatically resolves statutory tax rates based on client location:
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                            <span class="font-bold text-slate-900 block">Ontario (ON): 13.00% HST</span>
                            <span class="text-slate-500 text-[11px]">Harmonized Sales Tax single rate.</span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                            <span class="font-bold text-slate-900 block">Quebec (QC): 14.975% Dual Tax</span>
                            <span class="text-slate-500 text-[11px]">5.00% Federal GST + 9.975% Provincial QST.</span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                            <span class="font-bold text-slate-900 block">British Columbia (BC): 12.00% Dual Tax</span>
                            <span class="text-slate-500 text-[11px]">5.00% Federal GST + 7.00% Provincial PST.</span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                            <span class="font-bold text-slate-900 block">Alberta & Territories: 5.00% GST</span>
                            <span class="text-slate-500 text-[11px]">Federal GST only; 0% provincial tax.</span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                            <span class="font-bold text-slate-900 block">United Kingdom: 20.00% VAT</span>
                            <span class="text-slate-500 text-[11px]">Standard HMRC Value Added Tax.</span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                            <span class="font-bold text-slate-900 block">Saudi Arabia: 15.00% ZATCA VAT</span>
                            <span class="text-slate-500 text-[11px]">ZATCA compliant electronic invoicing.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 12: Automated Reminders -->
            <div x-show="activeTab === 'reminders'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-bell"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">12. Automated Payment Reminders</h2>
                        <p class="text-xs text-slate-500">Prevent late payments with multi-stage automated email reminders.</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        The Reminders engine runs automated checks every hour to dispatch courteous reminders according to three standard milestones:
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-[11px]">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="font-bold text-slate-900 block">1. Due Soon</span>
                            <span class="text-slate-500">Sent 3 days prior to invoice due date.</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="font-bold text-slate-900 block">2. Due Today</span>
                            <span class="text-slate-500">Sent on the exact day payment is due.</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="font-bold text-slate-900 block">3. Overdue</span>
                            <span class="text-slate-500">Sent after grace period with late notice.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 13: SMTP Configuration -->
            <div x-show="activeTab === 'smtp'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-paper-plane"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">13. Custom Outgoing SMTP & Live Testing</h2>
                        <p class="text-xs text-slate-500">Send invoices from your own authenticated agency domain with 100% deliverability.</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        Subscribed agencies can configure their own authenticated Outgoing SMTP mail server in <strong>Agency Settings</strong>:
                    </p>
                    <ul class="list-disc list-inside space-y-1.5 pl-2">
                        <li><strong>Host:</strong> Your mail server (e.g. <code>mail.youragency.com</code>, <code>smtp.gmail.com</code>).</li>
                        <li><strong>Port & Encryption:</strong> Port <code>465</code> with SSL or Port <code>587</code> with TLS.</li>
                        <li><strong>Authentication:</strong> Full encrypted storage of SMTP credentials.</li>
                        <li><strong>Live Test Delivery:</strong> Sends an official branded test email confirming operational status.</li>
                    </ul>
                </div>
            </div>

            <!-- Section 14: Plan & Billing -->
            <div x-show="activeTab === 'billing'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-credit-card"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">14. Plan, Billing & Promotional Coupons</h2>
                        <p class="text-xs text-slate-500">Manage agency subscription tiers, promo codes, and upgrade requests.</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        Manage your agency plan inside <strong>Plan & Billing</strong>. You can submit upgrade or downgrade requests with real-time status banners:
                    </p>

                    <!-- Real Screenshot Showcase -->
                    <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
                        <img src="{{ asset('images/docs/subscriptions-billing.png') }}" alt="Subscriptions & Billing Management Screenshot" class="w-full object-cover">
                        <div class="p-3 bg-slate-50 text-[11px] text-slate-500 border-t border-slate-200 flex items-center gap-2">
                            <i class="ph ph-image text-slate-400"></i>
                            <span>Figure 14.1: Subscription management screen showing plan features, upgrade requests, and promo coupon entry.</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-purple-50 border border-purple-200 text-purple-950 text-xs">
                        <strong>Promotional Codes:</strong> Enter codes such as <code>FREE100</code> during checkout or registration to instantly unlock full service or discount perks.
                    </div>
                </div>
            </div>

            <!-- Section 15: AI Agent -->
            <div x-show="activeTab === 'ai'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-sparkle"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">15. AI Agent (Text & Voice Commands)</h2>
                        <p class="text-xs text-slate-500">Your intelligent financial assistant ready 24/7.</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        Invoicy includes an enterprise AI Agent with both text and hands-free voice commands.
                    </p>
                    <div class="p-4 rounded-2xl bg-slate-900 text-white space-y-2">
                        <div class="text-[11px] font-bold text-red-400 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="ph ph-microphone"></i> Try Saying These Voice Commands:
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                            <div class="p-2.5 rounded-xl bg-slate-800 border border-slate-700">"Create a new invoice"</div>
                            <div class="p-2.5 rounded-xl bg-slate-800 border border-slate-700">"Go to settings"</div>
                            <div class="p-2.5 rounded-xl bg-slate-800 border border-slate-700">"How do I recall an invoice?"</div>
                            <div class="p-2.5 rounded-xl bg-slate-800 border border-slate-700">"What is authentic QR verification?"</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 16: Support Desk (24h SLA) -->
            <div x-show="activeTab === 'support'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-lifebuoy"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">16. Support Desk & 24h SLA Guarantee</h2>
                        <p class="text-xs text-slate-500">Direct engineering assistance with guaranteed 24-hour turnaround and zero data loss.</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        Need assistance with a complex tax configuration, payment gateway setup, or custom invoicing feature? Our dedicated engineering support team operates directly within the application.
                    </p>

                    <!-- Real Screenshot Showcase -->
                    <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
                        <img src="{{ asset('images/docs/support-desk.png') }}" alt="Support Desk Screenshot" class="w-full object-cover">
                        <div class="p-3 bg-slate-50 text-[11px] text-slate-500 border-t border-slate-200 flex items-center gap-2">
                            <i class="ph ph-image text-slate-400"></i>
                            <span>Figure 16.1: Agency Support Desk showing active tickets, response threads, and 24-hour turnaround SLA tracking.</span>
                        </div>
                    </div>

                    <h4 class="font-bold text-slate-900 text-sm mt-4">Our Service Level Commitments:</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-950">
                            <div class="font-bold text-xs flex items-center gap-1.5 mb-1">
                                <i class="ph ph-clock-countdown text-emerald-600 text-base"></i> 24-Hour Turnaround SLA
                            </div>
                            <p class="text-[11px] text-emerald-800">Every support ticket is logged, triaged, and answered by senior engineers within 24 business hours.</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-950">
                            <div class="font-bold text-xs flex items-center gap-1.5 mb-1">
                                <i class="ph ph-shield-check text-indigo-600 text-base"></i> Zero Data Loss Architecture
                            </div>
                            <p class="text-[11px] text-indigo-800">All updates, schema migrations, and bug resolutions preserve existing invoices, ledgers, and client records intact.</p>
                        </div>
                    </div>

                    <h4 class="font-bold text-slate-900 text-sm mt-4">How to Open a Support Ticket:</h4>
                    <ol class="list-decimal list-inside space-y-1.5 pl-2">
                        <li>Navigate to <strong>Support Desk</strong> in the main navigation menu.</li>
                        <li>Click <strong>"Create New Ticket"</strong>.</li>
                        <li>Select the category (Billing, Gateway, Invoicing, Tax Configuration, Feature Request).</li>
                        <li>Choose Priority (Low, Medium, High, Urgent).</li>
                        <li>Provide detailed context or screenshots, and click <strong>"Submit Ticket"</strong>.</li>
                    </ol>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
