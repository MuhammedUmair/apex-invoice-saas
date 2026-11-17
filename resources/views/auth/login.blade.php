@extends('layouts.guest')

@section('content')
@php
    $loginLogo = \App\Models\CmsContent::getLoginLogo();
    $brandName = \App\Models\CmsContent::getVal('website_brand_name', 'Invoicy');
    $brandSub = \App\Models\CmsContent::getVal('website_brand_sub', 'RedMaple');
    $loginTitle = \App\Models\CmsContent::getVal('login_title');
    $loginSubtitle = \App\Models\CmsContent::getVal('login_subtitle', 'Sign in to your commercial invoicing studio');
@endphp
<div class="min-h-[85vh] flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8 py-10">
    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-xl border border-slate-200">
        <div class="text-center mb-8">
            @if($loginLogo)
                <div class="h-16 flex items-center justify-center mx-auto mb-3">
                    <img src="{{ $loginLogo }}" alt="{{ $brandName }}" class="max-h-16 max-w-[220px] object-contain">
                </div>
            @else
                <div class="w-16 h-16 bg-red-50 border border-red-100 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-sm p-3">
                    <!-- Red Maple Leaf Vector Logo -->
                    <svg class="w-full h-full text-red-600 drop-shadow-sm" viewBox="0 0 900 900" fill="currentColor">
                        <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                    </svg>
                </div>
            @endif

            @if($loginTitle)
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">
                    {{ $loginTitle }}
                </h2>
            @else
                <h2 class="text-2xl font-black text-slate-900 tracking-tight flex items-center justify-center gap-1">
                    {{ $brandName }} <span class="text-sm font-normal text-slate-400">by</span> <span class="text-red-600">{{ $brandSub }}</span>
                </h2>
            @endif
            <p class="text-xs text-slate-500 mt-1">{{ $loginSubtitle }}</p>
        </div>

        @php
            $lockedState = !empty($isLocked) || session()->has('lockout_seconds') || $errors->has('lockout');
            $rawSeconds = (int) (session('lockout_seconds') ?? ($lockoutSeconds ?? 0));
            if ($lockedState && $rawSeconds <= 0) {
                $rawSeconds = 3600;
            }
            $initMinutes = max(1, (int) ceil($rawSeconds / 60));
        @endphp

        @if($lockedState)
            <!-- Account Lockout Screen (Form Hidden) -->
            <div x-data="{
                    secondsLeft: {{ $rawSeconds }},
                    init() {
                        const timer = setInterval(() => {
                            if (this.secondsLeft > 0) {
                                this.secondsLeft--;
                            } else {
                                clearInterval(timer);
                                window.location.reload();
                            }
                        }, 1000);
                    },
                    get formattedTime() {
                        const m = Math.floor(this.secondsLeft / 60);
                        const s = this.secondsLeft % 60;
                        return `${m}m ${s < 10 ? '0' : ''}${s}s`;
                    },
                    get minutesRemaining() {
                        return Math.max(1, Math.ceil(this.secondsLeft / 60));
                    }
                 }"
                 class="space-y-6 text-center py-4">

                <div class="mx-auto w-16 h-16 rounded-2xl bg-rose-50 border-2 border-rose-200 flex items-center justify-center text-rose-600 shadow-sm animate-pulse">
                    <i class="ph ph-lock-key text-3xl"></i>
                </div>

                <div class="space-y-2">
                    <h3 class="text-sm font-black text-rose-700 uppercase tracking-wider">Account Access Restricted</h3>
                    <div class="p-4 rounded-xl bg-rose-50/70 border border-rose-200 text-rose-900 text-xs sm:text-sm leading-relaxed font-semibold">
                        Account locked: Too many failed login attempts. Your login is blocked for <span class="font-extrabold text-rose-700" x-text="minutesRemaining">{{ $initMinutes }}</span> minute(s). Please try again later.
                    </div>
                </div>

                <!-- Live Countdown Counter -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2.5">
                    <div class="flex items-center justify-between text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <span>Time Remaining</span>
                        <span class="inline-flex items-center gap-1.5 text-rose-600 font-mono text-sm font-extrabold">
                            <i class="ph ph-hourglass-high animate-spin"></i>
                            <span x-text="formattedTime">{{ $initMinutes }}m 00s</span>
                        </span>
                    </div>
                    <div class="w-full bg-slate-200 h-2.5 rounded-full overflow-hidden">
                        <div class="bg-gradient-to-r from-red-600 to-rose-600 h-full rounded-full transition-all duration-1000"
                             :style="`width: ${Math.min(100, Math.max(0, (secondsLeft / 3600) * 100))}%`"></div>
                    </div>
                    <p class="text-[11px] text-slate-500 font-medium">
                        <span x-text="minutesRemaining">{{ $initMinutes }}</span> minute(s) remaining before login is unlocked.
                    </p>
                </div>

                <div class="pt-2">
                    <a href="{{ route('password.request') }}" class="inline-flex items-center gap-1.5 text-xs text-red-600 hover:text-red-700 font-bold hover:underline">
                        <i class="ph ph-key"></i>
                        <span>Forgot your password? Reset it securely</span>
                    </a>
                </div>
            </div>
        @else
            @if($errors->any())
                <div class="mb-6 p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-5" autocomplete="off"
                  x-data="{ isHumanVerified: false }"
                  @human-verified.window="isHumanVerified = true"
                  @submit="if (!isHumanVerified) { $event.preventDefault(); window.dispatchEvent(new CustomEvent('invoicy-check-human')); }">
                @csrf
                <!-- Anti-autofill decoy inputs to prevent browser from pre-filling saved passwords -->
                <input type="text" style="display:none" name="fakeusernameremembered" tabindex="-1" autocomplete="off"/>
                <input type="password" style="display:none" name="fakepasswordremembered" tabindex="-1" autocomplete="off"/>

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Email Address</label>
                    <input id="email" type="text" name="email" value="{{ old('email') }}" required autofocus
                           autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                           data-lpignore="true" data-1p-ignore="true" data-form-type="other"
                           placeholder="name@company.com"
                           readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');"
                           class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition bg-white">
                </div>

                <div x-data="{ showPass: false }">
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Password</label>
                        <a href="{{ route('password.request') }}" class="text-xs text-red-600 hover:underline font-semibold">
                            Forgot Password?
                        </a>
                    </div>
                    <div class="relative">
                        <input id="password" 
                               type="text" 
                               name="password" 
                               value="" 
                               required 
                               autocomplete="off" 
                               autocorrect="off" 
                               autocapitalize="off" 
                               spellcheck="false" 
                               data-lpignore="true" 
                               data-1p-ignore="true" 
                               data-form-type="other"
                               readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');"
                               :style="showPass ? '-webkit-text-security: none; text-security: none;' : '-webkit-text-security: disc; text-security: disc;'"
                               style="-webkit-text-security: disc; text-security: disc;"
                               placeholder="Enter your password"
                               class="w-full pl-4 pr-11 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition bg-white">
                        <button type="button" @click="showPass = !showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition p-1" title="Toggle password visibility">
                            <i class="ph text-base" :class="showPass ? 'ph-eye-slash' : 'ph-eye'"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500">
                        <span class="text-slate-600 text-xs">Keep me signed in</span>
                    </label>
                </div>

                <!-- Proprietary Invoicy Human Shield (Cloudflare / Turnstile style) -->
                <x-human-shield action="login" />

                <button type="submit" 
                        class="w-full py-3 px-4 font-bold rounded-lg text-sm shadow-md transition flex items-center justify-center gap-2"
                        :class="isHumanVerified ? 'bg-red-600 hover:bg-red-700 text-white hover:shadow-lg cursor-pointer' : 'bg-slate-300 text-slate-600 cursor-not-allowed'">
                    <i class="ph text-base" :class="isHumanVerified ? 'ph-lock-key-open' : 'ph-lock-key'"></i>
                    <span x-text="isHumanVerified ? 'Sign In to RedMaple' : 'Verify Humanity to Sign In'"></span>
                </button>
            </form>
        @endif

        <div class="mt-6 pt-6 border-t border-slate-100 flex flex-col gap-2 text-center text-xs text-slate-500">
            <div>
                Don't have an agency account yet? 
                <a href="{{ route('register') }}" class="text-red-600 font-bold hover:underline">Start free trial</a>
            </div>
            <div class="pt-2">
                <a href="{{ route('docs.index') }}" class="inline-flex items-center gap-1.5 text-slate-600 hover:text-red-600 font-medium">
                    <i class="ph ph-book-open text-sky-600"></i>
                    <span>Multilingual User Manual & Guide (اردو, हिन्दी, Türkçe, English)</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
