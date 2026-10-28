@extends('layouts.guest')

@section('content')
<div class="min-h-[90vh] flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8 py-10" x-data="{
    password: '',
    get lengthOk() { return this.password.length >= 8; },
    get upperOk() { return /[A-Z]/.test(this.password) && /[a-z]/.test(this.password); },
    get numberOk() { return /[0-9]/.test(this.password); },
    get symbolOk() { return /[^A-Za-z0-9]/.test(this.password); },
    get strength() {
        let score = 0;
        if (this.lengthOk) score++;
        if (this.upperOk) score++;
        if (this.numberOk) score++;
        if (this.symbolOk) score++;
        return score;
    }
}">
    <div class="w-full max-w-lg bg-white p-8 sm:p-10 rounded-2xl shadow-xl border border-slate-200">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-red-50 border border-red-100 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-sm p-3">
                <!-- Red Maple Leaf Vector Logo -->
                <svg class="w-full h-full text-red-600 drop-shadow-sm" viewBox="0 0 900 900" fill="currentColor">
                    <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                </svg>
            </div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight flex items-center justify-center gap-1">
                Invoicy <span class="text-sm font-normal text-slate-400">by</span> <span class="text-red-600">RedMaple</span>
            </h2>
            <p class="text-xs text-slate-500 mt-1">Start your 14-day free trial. No credit card required.</p>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.post') }}" class="space-y-4">
            @csrf
            <div>
                <label for="company_name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Agency or Business Name</label>
                <input id="company_name" type="text" name="company_name" value="{{ old('company_name') }}" required placeholder="e.g. Acme Creative Agency"
                       class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Your Full Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required placeholder="Alex Rivera"
                           class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition">
                </div>
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="alex@acme.com"
                           class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="country_code" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Country (for Tax)</label>
                    <select id="country_code" name="country_code" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition bg-white">
                        <option value="US">United States (Sales Tax)</option>
                        <option value="GB">United Kingdom (VAT 20%)</option>
                        <option value="DE">Germany (MwSt 19%)</option>
                        <option value="FR">France (TVA 20%)</option>
                        <option value="ES">Spain (IVA 21%)</option>
                        <option value="AE">United Arab Emirates (VAT 5%)</option>
                        <option value="SA">Saudi Arabia (VAT 15%)</option>
                        <option value="AU">Australia (GST 10%)</option>
                        <option value="CA">Canada (GST/HST 13%)</option>
                        <option value="IN">India (GST 18%)</option>
                        <option value="TR">Turkey (KDV 20%)</option>
                        <option value="PK">Pakistan (SST 16%)</option>
                    </select>
                </div>
                <div>
                    <label for="currency" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Default Currency</label>
                    <select id="currency" name="currency" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition bg-white">
                        <option value="USD">USD ($)</option>
                        <option value="EUR">EUR (€)</option>
                        <option value="GBP">GBP (£)</option>
                        <option value="AED">AED (د.إ)</option>
                        <option value="SAR">SAR (﷼)</option>
                        <option value="CAD">CAD (C$)</option>
                        <option value="AUD">AUD (A$)</option>
                        <option value="INR">INR (₹)</option>
                        <option value="TRY">TRY (₺)</option>
                        <option value="PKR">PKR (Rs)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Strong Password *</label>
                    <input id="password" type="password" name="password" x-model="password" required placeholder="Min 8 chars, Aa, 0-9, @"
                           class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Confirm Password *</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="Repeat password"
                           class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition">
                </div>
            </div>

            <!-- Real-time Password Strength Meter -->
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-2" x-show="password.length > 0">
                <div class="flex items-center justify-between font-semibold text-slate-700">
                    <span>Password Security Strength:</span>
                    <span :class="{
                        'text-rose-600': strength <= 2,
                        'text-amber-600': strength === 3,
                        'text-emerald-600': strength === 4
                    }" x-text="strength <= 2 ? 'Weak' : (strength === 3 ? 'Medium' : 'Strong')"></span>
                </div>
                <div class="grid grid-cols-4 gap-1 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                    <div class="h-full transition-all" :class="strength >= 1 ? (strength <= 2 ? 'bg-rose-500' : (strength === 3 ? 'bg-amber-500' : 'bg-emerald-500')) : ''"></div>
                    <div class="h-full transition-all" :class="strength >= 2 ? (strength <= 2 ? 'bg-rose-500' : (strength === 3 ? 'bg-amber-500' : 'bg-emerald-500')) : ''"></div>
                    <div class="h-full transition-all" :class="strength >= 3 ? (strength === 3 ? 'bg-amber-500' : 'bg-emerald-500') : ''"></div>
                    <div class="h-full transition-all" :class="strength >= 4 ? 'bg-emerald-500' : ''"></div>
                </div>
                <div class="grid grid-cols-2 gap-1 text-[11px] text-slate-500 pt-1">
                    <div class="flex items-center gap-1" :class="lengthOk ? 'text-emerald-700 font-bold' : ''">
                        <i :class="lengthOk ? 'ph-bold ph-check text-emerald-600' : 'ph ph-circle text-slate-400'"></i> Min 8 characters
                    </div>
                    <div class="flex items-center gap-1" :class="upperOk ? 'text-emerald-700 font-bold' : ''">
                        <i :class="upperOk ? 'ph-bold ph-check text-emerald-600' : 'ph ph-circle text-slate-400'"></i> Uppercase & lowercase
                    </div>
                    <div class="flex items-center gap-1" :class="numberOk ? 'text-emerald-700 font-bold' : ''">
                        <i :class="numberOk ? 'ph-bold ph-check text-emerald-600' : 'ph ph-circle text-slate-400'"></i> Number (0-9)
                    </div>
                    <div class="flex items-center gap-1" :class="symbolOk ? 'text-emerald-700 font-bold' : ''">
                        <i :class="symbolOk ? 'ph-bold ph-check text-emerald-600' : 'ph ph-circle text-slate-400'"></i> Special symbol (!@#$)
                    </div>
                </div>
            </div>

            <!-- Promotional / Referral Coupon Code Field (Item 4) -->
            <div class="pt-1">
                <label for="coupon_code" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                    <span class="flex items-center gap-1.5">
                        <i class="ph ph-ticket text-fuchsia-600 text-sm"></i> Promo / Referral Code
                    </span>
                    <span class="text-[10px] text-slate-400 font-normal lowercase">(optional)</span>
                </label>
                <div class="relative">
                    <input id="coupon_code" type="text" name="coupon_code" value="{{ old('coupon_code', request('promo') ?? request('ref')) }}" placeholder="e.g. WELCOME50, FREESERVICE"
                           class="w-full pl-9 pr-4 py-2.5 rounded-lg border border-slate-300 text-sm uppercase font-mono tracking-wide focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition bg-white">
                    <i class="ph ph-tag absolute left-3 top-3 text-slate-400 text-base"></i>
                </div>
                <p class="text-[11px] text-slate-500 mt-1">Have an offer code? Enter it above to unlock exclusive discounts, extended trial, or free service credits.</p>
            </div>

            <button type="submit" class="w-full mt-2 py-3 px-4 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2">
                <i class="ph ph-rocket-launch text-base"></i>
                Create Invoicy Workspace
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-100 text-center text-xs text-slate-500">
            Already registered? 
            <a href="{{ route('login') }}" class="text-red-600 font-bold hover:underline">Log in here</a>
        </div>
    </div>
</div>
@endsection
