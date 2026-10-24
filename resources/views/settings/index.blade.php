@extends('layouts.app', ['title' => 'Agency Settings - Invoicy', 'header' => __('Agency Settings')])

@section('content')
<div class="max-w-5xl mx-auto space-y-8">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Workspace & Invoicing Configuration</h2>
            <p class="text-xs text-slate-500 mt-0.5">Manage your agency profile, wire transfer details, brand styling, and payment gateway keys</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                <span class="w-2 h-2 rounded-full bg-red-600 animate-pulse"></span>
                Tenant: {{ $workspace->slug }}
            </span>
        </div>
    </div>

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <!-- Card 1: Agency Business Profile -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-xl shrink-0">
                    <i class="ph ph-buildings"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Agency Profile & Contact Information</h3>
                    <p class="text-xs text-slate-500">Information displayed on generated invoices, customer portal, and PDF headers</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Agency / Company Name *</label>
                    <input type="text" name="name" value="{{ old('name', $workspace->name) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                    @error('name') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">VAT / Tax Identification Number</label>
                    <input type="text" name="tax_id" value="{{ old('tax_id', $workspace->tax_id) }}" placeholder="US-987654321 / GB123456"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Billing Email</label>
                    <input type="email" name="email" value="{{ old('email', $workspace->email) }}" placeholder="billing@agency.com"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $workspace->phone) }}" placeholder="+1 (555) 019-2834"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Website URL</label>
                    <input type="url" name="website" value="{{ old('website', $workspace->website) }}" placeholder="https://agency.com"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Physical Business Address</label>
                <textarea name="address" rows="2" placeholder="Suite 100, 742 Evergreen Terrace..."
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">{{ old('address', $workspace->address) }}</textarea>
            </div>
        </div>

        <!-- Card 2: Regional Defaults & Multi-Language -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                    <i class="ph ph-globe"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Regional Defaults & Invoice Language</h3>
                    <p class="text-xs text-slate-500">Default currency, jurisdiction tax defaults, and multilingual presets</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Default Language</label>
                    <select name="language" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 bg-white">
                        <option value="en" {{ old('language', $workspace->language) === 'en' ? 'selected' : '' }}>🇺🇸 English</option>
                        <option value="ur" {{ old('language', $workspace->language) === 'ur' ? 'selected' : '' }}>🇵🇰 Urdu (اردو)</option>
                        <option value="hi" {{ old('language', $workspace->language) === 'hi' ? 'selected' : '' }}>🇮🇳 Hindi (हिन्दी)</option>
                        <option value="tr" {{ old('language', $workspace->language) === 'tr' ? 'selected' : '' }}>🇹🇷 Turkish (Türkçe)</option>
                        <option value="ar" {{ old('language', $workspace->language) === 'ar' ? 'selected' : '' }}>🇦🇪 Arabic (العربية)</option>
                        <option value="es" {{ old('language', $workspace->language) === 'es' ? 'selected' : '' }}>🇪🇸 Spanish (Español)</option>
                        <option value="fr" {{ old('language', $workspace->language) === 'fr' ? 'selected' : '' }}>🇫🇷 French (Français)</option>
                        <option value="de" {{ old('language', $workspace->language) === 'de' ? 'selected' : '' }}>🇩🇪 German (Deutsch)</option>
                        <option value="zh" {{ old('language', $workspace->language) === 'zh' ? 'selected' : '' }}>🇨🇳 Chinese (中文)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Primary Currency</label>
                    <select name="currency" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 bg-white">
                        <option value="USD" {{ old('currency', $workspace->currency) === 'USD' ? 'selected' : '' }}>USD ($)</option>
                        <option value="EUR" {{ old('currency', $workspace->currency) === 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                        <option value="GBP" {{ old('currency', $workspace->currency) === 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                        <option value="AED" {{ old('currency', $workspace->currency) === 'AED' ? 'selected' : '' }}>AED (د.إ)</option>
                        <option value="SAR" {{ old('currency', $workspace->currency) === 'SAR' ? 'selected' : '' }}>SAR (﷼)</option>
                        <option value="CAD" {{ old('currency', $workspace->currency) === 'CAD' ? 'selected' : '' }}>CAD (C$)</option>
                        <option value="AUD" {{ old('currency', $workspace->currency) === 'AUD' ? 'selected' : '' }}>AUD (A$)</option>
                        <option value="INR" {{ old('currency', $workspace->currency) === 'INR' ? 'selected' : '' }}>INR (₹)</option>
                        <option value="TRY" {{ old('currency', $workspace->currency) === 'TRY' ? 'selected' : '' }}>TRY (₺)</option>
                        <option value="PKR" {{ old('currency', $workspace->currency) === 'PKR' ? 'selected' : '' }}>PKR (Rs)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tax Jurisdiction Country</label>
                    <select name="country_code" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 bg-white">
                        <option value="US" {{ old('country_code', $workspace->country_code) === 'US' ? 'selected' : '' }}>United States</option>
                        <option value="PK" {{ old('country_code', $workspace->country_code) === 'PK' ? 'selected' : '' }}>Pakistan</option>
                        <option value="IN" {{ old('country_code', $workspace->country_code) === 'IN' ? 'selected' : '' }}>India</option>
                        <option value="TR" {{ old('country_code', $workspace->country_code) === 'TR' ? 'selected' : '' }}>Turkey</option>
                        <option value="AE" {{ old('country_code', $workspace->country_code) === 'AE' ? 'selected' : '' }}>UAE</option>
                        <option value="SA" {{ old('country_code', $workspace->country_code) === 'SA' ? 'selected' : '' }}>Saudi Arabia</option>
                        <option value="GB" {{ old('country_code', $workspace->country_code) === 'GB' ? 'selected' : '' }}>United Kingdom</option>
                        <option value="CA" {{ old('country_code', $workspace->country_code) === 'CA' ? 'selected' : '' }}>Canada</option>
                        <option value="AU" {{ old('country_code', $workspace->country_code) === 'AU' ? 'selected' : '' }}>Australia</option>
                        <option value="DE" {{ old('country_code', $workspace->country_code) === 'DE' ? 'selected' : '' }}>Germany</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Card 3: Bank Details for Invoices -->
        @php
            $bank = is_array($workspace->bank_details) ? $workspace->bank_details : (json_decode($workspace->bank_details, true) ?? []);
        @endphp
        <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                    <i class="ph ph-bank"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Bank Wire & Remittance Instructions</h3>
                    <p class="text-xs text-slate-500">Printed directly on customer PDF invoices and payment portal instructions</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Bank Name</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $bank['bank_name'] ?? '') }}" placeholder="Silicon Valley Commercial Bank"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Account / IBAN Number</label>
                    <input type="text" name="account_number" value="{{ old('account_number', $bank['account_number'] ?? '') }}" placeholder="US89 3704 0044 0532 0130 00"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Routing / Sort Code</label>
                    <input type="text" name="routing_number" value="{{ old('routing_number', $bank['routing_number'] ?? '') }}" placeholder="121000358"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">SWIFT / BIC Code</label>
                    <input type="text" name="swift" value="{{ old('swift', $bank['swift'] ?? '') }}" placeholder="SVCBUS33"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                </div>
            </div>
        </div>

        <!-- Card 4: Brand Identity, Custom Logo & Colors -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shrink-0">
                    <i class="ph ph-palette"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Brand Customization, Logo & Colors</h3>
                    <p class="text-xs text-slate-500">Update agency logo and accent colors across your invoices and client portal</p>
                </div>
            </div>

            <!-- Logo Upload & Customization -->
            <div class="bg-slate-50 p-5 rounded-xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Agency Brand Logo</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Displays in app header, customer payment portal, and PDF invoices</p>
                    </div>
                    @if($workspace->logo_url)
                        <label class="flex items-center gap-1.5 text-xs text-rose-600 font-semibold cursor-pointer">
                            <input type="checkbox" name="remove_logo" value="1" class="rounded text-rose-600 focus:ring-rose-500">
                            Reset to Default RedMaple Logo
                        </label>
                    @endif
                </div>

                <div class="flex items-center gap-5">
                    <div class="w-16 h-16 rounded-xl border border-slate-300 bg-white p-2 flex items-center justify-center shrink-0 shadow-sm overflow-hidden">
                        @if($workspace->logo_url)
                            <img src="{{ $workspace->logo_url }}" alt="Agency Logo" class="w-full h-full object-contain">
                        @else
                            <svg class="w-full h-full text-red-600" viewBox="0 0 900 900" fill="currentColor">
                                <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                            </svg>
                        @endif
                    </div>
                    <div class="flex-1 space-y-2">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Upload New Logo (PNG, JPG, SVG, WebP up to 2MB)</label>
                            <input type="file" name="logo" accept="image/*"
                                   class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Or Logo Image URL</label>
                            <input type="url" name="logo_url" value="{{ old('logo_url', $workspace->logo_url) }}" placeholder="https://youragency.com/logo.png"
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Primary Brand Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="brand_color" value="{{ old('brand_color', $workspace->brand_color ?? '#dc2626') }}"
                               class="w-12 h-11 p-1 rounded-xl border border-slate-300 cursor-pointer">
                        <input type="text" value="{{ old('brand_color', $workspace->brand_color ?? '#dc2626') }}" readonly
                               class="flex-1 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-mono text-slate-600">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Secondary Header Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="secondary_color" value="{{ old('secondary_color', $workspace->secondary_color ?? '#1e293b') }}"
                               class="w-12 h-11 p-1 rounded-xl border border-slate-300 cursor-pointer">
                        <input type="text" value="{{ old('secondary_color', $workspace->secondary_color ?? '#1e293b') }}" readonly
                               class="flex-1 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-mono text-slate-600">
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_whitelabel_enabled" value="1" {{ old('is_whitelabel_enabled', $workspace->is_whitelabel_enabled) ? 'checked' : '' }}
                           class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500">
                    <span class="text-sm font-medium text-slate-700">Enable Whitelabel Mode (Removes "Powered by RedMaple" badge from client PDFs)</span>
                </label>
            </div>
        </div>

        <!-- Card 5: Payment Gateway API Keys -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                    <i class="ph ph-key"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Payment Gateway API Keys (Stripe & PayPal)</h3>
                    <p class="text-xs text-slate-500">Direct client payments deposit directly into your agency merchant account</p>
                </div>
            </div>

            <div class="space-y-4">
                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                    <i class="ph ph-stripe-logo text-lg text-indigo-600"></i> Stripe Integration
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Publishable Key</label>
                        <input type="text" name="stripe_publishable_key" placeholder="pk_live_..."
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Secret Key</label>
                        <input type="password" name="stripe_secret_key" placeholder="sk_live_..."
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-red-500">
                    </div>
                </div>
            </div>

            <div class="space-y-4 pt-4 border-t border-slate-100">
                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                    <i class="ph ph-paypal-logo text-lg text-sky-600"></i> PayPal Integration
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Client ID</label>
                        <input type="text" name="paypal_client_id" placeholder="Client ID from PayPal Developer"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Secret</label>
                        <input type="password" name="paypal_client_secret" placeholder="Secret Key"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-red-500">
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-4 pb-12">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-md transition text-sm">
                <i class="ph ph-floppy-disk text-base"></i> Save Workspace Settings
            </button>
        </div>
    </form>

</div>
@endsection
