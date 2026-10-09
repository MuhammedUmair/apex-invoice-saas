<?php

namespace App\Livewire;

use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\CouponService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MembershipCheckout extends Component
{
    public ?string $selectedPlanId = null;
    public string $couponCode = '';
    public string $couponStatus = '';
    public bool $isCouponValid = false;
    public int $discountCents = 0;
    public string $gateway = 'stripe'; // stripe or paypal

    protected CouponService $couponService;

    public function boot(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    public function mount()
    {
        $currentSub = Auth::user()->workspace->currentSubscription;
        if ($currentSub) {
            $this->selectedPlanId = $currentSub->plan_id;
        } else {
            $firstPlan = Plan::where('code', 'starter')->first();
            $this->selectedPlanId = $firstPlan ? $firstPlan->id : null;
        }
    }

    public function selectPlan(string $planId)
    {
        $this->selectedPlanId = $planId;
        if ($this->couponCode) {
            $this->validateCoupon();
        }
    }

    public function validateCoupon()
    {
        $workspace = Auth::user()->workspace;
        $plan = Plan::find($this->selectedPlanId);

        if (!$plan) {
            $this->couponStatus = 'Please select a membership package first.';
            $this->isCouponValid = false;
            return;
        }

        $result = $this->couponService->validateCode(
            $this->couponCode,
            $workspace,
            $plan->price_cents,
            $plan->currency
        );

        $this->isCouponValid = $result['valid'];
        $this->couponStatus = $result['message'];
        $this->discountCents = $result['discount_cents'];
    }

    public function processCheckout()
    {
        $workspace = Auth::user()->workspace;
        $plan = Plan::findOrFail($this->selectedPlanId);

        $finalPriceCents = max(0, $plan->price_cents - $this->discountCents);

        // If coupon applied, record atomic redemption
        if ($this->isCouponValid && $this->couponCode) {
            $coupon = Coupon::where('code', strtoupper(trim($this->couponCode)))->first();
            if ($coupon) {
                $this->couponService->redeemCoupon(
                    $coupon,
                    $workspace,
                    $plan->price_cents,
                    $plan->currency,
                    'sub_active_' . time(),
                    $this->gateway
                );
            }
        }

        // Activate / Update Subscription
        Subscription::updateOrCreate(
            ['workspace_id' => $workspace->id],
            [
                'plan_id' => $plan->id,
                'gateway' => $this->gateway,
                'gateway_subscription_id' => 'sub_' . strtolower($this->gateway) . '_' . time(),
                'status' => 'active',
                'current_period_ends_at' => now()->addMonth(),
            ]
        );

        session()->flash('success', "Your membership has been upgraded to {$plan->name}!");
        return redirect()->route('dashboard');
    }

    public function render()
    {
        $plans = Plan::where('is_active', true)->where('code', '!=', 'trial')->orderBy('price_cents')->get();
        $selectedPlan = $this->selectedPlanId ? Plan::find($this->selectedPlanId) : null;

        $finalPriceFormatted = '$0.00';
        if ($selectedPlan) {
            $finalCents = max(0, $selectedPlan->price_cents - $this->discountCents);
            $finalPriceFormatted = '$' . number_format($finalCents / 100, 2);
        }

        return view('livewire.membership-checkout', [
            'plans' => $plans,
            'selectedPlan' => $selectedPlan,
            'finalPriceFormatted' => $finalPriceFormatted,
        ]);
    }
}
