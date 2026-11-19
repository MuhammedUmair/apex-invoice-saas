@extends('layouts.admin', ['title' => 'General Configuration', 'header' => 'System Configuration'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white">General Platform Settings</h1>
            <p class="text-xs text-slate-400 mt-0.5">Global defaults for currency, support email, and branding.</p>
        </div>
    </div>

    <!-- Subnav Tabs for Configuration (Section 3) -->
    <div class="flex items-center gap-2 border-b border-slate-800 pb-2 text-xs">
        <a href="{{ route('admin.config.general') }}" class="px-3.5 py-1.5 rounded-xl font-bold bg-amber-500 text-slate-950">General</a>
        <a href="{{ route('admin.config.gateways') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">Gateways</a>
        <a href="{{ route('admin.config.smtp') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">SMTP Server</a>
        <a href="{{ route('admin.config.storage') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">Storage</a>
        <a href="{{ route('admin.config.taxes') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">Currency & Taxes</a>
        <a href="{{ route('admin.config.branding') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">Platform Branding</a>
    </div>

    <form action="{{ route('admin.config.update') }}" method="POST" class="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-4 max-w-2xl shadow-sm">
        @csrf
        <input type="hidden" name="group" value="general">
        
        <div>
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Platform Application Name</label>
            <input type="text" name="settings[app_name]" value="{{ $settings['app_name'] }}"
                   class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-200 focus:ring-2 focus:ring-amber-500 outline-none">
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Official SaaS URL</label>
            <input type="url" name="settings[app_url]" value="{{ $settings['app_url'] }}"
                   class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-200 focus:ring-2 focus:ring-amber-500 outline-none">
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Platform Support Email</label>
            <input type="email" name="settings[support_email]" value="{{ $settings['support_email'] }}"
                   class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-200 focus:ring-2 focus:ring-amber-500 outline-none">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Default Platform Currency</label>
                <input type="text" name="settings[default_currency]" value="{{ $settings['default_currency'] }}"
                       class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-200 focus:ring-2 focus:ring-amber-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Default Locale</label>
                <input type="text" name="settings[default_language]" value="{{ $settings['default_language'] }}"
                       class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-200 focus:ring-2 focus:ring-amber-500 outline-none">
            </div>
        </div>

        <!-- Welcome Walkthrough & Onboarding Popup Settings (Request 2) -->
        <div class="pt-6 mt-6 border-t border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center gap-1.5">
                        <i class="ph ph-compass text-amber-400 text-base"></i>
                        <span>Welcome Walkthrough & Onboarding Tour Popup</span>
                    </h3>
                    <p class="text-[11px] text-slate-400">Controls the multi-slide guide shown to tenants upon sign-in.</p>
                </div>
                <div>
                    <select name="settings[welcome_popup_enabled]" class="px-3 py-1.5 bg-slate-950 border border-slate-700 rounded-xl text-xs font-bold text-amber-400 focus:ring-2 focus:ring-amber-500 outline-none">
                        <option value="1" {{ ($settings['welcome_popup_enabled'] ?? '1') == '1' ? 'selected' : '' }}>Enabled (Turned ON)</option>
                        <option value="0" {{ ($settings['welcome_popup_enabled'] ?? '1') == '0' ? 'selected' : '' }}>Disabled (Turned OFF)</option>
                    </select>
                </div>
            </div>

            <!-- Slide 1 -->
            <div class="p-3.5 bg-slate-950/60 rounded-xl border border-slate-800 space-y-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-400">Slide 1: Invoicing & Walk-in Mode</span>
                <input type="text" name="settings[welcome_step1_title]" value="{{ $settings['welcome_step1_title'] }}" placeholder="Step 1 Title"
                       class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs text-slate-200">
                <textarea name="settings[welcome_step1_text]" rows="2" placeholder="Step 1 Guidance Text"
                          class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs text-slate-300">{{ $settings['welcome_step1_text'] }}</textarea>
            </div>

            <!-- Slide 2 -->
            <div class="p-3.5 bg-slate-950/60 rounded-xl border border-slate-800 space-y-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-400">Slide 2: Industry Presets & Line Items</span>
                <input type="text" name="settings[welcome_step2_title]" value="{{ $settings['welcome_step2_title'] }}" placeholder="Step 2 Title"
                       class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs text-slate-200">
                <textarea name="settings[welcome_step2_text]" rows="2" placeholder="Step 2 Guidance Text"
                          class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs text-slate-300">{{ $settings['welcome_step2_text'] }}</textarea>
            </div>

            <!-- Slide 3 -->
            <div class="p-3.5 bg-slate-950/60 rounded-xl border border-slate-800 space-y-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-400">Slide 3: Draft Safeguard & Confidentiality</span>
                <input type="text" name="settings[welcome_step3_title]" value="{{ $settings['welcome_step3_title'] }}" placeholder="Step 3 Title"
                       class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs text-slate-200">
                <textarea name="settings[welcome_step3_text]" rows="2" placeholder="Step 3 Guidance Text"
                          class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs text-slate-300">{{ $settings['welcome_step3_text'] }}</textarea>
            </div>

            <!-- Slide 4 -->
            <div class="p-3.5 bg-slate-950/60 rounded-xl border border-slate-800 space-y-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-400">Slide 4: Verification QR & Automated Reminders</span>
                <input type="text" name="settings[welcome_step4_title]" value="{{ $settings['welcome_step4_title'] }}" placeholder="Step 4 Title"
                       class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs text-slate-200">
                <textarea name="settings[welcome_step4_text]" rows="2" placeholder="Step 4 Guidance Text"
                          class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs text-slate-300">{{ $settings['welcome_step4_text'] }}</textarea>
            </div>
        </div>

        <div class="pt-4 flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl text-xs transition">
                Save Platform Settings
            </button>
        </div>
    </form>
</div>
@endsection
