<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'LAUNCH2026',
                'discount_type' => 'percent',
                'discount_value' => 20.00,
                'currency_restriction' => null,
                'min_purchase_cents' => 0,
                'duration' => 'repeating',
                'duration_months' => 6,
                'max_redemptions' => 1000,
                'is_active' => true,
                'expires_at' => now()->addMonths(12),
            ],
            [
                'code' => 'HOSTINGER50',
                'discount_type' => 'percent',
                'discount_value' => 50.00,
                'currency_restriction' => 'USD',
                'min_purchase_cents' => 1900,
                'duration' => 'once',
                'duration_months' => 1,
                'max_redemptions' => 500,
                'is_active' => true,
                'expires_at' => now()->addMonths(6),
            ],
            [
                'code' => 'SAVE10FIXED',
                'discount_type' => 'fixed',
                'discount_value' => 10.00,
                'currency_restriction' => 'USD',
                'min_purchase_cents' => 1900,
                'duration' => 'once',
                'duration_months' => null,
                'max_redemptions' => 250,
                'is_active' => true,
                'expires_at' => now()->addMonths(3),
            ],
        ];

        foreach ($coupons as $c) {
            Coupon::updateOrCreate(
                ['code' => $c['code']],
                $c
            );
        }
    }
}
