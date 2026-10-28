@extends('layouts.app')

@section('title', __('Coupons & Promotional Offers') . ' - RedMaple')

@section('content')
<div class="space-y-6" x-data="{ showCreateModal: false, offerType: 'discount_percent' }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <i class="ph ph-ticket text-fuchsia-600 text-2xl"></i>
                {{ __('Coupons & Promotional Offers') }}
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                {{ __('Design promotional codes, discount campaigns, and complimentary service offers for user signup and customer invoices.') }}
            </p>
        </div>
        <button type="button" @click="showCreateModal = true" class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs shadow-sm hover:shadow transition flex items-center gap-2 shrink-0">
            <i class="ph ph-plus-circle text-base"></i>
            {{ __('Design New Promotion') }}
        </button>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2.5 shadow-2xs">
            <i class="ph ph-check-circle text-lg text-emerald-600 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1 shadow-2xs">
            <div class="flex items-center gap-2 font-bold">
                <i class="ph ph-warning-circle text-lg text-rose-600 shrink-0"></i>
                <span>{{ __('Please correct the following errors:') }}</span>
            </div>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Total Coupons') }}</span>
                <div class="w-8 h-8 rounded-lg bg-fuchsia-50 text-fuchsia-600 flex items-center justify-center">
                    <i class="ph ph-ticket text-lg"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ $stats['total_coupons'] }}</div>
            <span class="text-[11px] text-slate-400 mt-0.5 block">{{ __('Designed promotions') }}</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Active Campaigns') }}</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="ph ph-broadcast text-lg"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-emerald-600 mt-2">{{ $stats['active_coupons'] }}</div>
            <span class="text-[11px] text-slate-400 mt-0.5 block">{{ __('Currently redeemable') }}</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Total Redemptions') }}</span>
                <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center">
                    <i class="ph ph-users-three text-lg"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ $stats['total_redemptions'] }}</div>
            <span class="text-[11px] text-slate-400 mt-0.5 block">{{ __('Claimed by customers') }}</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Discounts Value') }}</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="ph ph-currency-dollar text-lg"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 mt-2">
                ${{ number_format($stats['total_discount_given_cents'] / 100, 2) }}
            </div>
            <span class="text-[11px] text-slate-400 mt-0.5 block">{{ __('Total promotional benefits') }}</span>
        </div>
    </div>

    <!-- Coupons Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                <i class="ph ph-tag text-slate-600"></i>
                {{ __('All Promotional Offers & Codes') }}
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3">{{ __('CODE & NAME') }}</th>
                        <th class="px-3 py-3">{{ __('OFFER BENEFIT') }}</th>
                        <th class="px-3 py-3 text-center">{{ __('TARGET AUDIENCE') }}</th>
                        <th class="px-3 py-3 text-center">{{ __('USAGE / LIMIT') }}</th>
                        <th class="px-3 py-3">{{ __('EXPIRY') }}</th>
                        <th class="px-3 py-3 text-center">{{ __('STATUS') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('ACTIONS') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($coupons as $coupon)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-4 py-3.5 align-middle">
                                <div class="flex items-center gap-2.5">
                                    <span class="px-2.5 py-1 rounded-lg bg-fuchsia-50 border border-fuchsia-200 text-fuchsia-800 font-mono font-bold text-xs">
                                        {{ $coupon->code }}
                                    </span>
                                    <div>
                                        <p class="font-bold text-slate-900">{{ $coupon->name ?: 'General Promotion' }}</p>
                                        @if($coupon->description)
                                            <p class="text-[11px] text-slate-500 line-clamp-1">{{ $coupon->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3.5 align-middle">
                                @if($coupon->offer_type === 'service' || !empty($coupon->service_name))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        <i class="ph ph-gift text-sm text-amber-600"></i> Free Service: {{ $coupon->service_name }}
                                    </span>
                                @elseif($coupon->discount_type === 'percent')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        <i class="ph ph-percent text-sm text-emerald-600"></i> {{ number_format($coupon->discount_value, 0) }}% Off
                                    </span>
                                @elseif($coupon->discount_type === 'trial_days' || $coupon->offer_type === 'trial')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-800 border border-sky-200">
                                        <i class="ph ph-clock text-sm text-sky-600"></i> {{ number_format($coupon->discount_value, 0) }} Days Extended Trial
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        <i class="ph ph-currency-dollar text-sm text-emerald-600"></i> ${{ number_format($coupon->discount_value, 2) }} Flat Discount
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-3.5 text-center align-middle">
                                @if($coupon->target === 'signup')
                                    <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 font-semibold text-[10px] border border-purple-200">
                                        {{ __('Signup Only') }}
                                    </span>
                                @elseif($coupon->target === 'invoice')
                                    <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-semibold text-[10px] border border-blue-200">
                                        {{ __('Invoices Only') }}
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-semibold text-[10px] border border-slate-200">
                                        {{ __('All (Signup & Invoices)') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-3.5 text-center align-middle font-semibold text-slate-700">
                                {{ $coupon->times_redeemed }} / {{ $coupon->max_redemptions ?: '∞' }}
                            </td>
                            <td class="px-3 py-3.5 align-middle text-slate-600">
                                {{ $coupon->expires_at ? $coupon->expires_at->format('M d, Y') : __('Never') }}
                            </td>
                            <td class="px-3 py-3.5 text-center align-middle">
                                <form method="POST" action="{{ route('admin.coupons.toggle', $coupon->id) }}">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded-full text-[11px] font-bold transition {{ $coupon->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                        {{ $coupon->is_active ? __('Active') : __('Inactive') }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-3.5 text-right align-middle">
                                <form method="POST" action="{{ route('admin.coupons.destroy', $coupon->id) }}" onsubmit="return confirm('Delete this coupon code?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-rose-600 transition p-1" title="Delete Coupon">
                                        <i class="ph ph-trash text-base"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                                <i class="ph ph-ticket text-3xl mb-1 block"></i>
                                {{ __('No promotional coupon codes created yet.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($coupons->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $coupons->links() }}
            </div>
        @endif
    </div>

    <!-- Recent Redemptions Log -->
    @if($recentRedemptions->count() > 0)
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-3">
            <h4 class="font-bold text-slate-900 text-xs flex items-center gap-2">
                <i class="ph ph-clock-counter-clockwise text-slate-600"></i>
                {{ __('Recent Redemptions Log') }}
            </h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="text-[10px] text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-1">
                        <tr>
                            <th class="py-2">{{ __('COUPON') }}</th>
                            <th class="py-2">{{ __('WORKSPACE / TENANT') }}</th>
                            <th class="py-2">{{ __('BENEFIT APPLIED') }}</th>
                            <th class="py-2 text-right">{{ __('DATE') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($recentRedemptions as $redemption)
                            <tr>
                                <td class="py-2.5 font-bold font-mono text-fuchsia-700">{{ $redemption->coupon?->code ?: 'N/A' }}</td>
                                <td class="py-2.5 text-slate-800">{{ $redemption->workspace?->name ?: 'N/A' }}</td>
                                <td class="py-2.5 text-slate-600">
                                    @if($redemption->service_granted)
                                        <span class="text-amber-700 font-semibold">{{ $redemption->service_granted }}</span>
                                    @else
                                        ${{ number_format($redemption->discount_applied_cents / 100, 2) }}
                                    @endif
                                </td>
                                <td class="py-2.5 text-right text-slate-400">{{ $redemption->redeemed_at ? $redemption->redeemed_at->diffForHumans() : 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Create Coupon Modal -->
    <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showCreateModal = false" class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-xl w-full p-6 space-y-5 animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <i class="ph ph-ticket text-fuchsia-600 text-lg"></i>
                    {{ __('Design New Promotional Offer / Code') }}
                </h3>
                <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.coupons.store') }}" class="space-y-4">
                @csrf

                <!-- Code & Name -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Coupon Code *') }}</label>
                        <input type="text" name="code" required placeholder="e.g. WELCOME50, FREESERVICE"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono uppercase font-bold focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Campaign Title / Name') }}</label>
                        <input type="text" name="name" placeholder="e.g. Summer Agency Launch"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <!-- Offer Category Radio Selection -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-2">{{ __('Offer Type & Benefit *') }}</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <label class="border rounded-xl p-2.5 text-center cursor-pointer transition"
                               :class="offerType === 'discount_percent' ? 'border-fuchsia-500 bg-fuchsia-50/50 text-fuchsia-900 font-bold' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                            <input type="radio" name="offer_type" value="discount_percent" x-model="offerType" class="sr-only">
                            <i class="ph ph-percent text-base block mb-0.5"></i>
                            <span class="text-[11px] block">{{ __('% Discount') }}</span>
                        </label>
                        <label class="border rounded-xl p-2.5 text-center cursor-pointer transition"
                               :class="offerType === 'discount_fixed' ? 'border-fuchsia-500 bg-fuchsia-50/50 text-fuchsia-900 font-bold' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                            <input type="radio" name="offer_type" value="discount_fixed" x-model="offerType" class="sr-only">
                            <i class="ph ph-currency-dollar text-base block mb-0.5"></i>
                            <span class="text-[11px] block">{{ __('Fixed ($)') }}</span>
                        </label>
                        <label class="border rounded-xl p-2.5 text-center cursor-pointer transition"
                               :class="offerType === 'service' ? 'border-fuchsia-500 bg-fuchsia-50/50 text-fuchsia-900 font-bold' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                            <input type="radio" name="offer_type" value="service" x-model="offerType" class="sr-only">
                            <i class="ph ph-gift text-base block mb-0.5"></i>
                            <span class="text-[11px] block">{{ __('Free Service') }}</span>
                        </label>
                        <label class="border rounded-xl p-2.5 text-center cursor-pointer transition"
                               :class="offerType === 'trial_days' ? 'border-fuchsia-500 bg-fuchsia-50/50 text-fuchsia-900 font-bold' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                            <input type="radio" name="offer_type" value="trial_days" x-model="offerType" class="sr-only">
                            <i class="ph ph-clock text-base block mb-0.5"></i>
                            <span class="text-[11px] block">{{ __('Extra Trial') }}</span>
                        </label>
                    </div>
                </div>

                <!-- Dynamic Offer Inputs based on Selection -->
                <div x-show="offerType === 'discount_percent' || offerType === 'discount_fixed' || offerType === 'trial_days'">
                    <label class="block text-xs font-semibold text-slate-700 mb-1" x-text="offerType === 'discount_percent' ? 'Discount Percentage (%)' : (offerType === 'discount_fixed' ? 'Fixed Discount Amount ($ / Currency)' : 'Trial Extension Days')"></label>
                    <input type="number" step="0.01" min="0" name="discount_value" placeholder="e.g. 20"
                           class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                </div>

                <div x-show="offerType === 'service'" class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Select Catalog Service to Offer Free') }}</label>
                        <select name="service_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 bg-white">
                            <option value="">-- {{ __('Select from Active Catalog') }} --</option>
                            @foreach($services as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->category }} - ${{ number_format($s->rate_cents / 100, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Or Enter Custom Service Offer Title') }}</label>
                        <input type="text" name="custom_service_name" placeholder="e.g. Complimentary 1-Month Cloud Hosting & SSL"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <!-- Target Audience -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Applicable Target *') }}</label>
                        <select name="target" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 bg-white">
                            <option value="all">{{ __('All (Signup & Customer Invoices)') }}</option>
                            <option value="signup">{{ __('User Signup Only (Welcome Offer)') }}</option>
                            <option value="invoice">{{ __('Invoice Checkout Only (Clients)') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Max Redemptions Limit') }}</label>
                        <input type="number" min="1" name="max_redemptions" placeholder="Unlimited if blank"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <!-- Description & Expiry -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Min Spend Amount ($)') }}</label>
                        <input type="number" step="0.01" min="0" name="min_purchase" placeholder="0"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Expiry Date') }}</label>
                        <input type="date" name="expires_at"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 bg-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Campaign Description / Notes') }}</label>
                    <textarea name="description" rows="2" placeholder="Internal notes or customer-facing description..."
                              class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500">
                        <span class="text-xs font-semibold text-slate-700">{{ __('Activate Immediately') }}</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs font-bold transition">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs shadow-xs transition">
                            {{ __('Save & Publish Promotion') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
