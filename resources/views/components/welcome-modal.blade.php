@php
    $tourEnabled = (\App\Models\CmsContent::getVal('welcome_popup_enabled', \App\Models\CmsContent::getVal('enable_welcome_tour', '1'))) === '1';
    $tourSlides = \App\Models\CmsContent::getWelcomeTourSlides();
    $totalSteps = max(1, count($tourSlides));
@endphp

@if($tourEnabled && count($tourSlides) > 0)
<div x-data="{
        isOpen: false,
        step: 1,
        totalSteps: {{ $totalSteps }},
        slides: {{ json_encode($tourSlides) }},
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

                <!-- Dynamic Slide Content Loops -->
                <template x-for="(slide, index) in slides" :key="index">
                    <div x-show="step === (index + 1)" 
                         x-transition:enter="transition ease-out duration-200" 
                         x-transition:enter-start="opacity-0 translate-x-4" 
                         x-transition:enter-end="opacity-100 translate-x-0" 
                         class="py-6 space-y-4">
                        
                        <!-- Slide Icon Box -->
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl mx-auto shadow-inner"
                             :class="{
                                 'bg-amber-50 border border-amber-200 text-amber-600': (slide.color || 'amber') === 'amber',
                                 'bg-red-50 border border-red-200 text-red-600': slide.color === 'red',
                                 'bg-emerald-50 border border-emerald-200 text-emerald-600': slide.color === 'emerald',
                                 'bg-blue-50 border border-blue-200 text-blue-600': slide.color === 'blue',
                                 'bg-purple-50 border border-purple-200 text-purple-600': slide.color === 'purple',
                                 'bg-sky-50 border border-sky-200 text-sky-600': slide.color === 'sky'
                             }">
                            <i class="ph" :class="slide.icon || 'ph-sparkle'"></i>
                        </div>

                        <!-- Titles & Description -->
                        <div class="text-center space-y-2">
                            <h4 class="text-lg font-black text-slate-900" x-text="slide.title"></h4>
                            <p class="text-xs text-slate-600 max-w-md mx-auto leading-relaxed" x-text="slide.text"></p>
                        </div>

                        <!-- Badge Pill -->
                        <template x-if="slide.badge">
                            <div class="flex items-center justify-center pt-1">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    <i class="ph ph-check-circle text-emerald-600"></i>
                                    <span x-text="slide.badge"></span>
                                </span>
                            </div>
                        </template>
                    </div>
                </template>

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
