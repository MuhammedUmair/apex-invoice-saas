@extends('layouts.admin', ['title' => 'Welcome Walkthrough Tour', 'header' => 'System Configuration'])

@section('content')
<div class="space-y-6" x-data="{
    slides: {{ json_encode($tourSlides) }},
    activePreviewSlide: 0,
    popupEnabled: '{{ $tourEnabled ? '1' : '0' }}',
    addSlide() {
        this.slides.push({
            title: (this.slides.length + 1) + '. New Walkthrough Feature',
            text: 'Explain this key feature to new tenants so they can achieve rapid value on their workspace.',
            badge: 'Key Feature',
            icon: 'ph-sparkle',
            color: 'amber'
        });
        this.activePreviewSlide = this.slides.length - 1;
    },
    removeSlide(index) {
        if (this.slides.length <= 1) {
            alert('At least one onboarding slide is required.');
            return;
        }
        if (confirm('Remove slide #' + (index + 1) + '?')) {
            this.slides.splice(index, 1);
            if (this.activePreviewSlide >= this.slides.length) {
                this.activePreviewSlide = Math.max(0, this.slides.length - 1);
            }
        }
    },
    moveUp(index) {
        if (index > 0) {
            const temp = this.slides[index];
            this.slides[index] = this.slides[index - 1];
            this.slides[index - 1] = temp;
            this.activePreviewSlide = index - 1;
        }
    },
    moveDown(index) {
        if (index < this.slides.length - 1) {
            const temp = this.slides[index];
            this.slides[index] = this.slides[index + 1];
            this.slides[index + 1] = temp;
            this.activePreviewSlide = index + 1;
        }
    }
}">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-800 gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20">
                    <i class="ph ph-compass text-xl"></i>
                </span>
                <h1 class="text-xl font-black text-white">Welcome Walkthrough & Onboarding Tour</h1>
            </div>
            <p class="text-xs text-slate-400 mt-0.5">Customize the step-by-step onboarding guide shown to tenants upon initial sign-in. Add or remove slides dynamically.</p>
        </div>

        <button type="button" @click="addSlide()" 
                class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl text-xs shadow-sm transition">
            <i class="ph ph-plus-circle text-base"></i> Add New Tour Section
        </button>
    </div>

    <!-- Subnav Tabs for Configuration -->
    <div class="flex items-center gap-2 border-b border-slate-800 pb-2 text-xs overflow-x-auto">
        <a href="{{ route('admin.config.general') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">General</a>
        <a href="{{ route('admin.config.tour') }}" class="px-3.5 py-1.5 rounded-xl font-bold bg-amber-500 text-slate-950 flex items-center gap-1.5">
            <i class="ph ph-compass"></i> Welcome Tour
        </a>
        <a href="{{ route('admin.config.gateways') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">Gateways</a>
        <a href="{{ route('admin.config.smtp') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">SMTP Server</a>
        <a href="{{ route('admin.config.storage') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">Storage</a>
        <a href="{{ route('admin.config.taxes') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">Currency & Taxes</a>
        <a href="{{ route('admin.config.branding') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">Platform Branding</a>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-950/40 border border-emerald-500/30 text-emerald-300 text-xs font-bold flex items-center gap-2">
            <i class="ph ph-check-circle text-base text-emerald-400"></i>
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.config.tour.update') }}" class="space-y-6">
        @csrf

        <!-- Master Switch & Overview Card -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center justify-center text-xl shrink-0">
                    <i class="ph ph-toggle-left"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white">Tenant Welcome Popup Status</h3>
                    <p class="text-xs text-slate-400">When enabled, new users see this walkthrough modal automatically upon login.</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <select name="welcome_popup_enabled" x-model="popupEnabled" 
                        class="px-4 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs font-bold text-amber-400 focus:ring-2 focus:ring-amber-500 outline-none">
                    <option value="1">Enabled (Visible to Tenants)</option>
                    <option value="0">Disabled (Turned Off)</option>
                </select>

                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl text-xs shadow-sm transition">
                    <i class="ph ph-floppy-disk text-base"></i> Save Changes
                </button>
            </div>
        </div>

        <!-- Main Layout: Left Editor, Right Live Simulation -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left: Slides List Editor (7 Columns) -->
            <div class="lg:col-span-7 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                        <i class="ph ph-stack text-sm text-amber-400"></i>
                        <span>Configured Tour Slides (<span x-text="slides.length"></span> Total)</span>
                    </h3>
                    <span class="text-[11px] text-slate-500">Reorder with arrows or delete anytime</span>
                </div>

                <!-- Dynamic Slide Cards -->
                <template x-for="(slide, index) in slides" :key="index">
                    <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-4 transition"
                         :class="activePreviewSlide === index ? 'ring-2 ring-amber-500/40 border-amber-500/40' : ''">
                        
                        <!-- Slide Card Top Header -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-amber-500 text-slate-950 font-black text-xs flex items-center justify-center" x-text="index + 1"></span>
                                <span class="text-xs font-bold text-white">Slide #<span x-text="index + 1"></span></span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-300 border border-slate-700" x-text="slide.badge || 'Feature'"></span>
                            </div>

                            <div class="flex items-center gap-1">
                                <!-- Preview Switch -->
                                <button type="button" @click="activePreviewSlide = index" 
                                        :class="activePreviewSlide === index ? 'bg-amber-500/20 text-amber-300 border-amber-500/30' : 'bg-slate-800 text-slate-400 border-slate-700'"
                                        class="px-2.5 py-1 rounded-lg text-[11px] font-bold border transition flex items-center gap-1">
                                    <i class="ph ph-eye text-xs"></i>
                                    <span>Preview</span>
                                </button>

                                <!-- Move Up -->
                                <button type="button" @click="moveUp(index)" :disabled="index === 0"
                                        class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 disabled:opacity-30 disabled:cursor-not-allowed border border-slate-700 transition" title="Move Up">
                                    <i class="ph ph-arrow-up text-xs"></i>
                                </button>

                                <!-- Move Down -->
                                <button type="button" @click="moveDown(index)" :disabled="index === slides.length - 1"
                                        class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 disabled:opacity-30 disabled:cursor-not-allowed border border-slate-700 transition" title="Move Down">
                                    <i class="ph ph-arrow-down text-xs"></i>
                                </button>

                                <!-- Delete Slide -->
                                <button type="button" @click="removeSlide(index)" 
                                        class="p-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 transition" title="Remove Slide">
                                    <i class="ph ph-trash text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Title Input -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Slide Title *</label>
                            <input type="text" :name="'slides[' + index + '][title]'" x-model="slide.title" required
                                   placeholder="e.g. 1. Create Invoices in Seconds"
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-700 bg-slate-950 text-white text-xs font-bold focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>

                        <!-- Guidance Text -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Guidance Text / Instructions *</label>
                            <textarea :name="'slides[' + index + '][text]'" x-model="slide.text" rows="2" required
                                      placeholder="Clear, actionable explanation of how this feature works for tenants..."
                                      class="w-full px-3.5 py-2 rounded-xl border border-slate-700 bg-slate-950 text-white text-xs leading-relaxed focus:ring-2 focus:ring-amber-500 outline-none"></textarea>
                        </div>

                        <!-- 3-Column Attributes -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Highlight Badge Tag</label>
                                <input type="text" :name="'slides[' + index + '][badge]'" x-model="slide.badge" placeholder="e.g. Zero Friction"
                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-700 bg-slate-950 text-white text-xs focus:ring-2 focus:ring-amber-500 outline-none">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Phosphor Icon</label>
                                <select :name="'slides[' + index + '][icon]'" x-model="slide.icon" 
                                        class="w-full px-3 py-1.5 rounded-lg border border-slate-700 bg-slate-950 text-white text-xs focus:ring-2 focus:ring-amber-500 outline-none">
                                    <option value="ph-receipt">ph-receipt (Invoicing)</option>
                                    <option value="ph-lightning">ph-lightning (Speed / Presets)</option>
                                    <option value="ph-shield-check">ph-shield-check (Security / Draft)</option>
                                    <option value="ph-bell-ringing">ph-bell-ringing (Reminders)</option>
                                    <option value="ph-sparkle">ph-sparkle (AI / Magic)</option>
                                    <option value="ph-compass">ph-compass (Navigation)</option>
                                    <option value="ph-currency-dollar">ph-currency-dollar (Finance)</option>
                                    <option value="ph-rocket">ph-rocket (Launch)</option>
                                    <option value="ph-users">ph-users (Clients / Team)</option>
                                    <option value="ph-chart-line">ph-chart-line (Analytics)</option>
                                    <option value="ph-buildings">ph-buildings (Agencies)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Color Theme</label>
                                <select :name="'slides[' + index + '][color]'" x-model="slide.color" 
                                        class="w-full px-3 py-1.5 rounded-lg border border-slate-700 bg-slate-950 text-white text-xs focus:ring-2 focus:ring-amber-500 outline-none">
                                    <option value="amber">Warm Amber</option>
                                    <option value="red">Ruby Red</option>
                                    <option value="emerald">Emerald Forest</option>
                                    <option value="blue">Ocean Blue</option>
                                    <option value="purple">Royal Purple</option>
                                    <option value="sky">Sky Cyan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Add Button Bottom -->
                <button type="button" @click="addSlide()" 
                        class="w-full py-3 rounded-2xl border border-dashed border-slate-700 hover:border-amber-500/60 bg-slate-900/60 hover:bg-slate-900 text-slate-300 hover:text-amber-400 text-xs font-bold transition flex items-center justify-center gap-2">
                    <i class="ph ph-plus-circle text-base"></i>
                    <span>Add Another Tour Slide / Step</span>
                </button>
            </div>

            <!-- Right: Interactive Live Simulation (5 Columns Sticky) -->
            <div class="lg:col-span-5 sticky top-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                        <i class="ph ph-device-mobile text-sm text-amber-400"></i>
                        <span>Live Tenant Preview Simulation</span>
                    </h3>
                    <span class="text-[10px] font-mono text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded">
                        Interactive
                    </span>
                </div>

                <!-- Simulation Modal Container -->
                <div class="rounded-3xl bg-slate-950 border border-slate-800 shadow-2xl overflow-hidden text-left relative">
                    <!-- Top Gradient Accent -->
                    <div class="h-2 bg-gradient-to-r from-red-600 via-rose-500 to-amber-500"></div>

                    <div class="p-5 sm:p-6 space-y-5">
                        <!-- Simulation Top Header -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center justify-center font-bold text-sm">
                                    <i class="ph ph-compass text-base"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-black text-white tracking-tight flex items-center gap-1.5">
                                        <span>Platform Guided Walkthrough</span>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-slate-800 text-amber-400 font-mono">
                                            Step <span x-text="activePreviewSlide + 1"></span> of <span x-text="slides.length"></span>
                                        </span>
                                    </h4>
                                    <p class="text-[10px] text-slate-500">Tenant onboarding interactive popup</p>
                                </div>
                            </div>

                            <span class="w-7 h-7 rounded-lg bg-slate-800 text-slate-400 flex items-center justify-center text-xs">
                                <i class="ph ph-x"></i>
                            </span>
                        </div>

                        <!-- Active Slide Preview Box -->
                        <div class="py-4 space-y-4 text-center">
                            <!-- Icon Box -->
                            <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl mx-auto shadow-inner"
                                 :class="{
                                     'bg-amber-500/10 text-amber-400 border border-amber-500/20': (slides[activePreviewSlide]?.color || 'amber') === 'amber',
                                     'bg-red-500/10 text-red-400 border border-red-500/20': slides[activePreviewSlide]?.color === 'red',
                                     'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20': slides[activePreviewSlide]?.color === 'emerald',
                                     'bg-blue-500/10 text-blue-400 border border-blue-500/20': slides[activePreviewSlide]?.color === 'blue',
                                     'bg-purple-500/10 text-purple-400 border border-purple-500/20': slides[activePreviewSlide]?.color === 'purple',
                                     'bg-sky-500/10 text-sky-400 border border-sky-500/20': slides[activePreviewSlide]?.color === 'sky'
                                 }">
                                <i class="ph" :class="slides[activePreviewSlide]?.icon || 'ph-sparkle'"></i>
                            </div>

                            <div class="space-y-1.5">
                                <h5 class="text-base font-black text-white" x-text="slides[activePreviewSlide]?.title || 'Slide Title'"></h5>
                                <p class="text-xs text-slate-300 leading-relaxed max-w-sm mx-auto" x-text="slides[activePreviewSlide]?.text || 'Slide guidance text...'"></p>
                            </div>

                            <!-- Highlight Pill -->
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-slate-900 border border-slate-800 text-slate-300">
                                <i class="ph ph-check-circle text-emerald-400"></i>
                                <span x-text="slides[activePreviewSlide]?.badge || 'Key Capability'"></span>
                            </div>
                        </div>

                        <!-- Simulation Footer -->
                        <div class="pt-3 border-t border-slate-800 flex items-center justify-between">
                            <!-- Indicators -->
                            <div class="flex items-center gap-1.5">
                                <template x-for="(s, i) in slides" :key="i">
                                    <button type="button" @click="activePreviewSlide = i"
                                            :class="activePreviewSlide === i ? 'w-5 bg-amber-500' : 'w-1.5 bg-slate-700 hover:bg-slate-600'"
                                            class="h-1.5 rounded-full transition-all duration-200"></button>
                                </template>
                            </div>

                            <!-- Next / Back Controls -->
                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="activePreviewSlide = Math.max(0, activePreviewSlide - 1)" 
                                        :disabled="activePreviewSlide === 0"
                                        class="px-2.5 py-1 rounded-lg text-xs font-semibold text-slate-400 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed">
                                    Back
                                </button>
                                <button type="button" @click="activePreviewSlide = Math.min(slides.length - 1, activePreviewSlide + 1)"
                                        class="px-3.5 py-1.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-lg text-xs shadow transition flex items-center gap-1">
                                    <span x-text="activePreviewSlide === slides.length - 1 ? 'Get Started' : 'Next'"></span>
                                    <i class="ph ph-arrow-right text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-xs text-slate-400 space-y-2 shadow-sm">
                    <div class="font-bold text-white flex items-center gap-1.5">
                        <i class="ph ph-lightbulb text-amber-400"></i>
                        <span>Dynamic Architecture Notice</span>
                    </div>
                    <p class="leading-relaxed text-[11px]">
                        Slides configured here are stored as structured JSON and dynamically loop into the tenant modal. You can add as many steps as you need without code deployment.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4 border-t border-slate-800">
            <button type="submit" class="inline-flex items-center gap-2 px-8 py-3 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl text-sm shadow transition">
                <i class="ph ph-floppy-disk text-base"></i> Save All Tour Slides & Preferences
            </button>
        </div>
    </form>
</div>
@endsection
