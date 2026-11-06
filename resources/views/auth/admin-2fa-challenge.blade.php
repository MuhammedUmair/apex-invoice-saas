@extends('layouts.guest')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-slate-200 p-8 space-y-6">
        <div class="text-center space-y-2">
            <div class="w-14 h-14 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center mx-auto shadow-inner border border-red-100">
                <i class="ph ph-shield-check text-3xl"></i>
            </div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Super Admin Two-Factor Authentication</h2>
            <p class="text-xs text-slate-500">Please enter the 6-digit TOTP authentication code from your mobile authenticator app to proceed to the administrative panel.</p>
        </div>

        @if($errors->any())
            <div class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.2fa.verify') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 text-center">6-Digit Security Code</label>
                <input type="text" name="two_factor_code" maxlength="6" autofocus required
                       class="w-full text-center text-2xl font-mono tracking-widest py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:outline-none"
                       placeholder="000000">
            </div>

            <button type="submit" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-bold text-sm rounded-xl transition shadow-md">
                Verify & Enter Super Admin
            </button>
        </form>

        <div class="text-center pt-2">
            <a href="{{ route('dashboard') }}" class="text-xs font-medium text-slate-400 hover:text-slate-600">
                Return to Workspace Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
