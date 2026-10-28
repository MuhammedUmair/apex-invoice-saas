<div class="max-w-5xl mx-auto space-y-8">
    <!-- Header -->
    <div class="text-center max-w-xl mx-auto">
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Membership Packages & Upgrades</h2>
        <p class="text-sm text-slate-500 mt-2">Scale your invoicing capabilities, enable whitelabel branding, and unlock unlimited clients.</p>
        
        @if($currentPlan)
            <div class="mt-4 inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Current Plan: <strong class="text-slate-900">{{ $currentPlan->name }}</strong></span>
                @if($currentSub && $currentSub->current_period_ends_at)
                    <span class="text-slate-400">&bull; Valid until {{ $currentSub->current_period_ends_at->format('M d, Y') }}</span>
                @endif
            </div>
        @endif
    </div>

    @if(session('upgrade_requested') || $requestSubmitted)
        <!-- Upgrade Request Submitted Confirmation Card -->
        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 border-2 border-emerald-200 rounded-3xl p-8 max-w-2xl mx-auto shadow-sm text-center space-y-5">
            <div class="w-16 h-16 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-3xl mx-auto shadow-md">
                <i class="ph ph-check-circle font-bold"></i>
            </div>
            <div>
                <h3 class="text-xl font-black text-emerald-950">Upgrade Request Submitted!</h3>
                <p class="text-sm text-emerald-800 mt-1 max-w-md mx-auto">
                    Your request to upgrade to <strong class="text-emerald-950">{{ $submittedPlanName ?? ($selectedPlan->name ?? 'selected plan') }}</strong> has been dispatched to our billing department.
                </p>
            </div>
            <div class="bg-white/80 backdrop-blur rounded-2xl p-5 border border-emerald-100 text-left text-xs text-slate-600 space-y-2">
                <div class="flex items-center gap-2 font-bold text-slate-800 text-sm pb-1 border-b border-emerald-50">
                    <i class="ph ph-info text-emerald-600 text-base"></i> What happens next?
                </div>
                <p>1. Our accounts team will verify your wire transfer or issue a commercial invoice to your registered email.</p>
                <p>2. Once payment is confirmed, your administrator will activate your package.</p>
                <p>3. You will receive an automated email confirmation once your account upgrade is live.</p>
            </div>
            <div class="pt-2">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow transition">
                    <i class="ph ph-arrow-left"></i> Return to Dashboard
                </a>
            </div>
        </div>
    @else
        <!-- Pricing Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($plans as $plan)
                @php 
                    $isSelected = ($selectedPlanId === $plan->id); 
                    $isCurrent = ($currentPlan && $currentPlan->id === $plan->id);
                @endphp
                <div wire:click="selectPlan('{{ $plan->id }}')"
                     class="relative cursor-pointer rounded-2xl border-2 p-6 transition flex flex-col justify-between bg-white shadow-sm hover:shadow-md
                     {{ $isSelected ? 'border-red-600 ring-2 ring-red-600/20 shadow-red-100' : 'border-slate-200' }}">
                    
                    @if($plan->code === 'pro')
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-red-600 text-white text-[10px] uppercase font-black px-3 py-0.5 rounded-full shadow-sm">
                            Most Popular
                        </span>
                    @endif

                    @if($isCurrent)
                        <span class="absolute top-3 right-3 bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-md border border-emerald-200">
                            Active Plan
                        </span>
                    @endif

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold text-slate-900 text-lg">{{ $plan->name }}</h3>
                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center {{ $isSelected ? 'border-red-600 bg-red-600 text-white' : 'border-slate-300' }}">
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

                    <button type="button" class="w-full py-2.5 rounded-xl font-bold text-xs transition {{ $isSelected ? 'bg-red-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        {{ $isCurrent ? 'Current Plan' : ($isSelected ? 'Selected Package' : 'Select Plan') }}
                    </button>
                </div>
            @endforeach
        </div>

        <!-- Checkout Box with Live Coupon Engine & Manual Wire Instructions -->
        @if($selectedPlan)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-md p-8 max-w-2xl mx-auto space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Upgrade Order Summary</h3>
                        <p class="text-xs text-slate-500">Selected: <span class="font-semibold text-slate-800">{{ $selectedPlan->name }}</span></p>
                    </div>
                    <div class="text-right">
                        @if($discountCents > 0)
                            <span class="text-xs text-slate-400 line-through block font-medium">${{ number_format($selectedPlan->price_cents / 100, 2) }}</span>
                        @endif
                        <span class="text-xl font-black text-red-600">{{ $finalPriceFormatted }} <span class="text-xs font-normal text-slate-400">/ mo</span></span>
                    </div>
                </div>

                <!-- Coupon Code Section -->
                <div class="space-y-2">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Have a Promotional Coupon?</label>
                    <div class="flex gap-2">
                        <input type="text" wire:model.live="couponCode" placeholder="e.g. LAUNCH2026 or PROMO50"
                               class="flex-1 px-3 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 uppercase font-mono">
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

                <!-- Payment Method & Wire Transfer Details (Option B) -->
                <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0 border border-blue-100">
                            <i class="ph ph-bank"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Offline Wire Transfer & Commercial Invoicing</h4>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Plan upgrades are verified through manual bank transfer or invoice settlement. Submitting this request will alert our finance administration to activate your plan upon payment receipt.
                            </p>
                        </div>
                    </div>

                    <!-- Payment Reference / Notes Input -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Payment Reference or Wire Notes (Optional)</label>
                        <input type="text" wire:model="paymentNotes" placeholder="e.g. Wire transfer ref #12345, or PO #98765"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold bg-white focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="button" wire:click="processCheckout" 
                            class="w-full py-3.5 bg-red-600 hover:bg-red-700 text-white font-bold text-sm rounded-xl shadow-md hover:shadow-lg transition flex items-center justify-center gap-2">
                        @if($discountCents >= $selectedPlan->price_cents)
                            <i class="ph ph-ticket text-base"></i>
                            Activate Free via Promo Coupon ($0.00)
                        @else
                            <i class="ph ph-paper-plane-tilt text-base"></i>
                            Submit Upgrade Request to {{ $selectedPlan->name }} ({{ $finalPriceFormatted }}/mo)
                        @endif
                    </button>

                    <p class="text-center text-[11px] text-slate-400 mt-2">
                        No immediate card charge is made. Our billing administration will review your request and confirm payment before plan activation.
                    </p>
                </div>
            </div>
        @endif
    @endif
</div>
