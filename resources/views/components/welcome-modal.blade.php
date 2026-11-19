@php
    $tourEnabled = \App\Models\CmsContent::getVal('welcome_popup_enabled', '1') === '1';
    $step1Title = \App\Models\CmsContent::getVal('welcome_step1_title', '1. Create High-Precision Invoices in Seconds');
    $step1Text = \App\Models\CmsContent::getVal('welcome_step1_text', 'Click "+ New Invoice" in your sidebar or ask the AI Copilot. Enter client details or use Walk-in mode for instantaneous cash and retail billing without client signup.');
    
    $step2Title = \App\Models\CmsContent::getVal('welcome_step2_title', '2. 1-Click Industry Service Presets & Custom Line Items');
    $step2Text = \App\Models\CmsContent::getVal('welcome_step2_text', 'Access 45+ tailored presets for your business category (Computer Shop, Transportation, Limousine Taxi, Industrial, Construction, Distributor, Marketing Agency). 1-click adds pre-priced line items with matching units.');
    
    $step3Title = \App\Models\CmsContent::getVal('welcome_step3_title', '3. Private Draft Invoices & Zero Accidental Dispatch');
    $step3Text = \App\Models\CmsContent::getVal('welcome_step3_text', 'Draft invoices are completely private. Emails are never dispatched, and PDF downloads, customer payment portals, and QR verification screens are disabled until you click "Issue & Publish".');
    
    $step4Title = \App\Models\CmsContent::getVal('welcome_step4_title', '4. Cryptographic QR Seals & Automated Reminders');
    $step4Text = \App\Models\CmsContent::getVal('welcome_step4_text', 'Published invoices feature authentic ISO 2D QR verification codes for fraud prevention and automated milestone payment reminder schedules (45, 30, 15, 7, 3, and 2 days before due date).');
@endphp

@if($tourEnabled)
<div x-data="{
        isOpen: false,
        step: 1,
        totalSteps: 4,
        dontShowAgain: true,
        init() {
            const seen = localStorage.getItem('invoicy_welcome_tour_completed');
            if (!seen) {
                setTimeout(() => { this.isOpen = true; }, 800);
            }
            window.addEventListener('open-welcome-tour', () => {
                this.step = 1;
                this.isOpen = true;
            });
        },
        nextStep() {
            if (this.step < this.totalSteps) {
                this.step++;
            } else {
                this.closeTour();
            }
        },
        prevStep() {
            if (this.step > 1) {
                this.step--;
            }
        },
        closeTour() {
            if (this.dontShowAgain) {
                localStorage.setItem('invoicy_welcome_tour_completed', 'true');
            }
            this.isOpen = false;
        }
     }"
     x-show="isOpen"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="modal-title" role="dialog" aria-modal="true">

    <!-- Backdrop -->
    <div x-show="isOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"
         @click="closeTour()"></div>

    <!-- Modal Box -->
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div x-show="isOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-slate-200">

            <!-- Top Header Accent -->
            <div class="h-2 bg-gradient-to-r from-red-600 via-rose-500 to-amber-500"></div>

            <div class="p-6 sm:p-8">
                <!-- Top Header Bar with Close Button -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-red-600/10 text-red-600 flex items-center justify-center font-bold text-sm">
                            <i class="ph ph-compass text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-1.5">
                                <span>Platform Guided Walkthrough</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 font-mono" x-text="'Step ' + step + ' of ' + totalSteps"></span>
                            </h3>
                            <p class="text-[11px] text-slate-400">Master invoice creation, presets, drafts & verification</p>
                        </div>
                    </div>
                    <button type="button" @click="closeTour()" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center transition">
                        <i class="ph ph-x text-base font-bold"></i>
                    </button>
                </div>

                <!-- Slide 1: Create Invoice & Walk-in -->
                <div x-show="step === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="py-6 space-y-4">
                    <div class="w-16 h-16 rounded-2xl bg-red-50 border border-red-200 text-red-600 flex items-center justify-center text-3xl mx-auto shadow-inner">
                        <i class="ph ph-receipt"></i>
                    </div>
                    <div class="text-center space-y-2">
                        <h4 class="text-lg font-black text-slate-900">{{ $step1Title }}</h4>
                        <p class="text-xs text-slate-600 max-w-md mx-auto leading-relaxed">{{ $step1Text }}</p>
                    </div>
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 text-xs space-y-2 text-slate-700">
                        <div class="flex items-center justify-between font-bold text-[11px] text-slate-500 uppercase tracking-wider">
                            <span>Key Highlights</span>
                            <span class="text-emerald-600">Zero Customer Friction</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-[11px]">
                            <div class="p-2 bg-white rounded-xl border border-slate-200 flex items-center gap-2">
                                <i class="ph ph-user-circle text-base text-red-600"></i>
                                <span>Registered Client CRM</span>
                            </div>
                            <div class="p-2 bg-white rounded-xl border border-slate-200 flex items-center gap-2">
                                <i class="ph ph-shopping-bag text-base text-emerald-600"></i>
                                <span>Walk-in / Cash Billing</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 2: Industry Presets -->
                <div x-show="step === 2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="py-6 space-y-4">
                    <div class="w-16 h-16 rounded-2xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center text-3xl mx-auto shadow-inner">
                        <i class="ph ph-lightning"></i>
                    </div>
                    <div class="text-center space-y-2">
                        <h4 class="text-lg font-black text-slate-900">{{ $step2Title }}</h4>
                        <p class="text-xs text-slate-600 max-w-md mx-auto leading-relaxed">{{ $step2Text }}</p>
                    </div>
                    <div class="p-3.5 bg-amber-50/60 rounded-2xl border border-amber-200/80 text-xs space-y-2">
                        <span class="block text-[10px] font-extrabold uppercase tracking-wider text-amber-800">Supported Industry Presets</span>
                        <div class="flex flex-wrap gap-1.5 text-[10px] font-bold">
                            <span class="px-2 py-1 rounded-lg bg-white border border-amber-200 text-slate-700">💻 Computer Shop</span>
                            <span class="px-2 py-1 rounded-lg bg-white border border-amber-200 text-slate-700">🚚 Transportation</span>
                            <span class="px-2 py-1 rounded-lg bg-white border border-amber-200 text-slate-700">🚖 Limo & Taxi</span>
                            <span class="px-2 py-1 rounded-lg bg-white border border-amber-200 text-slate-700">⚙️ Industrial</span>
                            <span class="px-2 py-1 rounded-lg bg-white border border-amber-200 text-slate-700">🏗️ Construction</span>
                            <span class="px-2 py-1 rounded-lg bg-white border border-amber-200 text-slate-700">📦 Distributor</span>
                            <span class="px-2 py-1 rounded-lg bg-white border border-amber-200 text-slate-700">🎨 Marketing</span>
                        </div>
                    </div>
                </div>

                <!-- Slide 3: Draft Safeguard -->
                <div x-show="step === 3" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="py-6 space-y-4">
                    <div class="w-16 h-16 rounded-2xl bg-sky-50 border border-sky-200 text-sky-600 flex items-center justify-center text-3xl mx-auto shadow-inner">
                        <i class="ph ph-shield-check"></i>
                    </div>
                    <div class="text-center space-y-2">
                        <h4 class="text-lg font-black text-slate-900">{{ $step3Title }}</h4>
                        <p class="text-xs text-slate-600 max-w-md mx-auto leading-relaxed">{{ $step3Text }}</p>
                    </div>
                    <div class="p-3.5 bg-sky-50/60 rounded-2xl border border-sky-200 text-xs space-y-2">
                        <div class="grid grid-cols-2 gap-2 text-[11px]">
                            <div class="p-2.5 bg-white rounded-xl border border-slate-200">
                                <span class="font-bold text-amber-700 flex items-center gap-1 mb-1">
                                    <i class="ph ph-lock text-sm"></i> Draft State
                                </span>
                                <span class="text-slate-500 text-[10px] block">Only "Revise" & "Publish" active. Zero external access.</span>
                            </div>
                            <div class="p-2.5 bg-white rounded-xl border border-slate-200">
                                <span class="font-bold text-emerald-700 flex items-center gap-1 mb-1">
                                    <i class="ph ph-check-circle text-sm"></i> Published State
                                </span>
                                <span class="text-slate-500 text-[10px] block">PDF, QR seal, customer portal & reminders active.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 4: Verification & Automated Reminders -->
                <div x-show="step === 4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="py-6 space-y-4">
                    <div class="w-16 h-16 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center text-3xl mx-auto shadow-inner">
                        <i class="ph ph-bell-ringing"></i>
                    </div>
                    <div class="text-center space-y-2">
                        <h4 class="text-lg font-black text-slate-900">{{ $step4Title }}</h4>
                        <p class="text-xs text-slate-600 max-w-md mx-auto leading-relaxed">{{ $step4Text }}</p>
                    </div>
                    <div class="p-3.5 bg-emerald-50/60 rounded-2xl border border-emerald-200 text-xs space-y-2">
                        <span class="block text-[10px] font-extrabold uppercase tracking-wider text-emerald-800">Automated Reminder Schedule</span>
                        <div class="flex items-center justify-between text-[10px] font-bold text-slate-700">
                            <span class="px-2 py-0.5 rounded bg-white border border-emerald-200">45d</span>
                            <span class="px-2 py-0.5 rounded bg-white border border-emerald-200">30d</span>
                            <span class="px-2 py-0.5 rounded bg-white border border-emerald-200">15d</span>
                            <span class="px-2 py-0.5 rounded bg-white border border-emerald-200">7d</span>
                            <span class="px-2 py-0.5 rounded bg-white border border-emerald-200">3d</span>
                            <span class="px-2 py-0.5 rounded bg-red-600 text-white font-black">2d Final</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Navigation -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <!-- Step Indicators -->
                    <div class="flex items-center gap-1.5">
                        <template x-for="i in totalSteps" :key="i">
                            <button type="button" @click="step = i"
                                    :class="step === i ? 'w-6 bg-red-600' : 'w-2 bg-slate-200 hover:bg-slate-300'"
                                    class="h-2 rounded-full transition-all duration-200"></button>
                        </template>
                    </div>

                    <!-- Next / Previous Controls -->
                    <div class="flex items-center gap-2">
                        <button type="button" x-show="step > 1" @click="prevStep()"
                                class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                            Back
                        </button>
                        <button type="button" @click="nextStep()"
                                class="px-5 py-2.5 rounded-xl text-xs font-bold bg-red-600 hover:bg-red-700 text-white shadow-md shadow-red-500/20 transition flex items-center gap-1.5 group">
                            <span x-text="step === totalSteps ? 'Get Started & Create Invoice' : 'Next Step'"></span>
                            <i class="ph ph-arrow-right text-xs group-hover:translate-x-0.5 transition-transform"></i>
                        </button>
                    </div>
                </div>

                <!-- Dismiss Option -->
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                    <label class="flex items-center gap-1.5 cursor-pointer hover:text-slate-600">
                        <input type="checkbox" x-model="dontShowAgain" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                        <span>Don't show this walkthrough automatically again</span>
                    </label>
                    <button type="button" @click="closeTour()" class="hover:text-slate-600 font-semibold underline">
                        Skip Tour
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
