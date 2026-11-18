@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-8" x-data="{
    activeTab: '{{ $activeTab }}',
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
                <i class="ph ph-book-open text-sm"></i> Help Center & System Manual
            </div>
            <h1 class="text-3xl sm:text-4xl font-black tracking-tight mb-2">Invoicy Knowledge Base & Guide</h1>
            <p class="text-slate-300 text-sm leading-relaxed mb-6">
                Master all 10 core modules of Invoicy by RedMaple. Explore detailed feature breakdowns, interactive visual walk-throughs, and best practices for commercial billing.
            </p>

            <!-- Search Filter Bar -->
            <div class="relative">
                <i class="ph ph-magnifying-glass text-slate-400 absolute left-4 top-1/2 -translate-y-1/2 text-lg"></i>
                <input type="text" x-model="searchQuery" placeholder="Search guides, modules, or features (e.g., 'QR code', 'Recall', 'SMTP', 'Reminders')..."
                       class="w-full pl-11 pr-4 py-3 bg-white/10 border border-white/20 backdrop-blur-md rounded-2xl text-white placeholder-slate-400 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-red-500 transition">
            </div>
        </div>
    </div>

    <!-- Navigation Tabs & Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left Sidebar Navigation -->
        <div class="lg:col-span-4 bg-white rounded-3xl border border-slate-200 p-4 shadow-xs sticky top-6 space-y-1">
            <div class="px-3 py-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                Platform Sections
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
                    <span>2. Invoices & Pre-Dispatch Review</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'verification'" :class="activeTab === 'verification' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-qr-code text-base text-red-600"></i>
                    <span>3. QR Code & Authentic Verification</span>
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
                    <span>5. Live View Alerts & Engagement</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'retainers'" :class="activeTab === 'retainers' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-arrows-clockwise text-base text-emerald-600"></i>
                    <span>6. Recurring Retainers</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'reminders'" :class="activeTab === 'reminders' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-bell text-base text-amber-600"></i>
                    <span>7. Automated Reminders</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'smtp'" :class="activeTab === 'smtp' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-paper-plane text-base text-blue-600"></i>
                    <span>8. Custom SMTP & Live Testing</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'billing'" :class="activeTab === 'billing' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-credit-card text-base text-purple-600"></i>
                    <span>9. Plan, Billing & Coupons</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <button @click="activeTab = 'ai'" :class="activeTab === 'ai' ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-semibold'"
                    class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="ph ph-sparkle text-base text-red-600"></i>
                    <span>10. AI Agent (Voice & Text)</span>
                </span>
                <i class="ph ph-caret-right text-xs opacity-50"></i>
            </button>

            <!-- Quick Action Box -->
            <div class="pt-4 border-t border-slate-100 px-2">
                <div class="p-3 bg-red-50/70 rounded-2xl border border-red-100 text-xs">
                    <div class="font-bold text-red-900 flex items-center gap-1.5 mb-1">
                        <i class="ph ph-microphone text-sm text-red-600"></i> Need instant help?
                    </div>
                    <p class="text-slate-600 text-[11px] mb-2">Click the AI Agent icon at the bottom right to ask any question via voice or text!</p>
                    <button @click="$dispatch('open-ai-copilot')" type="button" class="w-full py-1.5 bg-red-600 text-white font-bold rounded-lg text-[11px] hover:bg-red-700 transition">
                        Launch AI Agent
                    </button>
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
                        <h2 class="text-xl font-black text-slate-900">Dashboard & Workspace Overview</h2>
                        <p class="text-xs text-slate-500">Your commercial central command for billing intelligence and cash flow.</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        The Invoicy Dashboard provides real-time financial tracking, overdue monitoring, and quick action shortcuts designed to give agency operators complete clarity over accounts receivable.
                    </p>

                    <!-- Interactive Animated Metric Preview -->
                    <div class="p-5 rounded-2xl bg-slate-900 text-white space-y-3">
                        <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Live Metrics Simulation</div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700">
                                <div class="text-slate-400 text-[10px]">Total Revenue</div>
                                <div class="text-base font-black text-emerald-400">$128,450</div>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700">
                                <div class="text-slate-400 text-[10px]">Outstanding Due</div>
                                <div class="text-base font-black text-amber-400">$14,200</div>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700">
                                <div class="text-slate-400 text-[10px]">Overdue Invoices</div>
                                <div class="text-base font-black text-rose-400">2 Invoices</div>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700">
                                <div class="text-slate-400 text-[10px]">Active Retainers</div>
                                <div class="text-base font-black text-blue-400">18 Monthly</div>
                            </div>
                        </div>
                    </div>

                    <h4 class="font-bold text-slate-900 text-sm mt-4">Key Dashboard Capabilities:</h4>
                    <ul class="list-disc list-inside space-y-1.5 pl-2">
                        <li><strong>Cash Flow Analytics:</strong> Visual revenue trends and payment velocity.</li>
                        <li><strong>Live Activity Feed:</strong> Real-time alerts when clients view, open, or pay invoices.</li>
                        <li><strong>Milestone Reminders:</strong> Direct notification of invoices reaching due date or grace period.</li>
                    </ul>
                </div>
            </div>

            <!-- Section 2: Invoices & Pre-Dispatch Review -->
            <div x-show="activeTab === 'invoices'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-receipt"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">Invoices Studio & Pre-Dispatch Review</h2>
                        <p class="text-xs text-slate-500">Fast, compliant commercial invoice creation with automatic safeguards.</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <h4 class="font-bold text-slate-900 text-sm">How to Create and Issue an Invoice:</h4>
                    <ol class="list-decimal list-inside space-y-2 pl-2">
                        <li><strong>Select Customer:</strong> Choose an existing client or check <em>Walk-in Customer</em> for instant creation.</li>
                        <li><strong>Configure Line Items:</strong> Add products, services, rates, and units. Taxes are automatically calculated according to the selected country.</li>
                        <li><strong>Apply Promotional Code:</strong> Enter any agency discount or coupon code.</li>
                        <li><strong>Click "Issue & Publish Invoice":</strong> Invoicy displays the <strong>Pre-Dispatch Review Screen</strong>.</li>
                        <li><strong>Confirm Terms & Email Dispatch:</strong> Review client recipient, dates, amounts, and toggle direct email notification before confirming.</li>
                    </ol>

                    <!-- Interactive Animated Step Mockup -->
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

            <!-- Section 3: QR Code & Authentic Verification -->
            <div x-show="activeTab === 'verification'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-qr-code"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">QR Code & Authentic Verification</h2>
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
                        <h2 class="text-xl font-black text-slate-900">Invoice Recall & Pull Back Option</h2>
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

            <!-- Section 5: Live View Alerts & Tracking -->
            <div x-show="activeTab === 'tracking'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-eye"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">Live Client View & Open Notifications</h2>
                        <p class="text-xs text-slate-500">Know the exact minute your client opens their invoice.</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        Never wonder whether a client received your bill. Invoicy includes high-precision dual-channel engagement tracking:
                    </p>
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

            <!-- Section 6: Recurring Retainers -->
            <div x-show="activeTab === 'retainers'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-arrows-clockwise"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">Recurring Retainers & Subscriptions</h2>
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

            <!-- Section 7: Automated Reminders -->
            <div x-show="activeTab === 'reminders'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-bell"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">Automated Payment Reminders</h2>
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
                            <span class="text-slate-500">Sent after grace period with late surcharge notice.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 8: SMTP Configuration -->
            <div x-show="activeTab === 'smtp'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-paper-plane"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">Custom Outgoing SMTP & Live Testing</h2>
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

            <!-- Section 9: Plan & Billing -->
            <div x-show="activeTab === 'billing'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-credit-card"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">Plan, Billing & Promotional Coupons</h2>
                        <p class="text-xs text-slate-500">Manage agency subscription tiers, promo codes, and upgrade requests.</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                    <p>
                        Manage your agency plan inside <strong>Plan & Billing</strong>. You can submit upgrade or downgrade requests with real-time status banners:
                    </p>
                    <div class="p-4 rounded-2xl bg-purple-50 border border-purple-200 text-purple-950 text-xs">
                        <strong>Promotional Codes:</strong> Enter codes such as <code>FREE100</code> during checkout or registration to instantly unlock full service or discount perks.
                    </div>
                </div>
            </div>

            <!-- Section 10: AI Agent -->
            <div x-show="activeTab === 'ai'" x-cloak class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center text-xl font-bold">
                        <i class="ph ph-sparkle"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">AI Agent (Text & Voice Commands)</h2>
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

        </div>

    </div>
</div>
@endsection
