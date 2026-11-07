@extends('layouts.guest')

@section('content')
<div class="min-h-[85vh] flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8 py-10"
     x-data="{
         remainingSeconds: {{ $remainingSeconds ?? 600 }},
         resendCountdown: 60,
         canResend: false,
         init() {
             const timer = setInterval(() => {
                 if (this.remainingSeconds > 0) {
                     this.remainingSeconds--;
                 }
                 if (this.resendCountdown > 0) {
                     this.resendCountdown--;
                 } else {
                     this.canResend = true;
                 }
             }, 1000);
         },
         formatTime(sec) {
             const m = Math.floor(sec / 60);
             const s = sec % 60;
             return m + ':' + (s < 10 ? '0' : '') + s;
         }
     }">
    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-2xl border-2 border-slate-900/10 relative overflow-hidden">
        <!-- Top Accent Bar for Control Panel -->
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-amber-500 via-red-600 to-rose-700"></div>

        <div class="text-center mb-6 pt-2">
            <!-- Super Admin Badge -->
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-300 shadow-2xs mb-4">
                <i class="ph ph-shield-check text-amber-600 text-sm"></i>
                <span>Platform Control Panel</span>
            </div>

            <div class="w-16 h-16 bg-slate-900 text-amber-400 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-md border border-slate-800">
                <i class="ph ph-lock-key text-3xl text-amber-400"></i>
            </div>

            <h2 class="text-2xl font-black text-slate-900 tracking-tight">
                Two-Factor <span class="text-red-600">Verification</span>
            </h2>
            <p class="text-xs text-slate-500 mt-1 font-medium">
                Enter the 6-digit one-time code dispatched to
            </p>
            <p class="text-xs font-bold text-slate-800 font-mono mt-0.5">
                {{ $maskedEmail }}
            </p>
        </div>

        @if (session('status'))
            <div class="mb-5 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
                <i class="ph ph-check-circle text-base text-emerald-600 shrink-0"></i>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 p-3 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs font-bold flex items-center gap-2">
                <i class="ph ph-warning-circle text-base text-red-600 shrink-0"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- OTP Form -->
        <form method="POST" action="{{ route('cp.otp.verify') }}" class="space-y-5">
            @csrf

            <div>
                <label for="otp" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 text-center">
                    6-Digit One-Time Security Passcode
                </label>
                <div class="relative">
                    <input id="otp"
                           type="text"
                           name="otp"
                           maxlength="6"
                           required
                           autofocus
                           autocomplete="one-time-code"
                           inputmode="numeric"
                           pattern="[0-9]{6}"
                           placeholder="000000"
                           class="w-full text-center tracking-[12px] text-2xl font-mono font-black py-3 px-4 rounded-xl border-2 border-slate-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-slate-50 focus:bg-white text-slate-900 shadow-inner">
                </div>
                <div class="flex items-center justify-between mt-2 text-xs text-slate-500">
                    <span class="flex items-center gap-1">
                        <i class="ph ph-clock text-slate-400"></i>
                        Expires in:
                        <strong class="font-mono text-red-600" x-text="formatTime(remainingSeconds)"></strong>
                    </span>
                    <span class="text-[11px] text-slate-400">Numeric only</span>
                </div>
            </div>

            <button type="submit"
                    class="w-full py-3 px-4 bg-slate-900 hover:bg-slate-800 text-amber-400 font-bold rounded-xl text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2 border border-slate-800 group">
                <i class="ph ph-shield-check text-lg text-amber-400 group-hover:scale-110 transition-transform"></i>
                <span>Verify & Enter Control Panel</span>
            </button>
        </form>

        <!-- Resend Code Form -->
        <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-between text-xs">
            <form method="POST" action="{{ route('cp.otp.resend') }}">
                @csrf
                <button type="submit"
                        :disabled="!canResend"
                        class="text-amber-700 font-bold hover:underline disabled:text-slate-400 disabled:no-underline disabled:cursor-not-allowed flex items-center gap-1">
                    <i class="ph ph-arrows-clockwise"></i>
                    <span>Resend Code</span>
                    <span x-show="!canResend" class="text-[11px] font-mono text-slate-400" x-text="'(' + resendCountdown + 's)'"></span>
                </button>
            </form>

            <a href="{{ route('cp.login') }}" class="text-slate-500 hover:text-slate-800 hover:underline">
                Back to Sign In
            </a>
        </div>

        <div class="mt-4 text-center text-[11px] text-slate-400">
            🔒 Invoicy Multi-Factor Authentication Active
        </div>
    </div>
</div>
@endsection
