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
    <div class="flex items-center gap-2 border-b border-slate-800 pb-2 text-xs overflow-x-auto">
        <a href="{{ route('admin.config.general') }}" class="px-3.5 py-1.5 rounded-xl font-bold bg-amber-500 text-slate-950">General</a>
        <a href="{{ route('admin.config.tour') }}" class="px-3.5 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 flex items-center gap-1.5 font-bold">
            <i class="ph ph-compass"></i> Welcome Tour
        </a>
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

        <!-- Welcome Walkthrough & Onboarding Tour Link Card -->
        <div class="pt-6 mt-6 border-t border-slate-800">
            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-compass"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-white flex items-center gap-1.5">
                            <span>Welcome Walkthrough & Onboarding Tour</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">Dedicated Tab</span>
                        </h4>
                        <p class="text-[11px] text-slate-400 mt-0.5">Manage slides, reorder, or add and remove walkthrough sections in its own dedicated tab.</p>
                    </div>
                </div>
                <a href="{{ route('admin.config.tour') }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold rounded-xl transition flex items-center gap-1.5 shrink-0 shadow-sm">
                    <span>Configure Tour Slides</span>
                    <i class="ph ph-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- AI Agent Configuration (Master Prompt Phase 9) -->
        <div class="pt-6 mt-6 border-t border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center gap-1.5">
                        <i class="ph ph-robot text-rose-400 text-base"></i>
                        <span>AI Agent & Multilingual Financial Assistant</span>
                    </h3>
                    <p class="text-[11px] text-slate-400">Controls conversational intelligence, external LLM provider, and system prompts.</p>
                </div>
                <div class="text-right">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tenant Visibility (Show / Hide)</label>
                    <select name="settings[ai_agent_enabled]" class="px-3.5 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs font-bold text-rose-400 focus:ring-2 focus:ring-rose-500 outline-none">
                        <option value="1" {{ ($settings['ai_agent_enabled'] ?? '1') == '1' ? 'selected' : '' }}>Show for Tenants (Enabled)</option>
                        <option value="0" {{ ($settings['ai_agent_enabled'] ?? '1') == '0' ? 'selected' : '' }}>Hide from Tenants (Disabled)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">AI Provider</label>
                    <select name="settings[ai_provider]" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-200 focus:ring-2 focus:ring-amber-500 outline-none">
                        <option value="auto" {{ ($settings['ai_provider'] ?? 'auto') == 'auto' ? 'selected' : '' }}>Auto (External LLM when key set, else Built-in Engine)</option>
                        <option value="openai" {{ ($settings['ai_provider'] ?? '') == 'openai' ? 'selected' : '' }}>OpenAI API (GPT-4o / GPT-4o-mini)</option>
                        <option value="gemini" {{ ($settings['ai_provider'] ?? '') == 'gemini' ? 'selected' : '' }}>Google Gemini API (Gemini 1.5 Flash)</option>
                        <option value="local" {{ ($settings['ai_provider'] ?? '') == 'local' ? 'selected' : '' }}>Built-in Multilingual High-Speed Engine (Zero API Cost)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">LLM Model Name</label>
                    <input type="text" name="settings[ai_model]" value="{{ $settings['ai_model'] ?? 'gpt-4o-mini' }}" placeholder="e.g. gpt-4o-mini, gpt-4o, gemini-1.5-flash"
                           class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-200 focus:ring-2 focus:ring-amber-500 outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">OpenAI API Key (Optional)</label>
                    <input type="password" name="settings[openai_api_key]" value="{{ $settings['openai_api_key'] ?? '' }}" placeholder="sk-..."
                           class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-200 focus:ring-2 focus:ring-amber-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Gemini API Key (Optional)</label>
                    <input type="password" name="settings[gemini_api_key]" value="{{ $settings['gemini_api_key'] ?? '' }}" placeholder="AIzaSy..."
                           class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-200 focus:ring-2 focus:ring-amber-500 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Custom System Instructions / Knowledge Overrides</label>
                <textarea name="settings[ai_system_prompt]" rows="3" placeholder="Enter custom instructions or brand behavior guidelines..."
                          class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-200">{{ $settings['ai_system_prompt'] ?? '' }}</textarea>
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
