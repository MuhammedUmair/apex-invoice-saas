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
         difficulty: {{ $challenge['difficulty'] }},
         action: '{{ $action }}',
         question: '{{ $challenge['question'] }}'
     })"
     class="w-full my-3 select-none"
     x-cloak>

    <!-- Hidden verification inputs passed to form -->
    <input type="hidden" name="human_challenge_token" :value="token">
    <input type="hidden" name="human_solution" :value="solution">
    <input type="hidden" name="human_solution_hash" :value="solutionHash">
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
                        <span x-show="state === 'verifying'">Computing browser challenge proof...</span>
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
        difficulty: config.difficulty || 2,
        action: config.action || 'general',
        question: config.question || '',
        state: 'idle', // idle, verifying, verified, error
        solution: '',
        solutionHash: '',
        telemetry: '',
        errorMessage: '',
        initTime: Date.now(),
        interactionStartTime: null,

        init() {
            // Check if human verification was already solved in this session
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
                // Minimum human inspection delay (700ms) for smooth UX
                const delayPromise = new Promise(resolve => setTimeout(resolve, 750));

                // Solve Proof of Work
                const powPromise = this.solvePoW(this.nonce, this.difficulty);
                const [_, powResult] = await Promise.all([delayPromise, powPromise]);

                this.solution = String(powResult.counter);
                this.solutionHash = powResult.hash;

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
        },

        async solvePoW(nonce, difficulty) {
            const requiredPrefix = '0'.repeat(difficulty);
            let counter = 0;

            // Fast SHA-256 loop
            while (true) {
                const message = `${nonce}:${counter}`;
                const hash = this.sha256(message);
                if (hash.startsWith(requiredPrefix)) {
                    return { counter, hash };
                }
                counter++;
                // Safety bound to avoid blocking event loop
                if (counter > 100000) {
                    throw new Error('PoW complexity limit reached');
                }
            }
        },

        sha256(ascii) {
            function rightRotate(value, amount) {
                return (value >>> amount) | (value << (32 - amount));
            }
            const mathPow = Math.pow;
            const maxWord = mathPow(2, 32);
            let i, j;
            let result = '';
            const words = [];
            const asciiBitLength = ascii.length * 8;
            
            const hash = [];
            const k = [];
            let primeCounter = 0;
            const isComposite = {};
            for (let candidate = 2; primeCounter < 64; candidate++) {
                if (!isComposite[candidate]) {
                    for (i = 0; i < 300; i += candidate) {
                        isComposite[i] = candidate;
                    }
                    hash[primeCounter] = (mathPow(candidate, 0.5) * maxWord) | 0;
                    k[primeCounter++] = (mathPow(candidate, 1 / 3) * maxWord) | 0;
                }
            }

            ascii += '\x80';
            while (ascii.length % 64 - 56) ascii += '\x00';
            for (i = 0; i < ascii.length; i++) {
                j = ascii.charCodeAt(i);
                if (j >> 8) return '';
                words[i >> 2] |= j << ((3 - i) % 4) * 8;
            }
            words[words.length] = (asciiBitLength / maxWord) | 0;
            words[words.length] = asciiBitLength;

            for (j = 0; j < words.length;) {
                const w = words.slice(j, j += 16);
                const oldHash = hash.slice(0);
                for (i = 0; i < 64; i++) {
                    const w15 = w[i - 15], w2 = w[i - 2];
                    const a = hash[0], e = hash[4];
                    const temp1 = hash[7]
                        + (rightRotate(e, 6) ^ rightRotate(e, 11) ^ rightRotate(e, 25))
                        + ((e & hash[5]) ^ ((~e) & hash[6]))
                        + k[i]
                        + (w[i] = (i < 16) ? w[i] : (
                            w[i - 16]
                            + (rightRotate(w15, 7) ^ rightRotate(w15, 18) ^ (w15 >>> 3))
                            + w[i - 7]
                            + (rightRotate(w2, 17) ^ rightRotate(w2, 19) ^ (w2 >>> 10))
                        ) | 0);
                    const temp2 = (rightRotate(a, 2) ^ rightRotate(a, 13) ^ rightRotate(a, 22))
                        + ((a & hash[1]) ^ (a & hash[2]) ^ (hash[1] & hash[2]));

                    hash.pop();
                    hash.unshift((temp1 + temp2) | 0);
                    hash[4] = (hash[4] + temp1) | 0;
                }
                for (i = 0; i < 8; i++) {
                    hash[i] = (hash[i] + oldHash[i]) | 0;
                }
            }

            for (i = 0; i < 8; i++) {
                for (j = 3; j + 1; j--) {
                    const b = (hash[i] >>> (j * 8)) & 255;
                    result += ((b < 16) ? 0 : '') + b.toString(16);
                }
            }
            return result;
        }
    }));
});
</script>
