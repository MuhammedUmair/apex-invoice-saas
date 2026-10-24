<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $workspace = $user->workspace;

        return view('settings.index', [
            'workspace' => $workspace,
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $workspace = $user->workspace;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'tax_id' => 'nullable|string|max:100',
            'country_code' => 'required|string|size:2',
            'currency' => 'required|string|size:3',
            'language' => 'required|string|max:10',
            'address' => 'nullable|string',
            'brand_color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
            'secondary_color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
            'logo_url' => 'nullable|url|max:500',
            'remove_logo' => 'nullable|boolean',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:100',
            'routing_number' => 'nullable|string|max:100',
            'swift' => 'nullable|string|max:50',
            'stripe_publishable_key' => 'nullable|string',
            'stripe_secret_key' => 'nullable|string',
            'stripe_webhook_secret' => 'nullable|string',
            'paypal_client_id' => 'nullable|string',
            'paypal_client_secret' => 'nullable|string',
            'paypal_webhook_id' => 'nullable|string',
            'is_whitelabel_enabled' => 'nullable|boolean',
        ]);

        $workspace->name = $validated['name'];
        $workspace->email = $validated['email'] ?? null;
        $workspace->phone = $validated['phone'] ?? null;
        $workspace->website = $validated['website'] ?? null;
        $workspace->tax_id = $validated['tax_id'] ?? null;
        $workspace->country_code = $validated['country_code'];
        $workspace->currency = $validated['currency'];
        $workspace->language = $validated['language'];
        $workspace->address = $validated['address'] ?? null;
        $workspace->brand_color = $validated['brand_color'];
        $workspace->secondary_color = $validated['secondary_color'];
        $workspace->is_whitelabel_enabled = (bool)($request->has('is_whitelabel_enabled'));

        // Handle Custom Logo Upload or Removal
        if ($request->has('remove_logo') && $request->remove_logo) {
            $workspace->logo_url = null;
        } elseif ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'logo_' . $workspace->slug . '_' . time() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('images/uploads');
            if (!file_exists($destinationPath)) {
                @mkdir($destinationPath, 0775, true);
            }
            $file->move($destinationPath, $filename);
            $workspace->logo_url = asset('images/uploads/' . $filename);
        } elseif (!empty($validated['logo_url'])) {
            $workspace->logo_url = $validated['logo_url'];
        }

        // Save bank details array
        $workspace->bank_details = [
            'bank_name' => $validated['bank_name'] ?? '',
            'account_number' => $validated['account_number'] ?? '',
            'routing_number' => $validated['routing_number'] ?? '',
            'swift' => $validated['swift'] ?? '',
        ];

        // Save Gateway keys if provided
        if (!empty($validated['stripe_publishable_key']) || !empty($validated['stripe_secret_key'])) {
            $workspace->stripe_keys_encrypted = [
                'publishable_key' => $validated['stripe_publishable_key'] ?? '',
                'secret_key' => $validated['stripe_secret_key'] ?? '',
                'webhook_secret' => $validated['stripe_webhook_secret'] ?? '',
            ];
        }

        if (!empty($validated['paypal_client_id']) || !empty($validated['paypal_client_secret'])) {
            $workspace->paypal_keys_encrypted = [
                'client_id' => $validated['paypal_client_id'] ?? '',
                'client_secret' => $validated['paypal_client_secret'] ?? '',
                'webhook_id' => $validated['paypal_webhook_id'] ?? '',
            ];
        }

        $workspace->save();

        return redirect()->route('settings.index')->with('success', 'Workspace settings and logo updated successfully.');
    }
}
