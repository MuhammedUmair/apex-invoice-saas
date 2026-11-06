<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Plan;
use App\Models\User;
use App\Models\Workspace;
use App\Services\CouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class CouponAbuseTest extends TestCase
{
    use RefreshDatabase;

    protected Workspace $workspace;
    protected User $user;
    protected CouponService $couponService;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('coupon_validate:127.0.0.1');

        $this->couponService = app(CouponService::class);

        $this->workspace = Workspace::create([
            'name' => 'Coupon Test Studio',
            'slug' => 'coupon-test-studio',
            'currency' => 'USD',
            'country_code' => 'US',
        ]);

        $this->user = User::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Promo Hunter',
            'email' => 'hunter@test.com',
            'password' => bcrypt('Secret123!'),
            'role' => 'owner',
            'email_verified_at' => now(),
        ]);
    }

    /** @test */
    public function coupon_validation_is_rate_limited_to_prevent_brute_forcing(): void
    {
        // 5 consecutive invalid guesses
        for ($i = 1; $i <= 5; $i++) {
            $res = $this->couponService->validate("FAKECODE{$i}", 'all', 5000, 'USD', $this->workspace);
            $this->assertFalse($res['valid']);
        }

        // 6th attempt must be throttled
        $res6 = $this->couponService->validate('ANYCODE', 'all', 5000, 'USD', $this->workspace);
        $this->assertFalse($res6['valid']);
        $this->assertStringContainsString('Too many coupon validation attempts', $res6['message']);
    }

    /** @test */
    public function global_max_redemptions_blocks_further_use_once_exhausted(): void
    {
        $coupon = Coupon::create([
            'code' => 'LIMITED10',
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'max_redemptions' => 1,
            'times_redeemed' => 1, // exhausted
            'is_active' => true,
        ]);

        $res = $this->couponService->validate('LIMITED10', 'all', 5000, 'USD', $this->workspace);
        $this->assertFalse($res['valid']);
        $this->assertStringContainsString('maximum global usage limit', $res['message']);
    }

    /** @test */
    public function free_plan_loop_is_blocked_for_same_user_across_multiple_workspaces(): void
    {
        $freeCoupon = Coupon::create([
            'code' => 'FREE100',
            'discount_type' => 'percentage',
            'discount_value' => 100,
            'is_active' => true,
        ]);

        // User redeems in workspace 1
        CouponRedemption::create([
            'coupon_id' => $freeCoupon->id,
            'workspace_id' => $this->workspace->id,
            'user_id' => $this->user->id,
            'discount_applied_cents' => 9900,
            'currency' => 'USD',
            'gateway' => 'promo_coupon',
            'redeemed_at' => now(),
        ]);

        // User creates workspace 2
        $workspace2 = Workspace::create([
            'name' => 'Secondary Loop Workspace',
            'slug' => 'secondary-loop',
            'currency' => 'USD',
            'country_code' => 'US',
        ]);

        $this->actingAs($this->user);

        // Validation for workspace 2 must be rejected
        $res = $this->couponService->validate('FREE100', 'all', 9900, 'USD', $workspace2);
        $this->assertFalse($res['valid']);
        $this->assertStringContainsString('already redeemed this promotional coupon', $res['message']);
    }
}
