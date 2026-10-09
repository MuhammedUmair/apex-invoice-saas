<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free Trial',
                'code' => 'trial',
                'price_cents' => 0,
                'currency' => 'USD',
                'billing_interval' => 'month',
                'invoice_limit' => 15,
                'client_limit' => 10,
                'has_whitelabel' => false,
                'has_client_portal' => true,
                'has_recurring' => false,
                'team_seats' => 1,
                'features_json' => [
                    '14-Day Full Access Trial',
                    'Up to 15 Invoices',
                    'Password-Protected PDFs',
                    'Standard Multi-Currency Support',
                    'Basic Email Sending',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Starter Agency',
                'code' => 'starter',
                'price_cents' => 1900,
                'currency' => 'USD',
                'billing_interval' => 'month',
                'invoice_limit' => 60,
                'client_limit' => 35,
                'has_whitelabel' => false,
                'has_client_portal' => true,
                'has_recurring' => false,
                'team_seats' => 2,
                'features_json' => [
                    '60 Invoices / Month',
                    '35 Saved Clients',
                    'Password-Protected PDFs',
                    'All 9 Languages & RTL Invoices',
                    'Stripe & PayPal Client Pay Now Links',
                    'Custom Industry Presets Importer',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Pro Business',
                'code' => 'pro',
                'price_cents' => 4900,
                'currency' => 'USD',
                'billing_interval' => 'month',
                'invoice_limit' => -1, // unlimited
                'client_limit' => -1,  // unlimited
                'has_whitelabel' => true,
                'has_client_portal' => true,
                'has_recurring' => true,
                'team_seats' => 5,
                'features_json' => [
                    'Unlimited Invoices & Clients',
                    'Credit Notes & Refund Tracking',
                    'Interactive Customer Pay Portal',
                    'Custom Workspace Branding & Colors',
                    'Country Tax Snapshots & VAT Rules',
                    '5 Team Member Accounts',
                    'Priority Email Delivery via Custom SMTP',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise Scale',
                'code' => 'enterprise',
                'price_cents' => 9900,
                'currency' => 'USD',
                'billing_interval' => 'month',
                'invoice_limit' => -1,
                'client_limit' => -1,
                'has_whitelabel' => true,
                'has_client_portal' => true,
                'has_recurring' => true,
                'team_seats' => 15,
                'features_json' => [
                    'Unlimited Everything',
                    '100% White-label Invoicing & Domain',
                    '15 Team Members with RBAC Roles',
                    'Automated Recurring Invoices & Dunning',
                    'Dedicated Account Onboarding',
                    'Raw Accounting & CSV Data Export',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($plans as $p) {
            Plan::updateOrCreate(
                ['code' => $p['code']],
                $p
            );
        }
    }
}
