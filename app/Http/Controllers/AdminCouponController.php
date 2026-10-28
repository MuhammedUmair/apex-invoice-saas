<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::withCount('redemptions')
            ->latest()
            ->paginate(15);

        $services = Service::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $recentRedemptions = CouponRedemption::with(['coupon', 'workspace'])
            ->latest('redeemed_at')
            ->take(10)
            ->get();

        $stats = [
            'total_coupons' => Coupon::count(),
            'active_coupons' => Coupon::where('is_active', true)->count(),
            'total_redemptions' => CouponRedemption::count(),
            'total_discount_given_cents' => CouponRedemption::sum('discount_applied_cents'),
        ];

        return view('admin.coupons.index', compact('coupons', 'services', 'recentRedemptions', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'offer_type' => 'required|in:discount_percent,discount_fixed,service,trial_days',
            'discount_value' => 'nullable|numeric|min:0',
            'service_id' => 'nullable|string',
            'custom_service_name' => 'nullable|string|max:255',
            'target' => 'required|in:all,signup,invoice',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_redemptions' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        $code = strtoupper(preg_replace('/[^A-Za-z0-9_-]/', '', $data['code']));

        // Map offer_type to coupon fields
        $discountType = 'fixed';
        $discountValue = 0.00;
        $serviceId = null;
        $serviceName = null;
        $offerType = 'discount';

        switch ($data['offer_type']) {
            case 'discount_percent':
                $discountType = 'percent';
                $discountValue = (float) ($data['discount_value'] ?? 10);
                $offerType = 'discount';
                break;
            case 'discount_fixed':
                $discountType = 'fixed';
                $discountValue = (float) ($data['discount_value'] ?? 10);
                $offerType = 'discount';
                break;
            case 'service':
                $discountType = 'fixed';
                $offerType = 'service';
                if (!empty($data['service_id'])) {
                    $srv = Service::find($data['service_id']);
                    if ($srv) {
                        $serviceId = $srv->id;
                        $serviceName = $srv->name;
                        $discountValue = (float) ($srv->rate_cents / 100);
                    }
                }
                if (empty($serviceName) && !empty($data['custom_service_name'])) {
                    $serviceName = $data['custom_service_name'];
                }
                break;
            case 'trial_days':
                $discountType = 'trial_days';
                $discountValue = (float) ($data['discount_value'] ?? 30);
                $offerType = 'trial';
                break;
        }

        Coupon::create([
            'code' => $code,
            'name' => $data['name'] ?: $code,
            'description' => $data['description'],
            'discount_type' => $discountType,
            'offer_type' => $offerType,
            'discount_value' => $discountValue,
            'service_id' => $serviceId,
            'service_name' => $serviceName,
            'target' => $data['target'],
            'min_purchase_cents' => (int) round(((float) ($data['min_purchase'] ?? 0)) * 100),
            'max_redemptions' => $data['max_redemptions'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.coupons.index')->with('success', "Promotion coupon '{$code}' successfully created!");
    }

    public function toggle(Coupon $coupon)
    {
        $coupon->update([
            'is_active' => !$coupon->is_active,
        ]);

        $status = $coupon->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Coupon '{$coupon->code}' {$status} successfully.");
    }

    public function destroy(Coupon $coupon)
    {
        $code = $coupon->code;
        $coupon->delete();

        return back()->with('success', "Coupon '{$code}' deleted successfully.");
    }
}
