@extends('layouts.guest')

@section('content')
<div class="min-h-[90vh] flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8 py-10">
    <div class="w-full max-w-lg bg-white p-8 sm:p-10 rounded-2xl shadow-xl border border-slate-200">
        <div class="text-center mb-8">
            <div class="w-12 h-12 bg-blue-600 text-white rounded-xl flex items-center justify-center mx-auto mb-3 font-black text-2xl shadow-md">
                A
            </div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Create Agency Account</h2>
            <p class="text-sm text-slate-500 mt-1">Start your 14-day free trial. No credit card required.</p>
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
                       class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Your Full Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required placeholder="Alex Rivera"
                           class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                </div>
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="alex@acme.com"
                           class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="country_code" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Country (for Tax)</label>
                    <select id="country_code" name="country_code" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-white">
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
                    <select id="currency" name="currency" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-white">
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
                    <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Password</label>
                    <input id="password" type="password" name="password" required placeholder="Min 8 characters"
                           class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="Repeat password"
                           class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                </div>
            </div>

            <button type="submit" class="w-full mt-2 py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2">
                <i class="ph ph-rocket-launch text-base"></i>
                Launch Free Trial Workspace
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-100 text-center text-xs text-slate-500">
            Already registered? 
            <a href="{{ route('login') }}" class="text-blue-600 font-semibold hover:underline">Log in here</a>
        </div>
    </div>
</div>
@endsection
