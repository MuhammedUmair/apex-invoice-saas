<div class="max-w-5xl mx-auto space-y-10">
    <!-- Header -->
    <div class="text-center max-w-xl mx-auto">
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Membership Packages & Upgrades</h2>
        <p class="text-sm text-slate-500 mt-2">Scale your invoicing capabilities, enable whitelabel branding, and unlock unlimited clients.</p>
    </div>

    <!-- Pricing Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($plans as $plan)
            @php $isSelected = ($selectedPlanId === $plan->id); @endphp
            <div wire:click="selectPlan('{{ $plan->id }}')"
                 class="relative cursor-pointer rounded-2xl border-2 p-6 transition flex flex-col justify-between bg-white shadow-sm hover:shadow-md
                 {{ $isSelected ? 'border-blue-600 ring-2 ring-blue-600/20 shadow-blue-100' : 'border-slate-200' }}">
                @if($plan->code === 'pro')
                    <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-blue-600 text-white text-[10px] uppercase font-black px-3 py-0.5 rounded-full shadow-sm">
                        Most Popular
                    </span>
                @endif

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-bold text-slate-900 text-lg">{{ $plan->name }}</h3>
                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center {{ $isSelected ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-300' }}">
                            @if($isSelected)<i class="ph ph-check text-xs"></i>@endif
                        </div>
                    </div>

                    <div class="mt-3 mb-6">
                        <span class="text-3xl font-black text-slate-900">${{ number_format($plan->price_cents / 100, 2) }}</span>
                        <span class="text-xs text-slate-400 font-medium">/ month</span>
                    </div>

                    <!-- Features -->
                    <ul class="space-y-2.5 text-xs text-slate-600 mb-6">
                        @if(is_array($plan->features_json))
                            @foreach($plan->features_json as $feat)
                                <li class="flex items-center gap-2">
                                    <i class="ph ph-check-circle text-emerald-500 text-sm"></i>
                                    <span>{{ $feat }}</span>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>

                <button type="button" class="w-full py-2.5 rounded-xl font-bold text-xs transition {{ $isSelected ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    {{ $isSelected ? 'Selected Package' : 'Select Plan' }}
                </button>
            </div>
        @endforeach
    </div>

    <!-- Checkout Box with Live Coupon Engine & Gateway Selection -->
    @if($selectedPlan)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-md p-8 max-w-2xl mx-auto space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Checkout Summary</h3>
                    <p class="text-xs text-slate-500">Plan: <span class="font-semibold text-slate-800">{{ $selectedPlan->name }}</span></p>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-400 line-through block font-medium">${{ number_format($selectedPlan->price_cents / 100, 2) }}</span>
                    <span class="text-xl font-black text-blue-600">{{ $finalPriceFormatted }}</span>
                </div>
            </div>

            <!-- Coupon Code Section -->
            <div class="space-y-2">
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Have a Promotional Coupon?</label>
                <div class="flex gap-2">
                    <input type="text" wire:model.live="couponCode" placeholder="e.g. LAUNCH2026 or HOSTINGER50"
                           class="flex-1 px-3 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 uppercase font-mono">
                    <button type="button" wire:click="validateCoupon" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-xl transition">
                        Apply Coupon
                    </button>
                </div>
                @if($couponStatus)
                    <p class="text-xs font-semibold mt-1.5 {{ $isCouponValid ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $couponStatus }}
                    </p>
                @endif
            </div>

            <!-- Dual Payment Gateway Selection -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Select Payment Method</label>
                <div class="grid grid-cols-2 gap-4">
                    <!-- Stripe -->
                    <label class="flex items-center justify-between p-3.5 rounded-xl border-2 cursor-pointer transition {{ $gateway === 'stripe' ? 'border-blue-600 bg-blue-50/40 text-blue-900' : 'border-slate-200 text-slate-700' }}">
                        <div class="flex items-center gap-2.5">
                            <input type="radio" wire:model.live="gateway" value="stripe" class="text-blue-600 focus:ring-blue-500">
                            <span class="text-xs font-bold">Credit Card (Stripe)</span>
                        </div>
                        <i class="ph ph-credit-card text-lg text-slate-600"></i>
                    </label>

                    <!-- PayPal -->
                    <label class="flex items-center justify-between p-3.5 rounded-xl border-2 cursor-pointer transition {{ $gateway === 'paypal' ? 'border-blue-600 bg-blue-50/40 text-blue-900' : 'border-slate-200 text-slate-700' }}">
                        <div class="flex items-center gap-2.5">
                            <input type="radio" wire:model.live="gateway" value="paypal" class="text-blue-600 focus:ring-blue-500">
                            <span class="text-xs font-bold">PayPal Subscription</span>
                        </div>
                        <i class="ph ph-wallet text-lg text-slate-600"></i>
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="button" wire:click="processCheckout" class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-md hover:shadow-lg transition flex items-center justify-center gap-2">
                <i class="ph ph-lock-key text-base"></i>
                Upgrade to {{ $selectedPlan->name }} ({{ $finalPriceFormatted }}/mo)
            </button>

            <p class="text-center text-[11px] text-slate-400">
                Cancel anytime from your account settings. 256-bit SSL encrypted checkout.
            </p>
        </div>
    @endif
</div>
