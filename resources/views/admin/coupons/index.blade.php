@extends('layouts.admin', ['title' => 'Coupons & Promotional Offers', 'header' => 'Coupons & Discounts Manager'])

@section('title', __('Coupons & Promotional Offers') . ' - RedMaple')

@section('content')
<div class="space-y-6" x-data="{
    showCreateModal: false,
    showEditModal: false,
    offerType: 'discount_percent',
    editOfferType: 'discount_percent',
    selectedCoupons: [],
    allCouponIds: {{ json_encode($coupons->pluck('id')) }},
    editingCoupon: {
        id: '',
        code: '',
        name: '',
        description: '',
        offer_type: 'discount_percent',
        discount_value: '',
        service_id: '',
        custom_service_name: '',
        target: 'all',
        min_purchase: '',
        max_redemptions: '',
        expires_at: '',
        is_active: true
    },
    editCoupon(c) {
        let type = 'discount_percent';
        if (c.offer_type === 'service') {
            type = 'service';
        } else if (c.offer_type === 'trial' || c.discount_type === 'trial_days') {
            type = 'trial_days';
        } else if (c.discount_type === 'fixed') {
            type = 'discount_fixed';
        }
        this.editOfferType = type;
        this.editingCoupon = {
            id: c.id,
            code: c.code,
            name: c.name || '',
            description: c.description || '',
            offer_type: type,
            discount_value: c.discount_value || '',
            service_id: c.service_id || '',
            custom_service_name: c.service_name || '',
            target: c.target || 'all',
            min_purchase: c.min_purchase_cents ? (c.min_purchase_cents / 100) : '',
            max_redemptions: c.max_redemptions || '',
            expires_at: c.expires_at ? c.expires_at.substring(0, 10) : '',
            is_active: !!c.is_active
        };
        this.showEditModal = true;
    },
    toggleAllCoupons() {
        if (this.selectedCoupons.length === this.allCouponIds.length) {
            this.selectedCoupons = [];
        } else {
            this.selectedCoupons = [...this.allCouponIds];
        }
    },
    submitCouponBulk(action, confirmMsg) {
        if (this.selectedCoupons.length === 0) return;
        if (confirmMsg && !confirm(confirmMsg)) return;
        $refs.couponBulkActionInput.value = action;
        $refs.couponBulkForm.submit();
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-2.5">
                <i class="ph ph-ticket text-fuchsia-500 text-2xl"></i>
                {{ __('Coupons & Promotional Offers') }}
            </h1>
            <p class="text-xs text-slate-400 mt-1">
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
        <div class="p-4 rounded-xl bg-emerald-950/50 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-2.5 shadow-2xs">
            <i class="ph ph-check-circle text-lg text-emerald-400 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-950/50 border border-rose-500/30 text-rose-300 text-xs space-y-1 shadow-2xs">
            <div class="flex items-center gap-2 font-bold">
                <i class="ph ph-warning-circle text-lg text-rose-400 shrink-0"></i>
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
        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Total Coupons') }}</span>
                <div class="w-8 h-8 rounded-lg bg-fuchsia-500/10 text-fuchsia-400 flex items-center justify-center">
                    <i class="ph ph-ticket text-lg"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-white mt-2">{{ $stats['total_coupons'] }}</div>
            <span class="text-[11px] text-slate-500 mt-0.5 block">{{ __('Designed promotions') }}</span>
        </div>

        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Active Campaigns') }}</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                    <i class="ph ph-broadcast text-lg"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-emerald-400 mt-2">{{ $stats['active_coupons'] }}</div>
            <span class="text-[11px] text-slate-500 mt-0.5 block">{{ __('Currently redeemable') }}</span>
        </div>

        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Total Redemptions') }}</span>
                <div class="w-8 h-8 rounded-lg bg-sky-500/10 text-sky-400 flex items-center justify-center">
                    <i class="ph ph-users-three text-lg"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-white mt-2">{{ $stats['total_redemptions'] }}</div>
            <span class="text-[11px] text-slate-500 mt-0.5 block">{{ __('Claimed by customers') }}</span>
        </div>

        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Discounts Value') }}</span>
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                    <i class="ph ph-currency-dollar text-lg"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-white mt-2">
                ${{ number_format($stats['total_discount_given_cents'] / 100, 2) }}
            </div>
            <span class="text-[11px] text-slate-500 mt-0.5 block">{{ __('Total promotional benefits') }}</span>
        </div>
    </div>

    <!-- Bulk Actions Container for Coupons -->
    <div class="space-y-4">
        <!-- Bulk Action Floating Toolbar (Shows when 1+ selected) -->
        <div x-show="selectedCoupons.length > 0" x-cloak class="p-3.5 bg-slate-900 text-white rounded-2xl shadow-lg flex flex-wrap items-center justify-between gap-3 border border-slate-700 transition">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-fuchsia-600 text-white text-xs font-bold" x-text="selectedCoupons.length"></span>
                <span class="text-xs font-semibold">{{ __('Coupon(s) Selected') }}</span>
                <button type="button" @click="selectedCoupons = []" class="text-[11px] text-slate-400 hover:text-white underline ml-1">
                    {{ __('Clear selection') }}
                </button>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" @click="submitCouponBulk('activate', 'Activate ' + selectedCoupons.length + ' selected promotional coupon(s)?')" 
                        class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-2xs">
                    <i class="ph ph-check-circle text-sm"></i> {{ __('Activate Offers') }}
                </button>
                <button type="button" @click="submitCouponBulk('deactivate', 'Pause / Deactivate ' + selectedCoupons.length + ' selected promotional coupon(s)?')" 
                        class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-2xs">
                    <i class="ph ph-pause text-sm"></i> {{ __('Pause / Deactivate') }}
                </button>
                <button type="button" @click="submitCouponBulk('delete', 'Permanently delete ' + selectedCoupons.length + ' selected coupon code(s)?')" 
                        class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-2xs">
                    <i class="ph ph-trash text-sm"></i> {{ __('Delete Selected') }}
                </button>
            </div>
        </div>

        <form x-ref="couponBulkForm" method="POST" action="{{ route('admin.coupons.bulk') }}">
            @csrf
            <input type="hidden" name="action" x-ref="couponBulkActionInput" value="">
            <template x-for="id in selectedCoupons" :key="id">
                <input type="hidden" name="coupon_ids[]" :value="id">
            </template>
        </form>

        <!-- Coupons Table Card -->
        <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-800 flex items-center justify-between">
                <h3 class="font-bold text-white text-sm flex items-center gap-2">
                    <i class="ph ph-tag text-amber-400"></i>
                    {{ __('All Promotional Offers & Codes') }}
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-950/60 text-slate-400 text-[10px] font-bold uppercase tracking-wider border-b border-slate-800">
                        <tr>
                            <th class="px-3 py-3 w-10 text-center">
                                <input type="checkbox" @change="toggleAllCoupons()" :checked="selectedCoupons.length === allCouponIds.length && allCouponIds.length > 0" class="rounded border-slate-700 bg-slate-950 text-fuchsia-600 focus:ring-fuchsia-500 cursor-pointer">
                            </th>
                            <th class="px-4 py-3">{{ __('CODE & NAME') }}</th>
                            <th class="px-3 py-3">{{ __('OFFER BENEFIT') }}</th>
                            <th class="px-3 py-3 text-center">{{ __('TARGET AUDIENCE') }}</th>
                            <th class="px-3 py-3 text-center">{{ __('USAGE / LIMIT') }}</th>
                            <th class="px-3 py-3">{{ __('EXPIRY') }}</th>
                            <th class="px-3 py-3 text-center">{{ __('STATUS') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('ACTIONS') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 text-slate-200">
                        @forelse($coupons as $coupon)
                            <tr class="hover:bg-slate-800/50 transition" :class="selectedCoupons.includes('{{ $coupon->id }}') ? 'bg-fuchsia-950/20' : ''">
                                <td class="px-3 py-3.5 text-center align-middle">
                                    <input type="checkbox" value="{{ $coupon->id }}" x-model="selectedCoupons" class="rounded border-slate-700 bg-slate-950 text-fuchsia-600 focus:ring-fuchsia-500 cursor-pointer">
                                </td>
                                <td class="px-4 py-3.5 align-middle">
                                    <div class="flex items-center gap-2.5">
                                        <span class="px-2.5 py-1 rounded-lg bg-fuchsia-950 border border-fuchsia-700/50 text-fuchsia-300 font-mono font-bold text-xs">
                                            {{ $coupon->code }}
                                        </span>
                                        <div>
                                            <p class="font-bold text-white">{{ $coupon->name ?: 'General Promotion' }}</p>
                                            @if($coupon->description)
                                                <p class="text-[11px] text-slate-400 line-clamp-1">{{ $coupon->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3.5 align-middle">
                                    @if($coupon->offer_type === 'service' || !empty($coupon->service_name))
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-950 border border-amber-700/50 text-amber-300">
                                            <i class="ph ph-gift text-sm text-amber-400"></i> Free Service: {{ $coupon->service_name }}
                                        </span>
                                    @elseif($coupon->discount_type === 'percent')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-950 border border-emerald-700/50 text-emerald-300">
                                            <i class="ph ph-percent text-sm text-emerald-400"></i> {{ number_format($coupon->discount_value, 0) }}% Off
                                        </span>
                                    @elseif($coupon->discount_type === 'trial_days' || $coupon->offer_type === 'trial')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-sky-950 border border-sky-700/50 text-sky-300">
                                            <i class="ph ph-clock text-sm text-sky-400"></i> {{ number_format($coupon->discount_value, 0) }} Days Extended Trial
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-950 border border-emerald-700/50 text-emerald-300">
                                            <i class="ph ph-currency-dollar text-sm text-emerald-400"></i> ${{ number_format($coupon->discount_value, 2) }} Flat Discount
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5 text-center align-middle">
                                    @if($coupon->target === 'signup')
                                        <span class="px-2 py-0.5 rounded-md bg-purple-950 text-purple-300 font-semibold text-[10px] border border-purple-700/50">
                                            {{ __('Signup Only') }}
                                        </span>
                                    @elseif($coupon->target === 'invoice')
                                        <span class="px-2 py-0.5 rounded-md bg-blue-950 text-blue-300 font-semibold text-[10px] border border-blue-700/50">
                                            {{ __('Invoices Only') }}
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-md bg-slate-800 text-slate-300 font-semibold text-[10px] border border-slate-700">
                                            {{ __('All (Signup & Invoices)') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5 text-center align-middle font-semibold text-slate-300">
                                    {{ $coupon->times_redeemed }} / {{ $coupon->max_redemptions ?: '∞' }}
                                </td>
                                <td class="px-3 py-3.5 align-middle text-slate-400">
                                    @if($coupon->expires_at)
                                        @if($coupon->expires_at->isPast())
                                            <span class="inline-flex items-center gap-1 text-rose-400 font-bold text-xs" title="Expired at {{ $coupon->expires_at->format('M d, Y h:i A') }}">
                                                <i class="ph ph-warning-circle"></i> {{ __('Expired') }} ({{ $coupon->expires_at->diffForHumans() }})
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-emerald-400 font-medium text-xs" title="Expires at {{ $coupon->expires_at->format('M d, Y h:i A') }}">
                                                <i class="ph ph-clock"></i> {{ $coupon->expires_at->diffForHumans() }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-slate-500 font-medium">{{ __('Never') }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5 text-center align-middle">
                                    <form method="POST" action="{{ route('admin.coupons.toggle', $coupon->id) }}">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-full text-[11px] font-bold transition {{ $coupon->is_active ? 'bg-emerald-900/60 text-emerald-300 border border-emerald-600 hover:bg-emerald-800' : 'bg-slate-800 text-slate-400 border border-slate-700 hover:bg-slate-700' }}">
                                            {{ $coupon->is_active ? __('Active') : __('Inactive') }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-3.5 text-right align-middle">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Share Promo Button (Request 14) -->
                                        <button type="button" 
                                                onclick="const msg = '🎉 Use promo code {{ $coupon->code }} on Invoicy to get {{ $coupon->discount_type === 'percent' ? $coupon->discount_value . '% OFF' : ($coupon->offer_type === 'service' ? 'free ' . $coupon->service_name : '$' . $coupon->discount_value . ' discount') }}! {{ $coupon->expires_at ? '⏰ Valid until: ' . $coupon->expires_at->format('M d, Y h:i A') . ' (' . $coupon->expires_at->diffForHumans() . ').' : '' }} Link: {{ url('/register') }}'; navigator.clipboard.writeText(msg); alert('Promo share message with decided duration copied to clipboard!');" 
                                                class="p-1.5 text-slate-400 hover:text-sky-400 rounded-lg hover:bg-slate-800 transition" 
                                                title="Share Promo Code with Expiry Duration">
                                            <i class="ph ph-share-network text-base"></i>
                                        </button>
                                        <button type="button" @click="editCoupon({{ json_encode($coupon) }})" class="p-1.5 text-slate-400 hover:text-amber-400 rounded-lg hover:bg-slate-800 transition" title="Edit Coupon">
                                            <i class="ph ph-pencil-simple text-base"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.coupons.destroy', $coupon->id) }}" onsubmit="return confirm('Delete this coupon code?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-400 rounded-lg hover:bg-slate-800 transition" title="Delete Coupon">
                                                <i class="ph ph-trash text-base"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                                    <i class="ph ph-ticket text-3xl mb-1 block"></i>
                                    {{ __('No promotional coupon codes created yet.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($coupons->hasPages())
                <div class="p-4 border-t border-slate-800">
                    {{ $coupons->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Redemptions Log -->
    @if($recentRedemptions->count() > 0)
        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-xs space-y-3">
            <h4 class="font-bold text-white text-xs flex items-center gap-2">
                <i class="ph ph-clock-counter-clockwise text-amber-400"></i>
                {{ __('Recent Redemptions Log') }}
            </h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="text-[10px] text-slate-400 uppercase tracking-wider border-b border-slate-800 pb-1">
                        <tr>
                            <th class="py-2">{{ __('COUPON') }}</th>
                            <th class="py-2">{{ __('WORKSPACE / TENANT') }}</th>
                            <th class="py-2">{{ __('BENEFIT APPLIED') }}</th>
                            <th class="py-2 text-right">{{ __('DATE') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 text-slate-300">
                        @foreach($recentRedemptions as $redemption)
                            <tr>
                                <td class="py-2.5 font-bold font-mono text-fuchsia-400">{{ $redemption->coupon?->code ?: 'N/A' }}</td>
                                <td class="py-2.5 text-white">{{ $redemption->workspace?->name ?: 'N/A' }}</td>
                                <td class="py-2.5 text-slate-300">
                                    @if($redemption->service_granted)
                                        <span class="text-amber-400 font-semibold">{{ $redemption->service_granted }}</span>
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
    <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showCreateModal = false" class="bg-white text-slate-900 rounded-2xl border border-slate-200 shadow-2xl max-w-xl w-full p-6 space-y-5 animate-in fade-in zoom-in-95 duration-150">
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
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono uppercase font-bold text-slate-900 bg-white placeholder-slate-400 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Campaign Title / Name') }}</label>
                        <input type="text" name="name" placeholder="e.g. Summer Agency Launch"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 bg-white placeholder-slate-400 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
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
                           class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 bg-white placeholder-slate-400 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                </div>

                <div x-show="offerType === 'service'" class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Select Catalog Service to Offer Free') }}</label>
                        <select name="service_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                            <option value="">-- {{ __('Select from Active Catalog') }} --</option>
                            @foreach($services as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->category }} - ${{ number_format($s->rate_cents / 100, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Or Enter Custom Service Offer Title') }}</label>
                        <input type="text" name="custom_service_name" placeholder="e.g. Complimentary 1-Month Cloud Hosting & SSL"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 bg-white placeholder-slate-400 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                    </div>
                </div>

                <!-- Target Audience -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Applicable Target *') }}</label>
                        <select name="target" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                            <option value="all">{{ __('All (Signup & Customer Invoices)') }}</option>
                            <option value="signup">{{ __('User Signup Only (Welcome Offer)') }}</option>
                            <option value="invoice">{{ __('Invoice Checkout Only (Clients)') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Max Redemptions Limit') }}</label>
                        <input type="number" min="1" name="max_redemptions" placeholder="Unlimited if blank"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 bg-white placeholder-slate-400 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                    </div>
                </div>

                <!-- Description, Decided Time Duration & Expiry (Request 14) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3" x-data="{ durationType: 'custom' }">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Min Spend ($)') }}</label>
                        <input type="number" step="0.01" min="0" name="min_purchase" placeholder="0"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 bg-white placeholder-slate-400 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Decided Time Duration') }}</label>
                        <select name="duration_preset" x-model="durationType"
                                class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                            <option value="custom">{{ __('Custom Date & Time') }}</option>
                            <option value="never">{{ __('No Expiry (Never)') }}</option>
                            <option value="1_hour">{{ __('1 Hour (Flash)') }}</option>
                            <option value="6_hours">{{ __('6 Hours') }}</option>
                            <option value="12_hours">{{ __('12 Hours') }}</option>
                            <option value="24_hours">{{ __('24 Hours (1 Day)') }}</option>
                            <option value="48_hours">{{ __('48 Hours (2 Days)') }}</option>
                            <option value="3_days">{{ __('3 Days') }}</option>
                            <option value="7_days">{{ __('7 Days (1 Week)') }}</option>
                            <option value="14_days">{{ __('14 Days (2 Weeks)') }}</option>
                            <option value="30_days">{{ __('30 Days (1 Month)') }}</option>
                        </select>
                    </div>
                    <div x-show="durationType === 'custom'">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Specific Date & Time') }}</label>
                        <input type="datetime-local" name="expires_at"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Campaign Description / Notes') }}</label>
                    <textarea name="description" rows="2" placeholder="Internal notes or customer-facing description..."
                              class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 bg-white placeholder-slate-400 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none"></textarea>
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

    <!-- Edit Coupon Modal -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showEditModal = false" class="bg-white text-slate-900 rounded-2xl border border-slate-200 shadow-2xl max-w-xl w-full p-6 space-y-5 animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <i class="ph ph-pencil-simple text-amber-500 text-lg"></i>
                    {{ __('Edit Promotional Offer / Code') }}
                </h3>
                <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>

            <form :action="'{{ url('/admin/coupons') }}/' + editingCoupon.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <!-- Code & Name -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Coupon Code *') }}</label>
                        <input type="text" name="code" x-model="editingCoupon.code" required placeholder="e.g. WELCOME50, FREESERVICE"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono uppercase font-bold text-slate-900 bg-white placeholder-slate-400 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Campaign Title / Name') }}</label>
                        <input type="text" name="name" x-model="editingCoupon.name" placeholder="e.g. Summer Agency Launch"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 bg-white placeholder-slate-400 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                    </div>
                </div>

                <!-- Offer Category Radio Selection -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-2">{{ __('Offer Type & Benefit *') }}</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <label class="border rounded-xl p-2.5 text-center cursor-pointer transition"
                               :class="editOfferType === 'discount_percent' ? 'border-fuchsia-500 bg-fuchsia-50/50 text-fuchsia-900 font-bold' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                            <input type="radio" name="offer_type" value="discount_percent" x-model="editOfferType" class="sr-only">
                            <i class="ph ph-percent text-base block mb-0.5"></i>
                            <span class="text-[11px] block">{{ __('% Discount') }}</span>
                        </label>
                        <label class="border rounded-xl p-2.5 text-center cursor-pointer transition"
                               :class="editOfferType === 'discount_fixed' ? 'border-fuchsia-500 bg-fuchsia-50/50 text-fuchsia-900 font-bold' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                            <input type="radio" name="offer_type" value="discount_fixed" x-model="editOfferType" class="sr-only">
                            <i class="ph ph-currency-dollar text-base block mb-0.5"></i>
                            <span class="text-[11px] block">{{ __('Fixed ($)') }}</span>
                        </label>
                        <label class="border rounded-xl p-2.5 text-center cursor-pointer transition"
                               :class="editOfferType === 'service' ? 'border-fuchsia-500 bg-fuchsia-50/50 text-fuchsia-900 font-bold' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                            <input type="radio" name="offer_type" value="service" x-model="editOfferType" class="sr-only">
                            <i class="ph ph-gift text-base block mb-0.5"></i>
                            <span class="text-[11px] block">{{ __('Free Service') }}</span>
                        </label>
                        <label class="border rounded-xl p-2.5 text-center cursor-pointer transition"
                               :class="editOfferType === 'trial_days' ? 'border-fuchsia-500 bg-fuchsia-50/50 text-fuchsia-900 font-bold' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                            <input type="radio" name="offer_type" value="trial_days" x-model="editOfferType" class="sr-only">
                            <i class="ph ph-clock text-base block mb-0.5"></i>
                            <span class="text-[11px] block">{{ __('Extra Trial') }}</span>
                        </label>
                    </div>
                </div>

                <!-- Dynamic Offer Inputs based on Selection -->
                <div x-show="editOfferType === 'discount_percent' || editOfferType === 'discount_fixed' || editOfferType === 'trial_days'">
                    <label class="block text-xs font-semibold text-slate-700 mb-1" x-text="editOfferType === 'discount_percent' ? 'Discount Percentage (%)' : (editOfferType === 'discount_fixed' ? 'Fixed Discount Amount ($ / Currency)' : 'Trial Extension Days')"></label>
                    <input type="number" step="0.01" min="0" name="discount_value" x-model="editingCoupon.discount_value" placeholder="e.g. 20"
                           class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 bg-white placeholder-slate-400 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                </div>

                <div x-show="editOfferType === 'service'" class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Select Catalog Service to Offer Free') }}</label>
                        <select name="service_id" x-model="editingCoupon.service_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                            <option value="">-- {{ __('Select from Active Catalog') }} --</option>
                            @foreach($services as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->category }} - ${{ number_format($s->rate_cents / 100, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Or Enter Custom Service Offer Title') }}</label>
                        <input type="text" name="custom_service_name" x-model="editingCoupon.custom_service_name" placeholder="e.g. Complimentary 1-Month Cloud Hosting & SSL"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 bg-white placeholder-slate-400 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                    </div>
                </div>

                <!-- Target Audience -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Applicable Target *') }}</label>
                        <select name="target" x-model="editingCoupon.target" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                            <option value="all">{{ __('All (Signup & Customer Invoices)') }}</option>
                            <option value="signup">{{ __('User Signup Only (Welcome Offer)') }}</option>
                            <option value="invoice">{{ __('Invoice Checkout Only (Clients)') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Max Redemptions Limit') }}</label>
                        <input type="number" min="1" name="max_redemptions" x-model="editingCoupon.max_redemptions" placeholder="Unlimited if blank"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 bg-white placeholder-slate-400 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                    </div>
                </div>

                <!-- Description, Decided Time Duration & Expiry (Request 14) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3" x-data="{ durationType: 'custom' }">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Min Spend ($)') }}</label>
                        <input type="number" step="0.01" min="0" name="min_purchase" x-model="editingCoupon.min_purchase" placeholder="0"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 bg-white placeholder-slate-400 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Decided Time Duration') }}</label>
                        <select name="duration_preset" x-model="durationType"
                                class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                            <option value="custom">{{ __('Custom Date & Time') }}</option>
                            <option value="never">{{ __('No Expiry (Never)') }}</option>
                            <option value="1_hour">{{ __('1 Hour (Flash)') }}</option>
                            <option value="6_hours">{{ __('6 Hours') }}</option>
                            <option value="12_hours">{{ __('12 Hours') }}</option>
                            <option value="24_hours">{{ __('24 Hours (1 Day)') }}</option>
                            <option value="48_hours">{{ __('48 Hours (2 Days)') }}</option>
                            <option value="3_days">{{ __('3 Days') }}</option>
                            <option value="7_days">{{ __('7 Days (1 Week)') }}</option>
                            <option value="14_days">{{ __('14 Days (2 Weeks)') }}</option>
                            <option value="30_days">{{ __('30 Days (1 Month)') }}</option>
                        </select>
                    </div>
                    <div x-show="durationType === 'custom'">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Specific Expiry Date') }}</label>
                        <input type="datetime-local" name="expires_at" x-model="editingCoupon.expires_at"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Campaign Description / Notes') }}</label>
                    <textarea name="description" x-model="editingCoupon.description" rows="2" placeholder="Internal notes or customer-facing description..."
                              class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 bg-white placeholder-slate-400 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none"></textarea>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" x-model="editingCoupon.is_active" class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500">
                        <span class="text-xs font-semibold text-slate-700">{{ __('Active Promotion') }}</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs font-bold transition">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs shadow-xs transition">
                            {{ __('Update Promotion') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
