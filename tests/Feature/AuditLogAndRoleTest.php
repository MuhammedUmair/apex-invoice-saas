<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuditLogAndRoleTest extends TestCase
{
    use RefreshDatabase;

    protected Workspace $workspace;
    protected User $owner;
    protected User $staff;
    protected User $superAdmin;
    protected Plan $proPlan;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->workspace = Workspace::create([
            'name' => 'Audit Test Studio',
            'slug' => 'audit-studio',
            'currency' => 'USD',
            'country_code' => 'US',
        ]);

        $this->owner = User::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Original Owner',
            'email' => 'old_owner@test.com',
            'password' => bcrypt('StrongSecret123!'),
            'role' => 'owner',
            'email_verified_at' => now(),
        ]);

        $this->staff = User::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Staff Member',
            'email' => 'staff@test.com',
            'password' => bcrypt('StaffPass123!'),
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $this->superAdmin = User::create([
            'workspace_id' => null,
            'name' => 'Platform Admin',
            'email' => 'admin@redmaplestudio.com',
            'password' => bcrypt('AdminSuperPass123!'),
            'role' => 'admin',
            'is_super_admin' => true,
            'email_verified_at' => now(),
        ]);

        $this->proPlan = Plan::create([
            'name' => 'Pro Business Plan',
            'code' => 'pro',
            'price_cents' => 4900,
            'currency' => 'USD',
            'invoice_limit' => 99999,
            'is_trial' => false,
        ]);
    }

    /** @test */
    public function changing_owner_email_requires_current_password_and_alerts_old_address(): void
    {
        $this->actingAs($this->owner);

        // Attempt 1: Without current password -> must fail
        $res1 = $this->post(route('settings.profile'), [
            'name' => 'Original Owner',
            'email' => 'new_owner@test.com',
            'current_password' => '',
        ]);
        $res1->assertSessionHasErrors('current_password');

        // Attempt 2: With correct password -> must succeed, alert old address, and log audit
        $res2 = $this->post(route('settings.profile'), [
            'name' => 'Original Owner',
            'email' => 'new_owner@test.com',
            'current_password' => 'StrongSecret123!',
        ]);
        $res2->assertRedirect(route('settings.index'));

        // Verify database updated
        $this->owner->refresh();
        $this->assertEquals('new_owner@test.com', $this->owner->email);
        $this->assertNull($this->owner->email_verified_at);

        // Verify audit log entry exists
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->owner->id,
            'action' => 'user.email_changed',
        ]);
    }

    /** @test */
    public function super_admin_approving_upgrade_is_idempotent_and_creates_audit_log(): void
    {
        $this->actingAs($this->superAdmin);

        // First approval request
        $res1 = $this->post(route('admin.workspaces.renew', $this->workspace->id), [
            'plan_id' => $this->proPlan->id,
            'duration_months' => 12,
        ]);
        $res1->assertSessionHas('success');

        // Immediate retry (simulating double click or network retry)
        $res2 = $this->post(route('admin.workspaces.renew', $this->workspace->id), [
            'plan_id' => $this->proPlan->id,
            'duration_months' => 12,
        ]);
        $res2->assertSessionHas('success');

        // Verify only 1 active subscription exists for workspace
        $activeSubsCount = Subscription::where('workspace_id', $this->workspace->id)->where('status', 'active')->count();
        $this->assertEquals(1, $activeSubsCount);

        // Verify audit log recorded
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'admin.subscription_approved',
            'workspace_id' => $this->workspace->id,
        ]);
    }

    /** @test */
    public function tenant_staff_is_blocked_from_super_admin_routes_with_403(): void
    {
        $this->actingAs($this->staff);

        $response = $this->get(route('admin.subscriptions.index'));
        $response->assertStatus(403);
    }
}
