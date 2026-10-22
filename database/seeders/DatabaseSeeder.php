<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Plan;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            IndustryPresetsSeeder::class,
            PlanSeeder::class,
            CouponSeeder::class,
        ]);

        // Create Default Demo Workspace
        $workspace = Workspace::firstOrCreate(
            ['slug' => 'redmaple-studios'],
            [
                'name' => 'RedMaple Digital Studio',
                'brand_color' => '#dc2626',
                'secondary_color' => '#1e293b',
                'currency' => 'USD',
                'language' => 'en',
                'country_code' => 'US',
                'tax_id' => 'US-987654321',
                'address' => '742 Evergreen Terrace, Suite 100, Springfield, OR',
                'phone' => '+1 (555) 019-2834',
                'email' => 'billing@redmaplestudio.com',
                'website' => 'https://redmaplestudio.com',
                'bank_details' => [
                    'bank_name' => 'Silicon Valley Commercial Bank',
                    'account_number' => '********9842',
                    'routing_number' => '121000358',
                    'swift' => 'SVCBUS33',
                ],
                'is_whitelabel_enabled' => true,
            ]
        );

        // Create Demo Admin User
        $user = User::firstOrCreate(
            ['email' => 'admin@redmaplestudio.com'],
            [
                'workspace_id' => $workspace->id,
                'name' => 'Alex Rivera',
                'password' => Hash::make('Password123!'),
                'role' => 'owner',
            ]
        );

        // Assign Pro Plan Subscription
        $proPlan = Plan::where('code', 'pro')->first();
        if ($proPlan) {
            Subscription::firstOrCreate(
                ['workspace_id' => $workspace->id],
                [
                    'plan_id' => $proPlan->id,
                    'gateway' => 'stripe',
                    'gateway_subscription_id' => 'sub_demo_123456789',
                    'status' => 'active',
                    'current_period_ends_at' => now()->addMonth(),
                ]
            );
        }

        // Create Demo Clients
        $clientA = Client::firstOrCreate(
            ['workspace_id' => $workspace->id, 'email' => 'finance@globex.corp'],
            [
                'name' => 'Sarah Jenkins',
                'company_name' => 'Globex Corporation',
                'phone' => '+1 (555) 321-4321',
                'tax_id' => 'GB987654321',
                'address' => '100 Financial Boulevard, Canary Wharf, London',
                'city' => 'London',
                'state' => 'Greater London',
                'postal_code' => 'E14 5AB',
                'country_code' => 'GB',
                'preferred_currency' => 'GBP',
                'preferred_language' => 'en',
                'default_payment_terms' => 'net_14',
                'is_tax_exempt' => false,
            ]
        );

        $clientB = Client::firstOrCreate(
            ['workspace_id' => $workspace->id, 'email' => 'omar@alnoor.ae'],
            [
                'name' => 'Omar Al-Mansoor',
                'company_name' => 'Al Noor Tech Trading LLC',
                'phone' => '+971 4 398 7654',
                'tax_id' => 'AE100293847500003',
                'address' => 'Floor 22, Burj Daman, DIFC',
                'city' => 'Dubai',
                'state' => 'Dubai',
                'country_code' => 'AE',
                'preferred_currency' => 'AED',
                'preferred_language' => 'ar',
                'default_payment_terms' => 'due_on_receipt',
                'is_tax_exempt' => false,
            ]
        );

        // Import some initial services for the demo workspace
        Service::firstOrCreate(
            ['workspace_id' => $workspace->id, 'name' => 'Full-Stack Web Application'],
            [
                'category' => 'Development',
                'description' => 'Architectural design, backend API development, and frontend interface',
                'unit' => 'project',
                'rate_cents' => 350000,
                'default_tax_rate' => 20.00,
            ]
        );

        Service::firstOrCreate(
            ['workspace_id' => $workspace->id, 'name' => 'Vector Artwork & Screen Separation'],
            [
                'category' => 'Artwork',
                'description' => 'High-resolution manual redraw with 6-spot color channel separations',
                'unit' => 'artwork',
                'rate_cents' => 4500,
                'default_tax_rate' => 0.00,
            ]
        );
    }
}
