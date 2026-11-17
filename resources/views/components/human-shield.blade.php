@props([
    'action' => 'general',
    'id' => 'human-shield-' . uniqid()
])

@php
    $challenge = app(\App\Services\HumanVerificationService::class)->generateChallenge($action);
@endphp

<div id="{{ $id }}"
     x-data="invoicyHumanShield({
         token: '{{ $challenge['token'] }}',
         nonce: '{{ $challenge['nonce'] }}',
         action: '{{ $action }}',
         question: '{{ $challenge['question'] }}'
     })"
     class="w-full my-3 select-none"
     x-cloak>

    <!-- Hidden verification inputs passed to form -->
    <input type="hidden" name="human_challenge_token" :value="token">
    <input type="hidden" name="human_solution" :value="solution">
    <input type="hidden" name="human_telemetry" :value="telemetry">

    <!-- Cloudflare-style Turnstile Card -->
    <div class="relative overflow-hidden rounded-xl border p-3.5 transition-all duration-300 shadow-xs"
         :class="{
             'border-slate-200 bg-gradient-to-r from-slate-50 to-white hover:border-slate-300': state === 'idle',
             'border-amber-300 bg-amber-50/40': state === 'verifying',
             'border-emerald-300 bg-gradient-to-r from-emerald-50/60 to-white ring-1 ring-emerald-400/30': state === 'verified',
             'border-rose-300 bg-rose-50/50 animate-shake': state === 'error'
         }">

        <div class="flex items-center justify-between gap-3">
            <!-- Left: Interactive Checkbox / Spinner / Success -->
            <div class="flex items-center gap-3 cursor-pointer" @click="handleInteraction">
                <!-- Checkbox Element -->
                <div class="w-7 h-7 rounded-lg flex items-center justify-center transition-all duration-300 shadow-xs"
                     :class="{
                         'border-2 border-slate-400 bg-white hover:border-red-500 hover:scale-105': state === 'idle',
                         'border-2 border-amber-400 bg-amber-100/80': state === 'verifying',
                         'bg-emerald-600 text-white border-2 border-emerald-600 shadow-emerald-200': state === 'verified',
                         'border-2 border-rose-500 bg-rose-100 text-rose-600': state === 'error'
                     }">
                    <!-- Idle state: unchecked -->
                    <template x-if="state === 'idle'">
                        <div class="w-3 h-3 rounded-xs bg-transparent"></div>
                    </template>

                    <!-- Verifying state: animated spinner -->
                    <template x-if="state === 'verifying'">
                        <svg class="animate-spin w-4 h-4 text-amber-600" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>

                    <!-- Verified state: checkmark -->
                    <template x-if="state === 'verified'">
                        <i class="ph-bold ph-check text-base animate-bounce-short"></i>
                    </template>

                    <!-- Error state: exclamation mark -->
                    <template x-if="state === 'error'">
                        <i class="ph-bold ph-warning text-sm"></i>
                    </template>
                </div>

                <!-- Status Labels -->
                <div class="text-left">
                    <div class="text-xs font-bold transition-colors leading-tight"
                         :class="{
                             'text-slate-800': state === 'idle',
                             'text-amber-800': state === 'verifying',
                             'text-emerald-800': state === 'verified',
                             'text-rose-700': state === 'error'
                         }">
                        <span x-show="state === 'idle'">Verify you are human</span>
                        <span x-show="state === 'verifying'">Verifying human integrity...</span>
                        <span x-show="state === 'verified'">Verification successful</span>
                        <span x-show="state === 'error'" x-text="errorMessage || 'Verification failed. Click to retry.'"></span>
                    </div>
                    <div class="text-[10px] text-slate-500 mt-0.5 leading-tight">
                        <span x-show="state === 'idle'">Click checkbox to authenticate browser session</span>
                        <span x-show="state === 'verifying'">Authenticating secure session token...</span>
                        <span x-show="state === 'verified'" class="text-emerald-700 font-medium">Session secured & authenticated</span>
                        <span x-show="state === 'error'">Click the box above to attempt verification again</span>
                    </div>
                </div>
            </div>

            <!-- Right: Branded Security Badge -->
            <div class="flex items-center gap-2 pl-3 border-l border-slate-200/80 shrink-0 text-right">
                <div class="flex flex-col items-end">
                    <div class="flex items-center gap-1 text-[11px] font-black tracking-tight text-slate-800">
                        <span class="text-red-600">RedMaple</span>
                        <span class="text-slate-600 font-bold">Shield</span>
                        <i class="ph-fill ph-shield-check text-emerald-600 text-xs"></i>
                    </div>
                    <div class="text-[9px] text-slate-400 font-medium">
                        Privacy &bull; Bot Protected
                    </div>
                </div>
            </div>
        </div>

        <!-- Subtle Progress Line during verification -->
        <div x-show="state === 'verifying'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-amber-100 overflow-hidden">
            <div class="h-full bg-gradient-to-r from-amber-400 to-red-500 animate-indeterminate"></div>
        </div>
    </div>
</div>

<style>
@keyframes indeterminate {
    0% { transform: translateX(-100%); width: 40%; }
    50% { transform: translateX(50%); width: 60%; }
    100% { transform: translateX(200%); width: 40%; }
}
.animate-indeterminate {
    animation: indeterminate 1.2s infinite ease-in-out;
}
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-4px); }
    40%, 80% { transform: translateX(4px); }
}
.animate-shake {
    animation: shake 0.4s ease-in-out;
}
</style>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('invoicyHumanShield', (config) => ({
        token: config.token,
        nonce: config.nonce,
        action: config.action || 'general',
        question: config.question || '',
        state: 'idle', // idle, verifying, verified, error
        solution: '',
        telemetry: '',
        errorMessage: '',
        initTime: Date.now(),
        interactionStartTime: null,

        init() {
            window.addEventListener('invoicy-check-human', (e) => {
                if (this.state !== 'verified') {
                    this.state = 'error';
                    this.errorMessage = 'Please complete the human check before proceeding.';
                    if (e.detail && typeof e.detail.callback === 'function') {
                        e.detail.callback(false);
                    }
                } else {
                    if (e.detail && typeof e.detail.callback === 'function') {
                        e.detail.callback(true);
                    }
                }
            });
        },

        handleInteraction() {
            if (this.state === 'verifying' || this.state === 'verified') return;
            this.verify();
        },

        async verify() {
            this.state = 'verifying';
            this.errorMessage = '';
            this.interactionStartTime = Date.now();

            try {
                // Minimum human inspection delay (700ms) for smooth UX animation
                await new Promise(resolve => setTimeout(resolve, 700));

                // Calculate arithmetic challenge solution
                let ans = 0;
                if (this.question && this.question.includes('+')) {
                    const parts = this.question.split('+').map(p => parseInt(p.trim(), 10));
                    ans = parts[0] + parts[1];
                }

                this.solution = String(ans);

                // Collect telemetry
                const duration = Date.now() - this.interactionStartTime;
                this.telemetry = JSON.stringify({
                    duration: duration,
                    screen: `${window.screen.width}x${window.screen.height}`,
                    touch: ('ontouchstart' in window) || (navigator.maxTouchPoints > 0),
                    tz: Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC',
                    ts: Date.now()
                });

                this.state = 'verified';

                // Dispatch global event so the parent form can enable submit button
                this.$dispatch('human-verified', {
                    action: this.action,
                    verified: true
                });

            } catch (err) {
                console.error('Human Shield verification error:', err);
                this.state = 'error';
                this.errorMessage = 'Browser security check could not complete. Click to retry.';
            }
        }
    }));
});
</script>
