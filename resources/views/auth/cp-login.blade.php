@extends('layouts.guest')

@section('content')
<div class="min-h-[85vh] flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8 py-10">
    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-2xl border-2 border-slate-900/10 relative overflow-hidden">
        <!-- Top Accent Bar for Control Panel -->
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-amber-500 via-red-600 to-rose-700"></div>

        <div class="text-center mb-8 pt-2">
            <!-- Super Admin Badge -->
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-300 shadow-2xs mb-4">
                <i class="ph ph-shield-check text-amber-600 text-sm"></i>
                <span>Platform Control Panel</span>
            </div>

            <div class="w-16 h-16 bg-slate-900 text-amber-400 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-md border border-slate-800">
                <!-- Red Maple & Shield Symbol -->
                <i class="ph ph-shield-star text-3xl text-amber-400"></i>
            </div>

            <h2 class="text-2xl font-black text-slate-900 tracking-tight">
                Invoicy <span class="text-red-600">CP</span>
            </h2>
            <p class="text-xs text-slate-500 mt-1 font-medium">Authorized Platform Super Administrators Only</p>
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
                    <h3 class="text-sm font-black text-rose-700 uppercase tracking-wider">Control Panel Access Locked</h3>
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
            </div>
        @else
            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('cp.login.post') }}" class="space-y-5" autocomplete="off">
                @csrf
                <!-- Anti-autofill decoy inputs -->
                <input type="text" style="display:none" name="fakeusernameremembered" tabindex="-1" autocomplete="off"/>
                <input type="password" style="display:none" name="fakepasswordremembered" tabindex="-1" autocomplete="off"/>

                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 flex items-center justify-between">
                        <span>Super Admin Email</span>
                        <span class="text-[10px] text-amber-700 font-semibold bg-amber-50 px-2 py-0.5 rounded border border-amber-200">Root Access</span>
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                           data-lpignore="true" data-1p-ignore="true" data-form-type="other"
                           placeholder="superadmin@redmapleagency.com"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white font-medium">
                </div>

                <div x-data="{ showPass: false }">
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Super Admin Password</label>
                        <a href="{{ route('password.request') }}" class="text-xs text-amber-700 hover:underline font-semibold">
                            Forgot?
                        </a>
                    </div>
                    <div class="relative">
                        <input id="password" 
                               :type="showPass ? 'text' : 'password'" 
                               name="password" 
                               value="" 
                               required 
                               autocomplete="new-password" 
                               autocorrect="off" 
                               autocapitalize="off" 
                               spellcheck="false" 
                               data-lpignore="true" 
                               data-1p-ignore="true" 
                               data-form-type="other"
                               x-ref="pwdInput"
                               x-init="$nextTick(() => { if ($refs.pwdInput) $refs.pwdInput.value = ''; })"
                               placeholder="Enter root password"
                               class="w-full pl-4 pr-11 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white font-medium">
                        <button type="button" @click="showPass = !showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition p-1 cursor-pointer" title="Toggle password visibility">
                            <i class="ph text-base" :class="showPass ? 'ph-eye-slash text-amber-600' : 'ph-eye text-slate-400'"></i>
                        </button>
                    </div>
                </div>



                <button type="submit" class="w-full py-3 px-4 bg-slate-900 hover:bg-slate-800 text-amber-400 font-bold rounded-xl text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2 border border-slate-800 group">
                    <i class="ph ph-shield-check text-lg text-amber-400 group-hover:scale-110 transition-transform"></i>
                    <span>Authenticate into Control Panel</span>
                </button>
            </form>
        @endif

        <div class="mt-6 pt-6 border-t border-slate-100 flex flex-col gap-2 text-center text-xs text-slate-500">
            <div>
                Are you an agency tenant or client? 
                <a href="{{ route('login') }}" class="text-red-600 font-bold hover:underline">Standard Agency Login</a>
            </div>
            <div class="pt-2 text-[11px] text-slate-400">
                🔒 Invoicy Platform Administration &bull; High-Security Area
            </div>
        </div>
    </div>
</div>
@endsection
