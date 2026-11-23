<?php

namespace Tests\Feature;

use App\Models\CmsContent;
use App\Models\SubscriptionAlert;
use App\Models\User;
use App\Models\Workspace;
use Tests\TestCase;

class SuperAdminPortalTest extends TestCase
{
    private User $superadmin;
    private User $tenantOwner;
    private Workspace $workspace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::create([
            'name' => 'Root SuperAdmin',
            'email' => 'superadmin@redmaple.test',
            'password' => bcrypt('Password123!'),
            'role' => 'superadmin',
            'email_verified_at' => now(),
        ]);

        $this->workspace = Workspace::create([
            'name' => 'Demo Agency',
            'slug' => 'demo-agency',
            'currency' => 'USD',
            'country_code' => 'US',
        ]);

        $this->tenantOwner = User::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Regular Owner',
            'email' => 'owner@demo.test',
            'password' => bcrypt('Password123!'),
            'role' => 'owner',
            'email_verified_at' => now(),
        ]);
    }

    public function test_tenant_owner_cannot_access_superadmin_portal(): void
    {
        $this->actingAs($this->tenantOwner);

        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(403);

        $tourResponse = $this->get(route('admin.config.tour'));
        $tourResponse->assertStatus(403);
    }

    public function test_superadmin_can_access_superadmin_views(): void
    {
        $this->actingAs($this->superadmin);

        $this->get(route('admin.dashboard'))->assertStatus(200);
        $this->get(route('admin.config.tour'))->assertStatus(200);
        $this->get(route('admin.advertisements'))->assertStatus(200);
        $this->get(route('admin.builder'))->assertStatus(200);
        $this->get(route('admin.cms'))->assertStatus(200);
    }

    public function test_superadmin_can_save_dynamic_welcome_tour_slides(): void
    {
        $this->actingAs($this->superadmin);

        $slides = [
            [
                'step' => 1,
                'badge' => 'STEP 1: IDENTITY',
                'title' => 'Custom Onboarding Slide One',
                'description' => 'Test guidance description for testing',
                'icon' => 'ph ph-rocket-launch',
                'color' => 'blue',
            ],
            [
                'step' => 2,
                'badge' => 'STEP 2: INVOICES',
                'title' => 'Create Your Invoices',
                'description' => 'Second slide guidance description',
                'icon' => 'ph ph-receipt',
                'color' => 'amber',
            ],
        ];

        $response = $this->post(route('admin.config.tour.update'), [
            'slides' => json_encode($slides),
        ]);

        $response->assertRedirect();
        $this->assertCount(2, CmsContent::getWelcomeTourSlides());
        $this->assertEquals('Custom Onboarding Slide One', CmsContent::getWelcomeTourSlides()[0]['title']);
    }

    public function test_subscription_alert_mark_as_read_resets_unread_count(): void
    {
        $this->actingAs($this->superadmin);

        $alert = SubscriptionAlert::create([
            'workspace_id' => $this->workspace->id,
            'type' => 'upgrade_request',
            'severity' => 'warning',
            'title' => 'Enterprise Upgrade Requested',
            'message' => 'Demo Agency requested an upgrade',
            'is_read' => false,
        ]);

        $this->assertEquals(1, SubscriptionAlert::unread()->count());

        $this->post(route('admin.subscriptions.mark-alert-read', $alert->id));

        $this->assertEquals(0, SubscriptionAlert::unread()->count());
    }

    public function test_admin_layout_renders_tenant_app_with_target_blank(): void
    {
        $this->actingAs($this->superadmin);

        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('target="_blank"', false);
    }
}
