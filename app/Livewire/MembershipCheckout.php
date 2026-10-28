<?php

namespace App\Livewire;

use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\CouponService;
use App\Services\SubscriptionAlertService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MembershipCheckout extends Component
{
    public ?string $selectedPlanId = null;
    public string $couponCode = '';
    public string $couponStatus = '';
    public bool $isCouponValid = false;
    public int $discountCents = 0;
    public string $paymentNotes = '';
    public bool $requestSubmitted = false;
    public ?string $submittedPlanName = null;

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
        $this->requestSubmitted = false;
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

        // Case 1: 100% Free Promotion via Valid Promotional Coupon
        if ($this->isCouponValid && $this->couponCode && $finalPriceCents === 0) {
            $coupon = Coupon::where('code', strtoupper(trim($this->couponCode)))->first();
            if ($coupon) {
                $this->couponService->redeemCoupon(
                    $coupon,
                    $workspace,
                    $plan->price_cents,
                    $plan->currency,
                    'coupon_promo_' . time(),
                    'promo_coupon'
                );
            }

            $existingSub = $workspace->currentSubscription ?? $workspace->subscriptions()->first();
            $oldPlan = $existingSub?->plan;

            Subscription::updateOrCreate(
                ['workspace_id' => $workspace->id],
                [
                    'plan_id' => $plan->id,
                    'gateway' => 'promo_coupon',
                    'gateway_subscription_id' => 'promo_' . time(),
                    'status' => 'active',
                    'current_period_ends_at' => now()->addMonth(),
                ]
            );

            app(SubscriptionAlertService::class)->notifyPlanChange(
                $workspace,
                $oldPlan,
                $plan,
                'promo_coupon',
                0
            );

            session()->flash('success', "Promotional Coupon Applied! Your membership has been activated on {$plan->name}!");
            return redirect()->route('dashboard');
        }

        // Case 2: Standard Paid Plan Upgrade Request (Option B - Manual Wire / Bank Invoice Approval)
        if ($this->isCouponValid && $this->couponCode) {
            $coupon = Coupon::where('code', strtoupper(trim($this->couponCode)))->first();
            if ($coupon) {
                $this->couponService->redeemCoupon(
                    $coupon,
                    $workspace,
                    $plan->price_cents,
                    $plan->currency,
                    'pending_upgrade_' . time(),
                    'wire_transfer'
                );
            }
        }

        // Dispatch Super Admin Upgrade Request Alert with customer notes
        app(SubscriptionAlertService::class)->notifyUpgradeRequest(
            $workspace,
            $plan,
            $this->paymentNotes,
            $finalPriceCents
        );

        $this->requestSubmitted = true;
        $this->submittedPlanName = $plan->name;

        session()->flash('upgrade_requested', "Your upgrade request for {$plan->name} has been submitted! Our billing team has been notified and will verify your wire transfer / manual payment before activating your account.");
    }

    public function render()
    {
        $currentSub = Auth::user()->workspace->currentSubscription;
        $currentPlan = $currentSub?->plan;

        $plans = Plan::where('is_active', true)->where('code', '!=', 'trial')->orderBy('price_cents')->get();
        $selectedPlan = $this->selectedPlanId ? Plan::find($this->selectedPlanId) : null;

        $finalPriceFormatted = '$0.00';
        if ($selectedPlan) {
            $finalCents = max(0, $selectedPlan->price_cents - $this->discountCents);
            $finalPriceFormatted = '$' . number_format($finalCents / 100, 2);
        }

        return view('livewire.membership-checkout', [
            'currentSub' => $currentSub,
            'currentPlan' => $currentPlan,
            'plans' => $plans,
            'selectedPlan' => $selectedPlan,
            'finalPriceFormatted' => $finalPriceFormatted,
        ]);
    }
}
