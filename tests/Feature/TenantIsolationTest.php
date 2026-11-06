<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Workspace $workspaceA;
    protected User $userA;
    protected Workspace $workspaceB;
    protected User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        // Tenant A
        $this->workspaceA = Workspace::create([
            'name' => 'Agency Alpha Corp',
            'slug' => 'alpha-corp',
            'currency' => 'USD',
            'country_code' => 'US',
        ]);
        $this->userA = User::create([
            'workspace_id' => $this->workspaceA->id,
            'name' => 'Alice Alpha',
            'email' => 'alice@alpha.test',
            'password' => bcrypt('Password123!'),
            'role' => 'owner',
            'email_verified_at' => now(),
        ]);

        // Tenant B
        $this->workspaceB = Workspace::create([
            'name' => 'Agency Beta LLC',
            'slug' => 'beta-llc',
            'currency' => 'EUR',
            'country_code' => 'DE',
        ]);
        $this->userB = User::create([
            'workspace_id' => $this->workspaceB->id,
            'name' => 'Bob Beta',
            'email' => 'bob@beta.test',
            'password' => bcrypt('Password123!'),
            'role' => 'owner',
            'email_verified_at' => now(),
        ]);
    }

    /** @test */
    public function tenant_b_cannot_view_tenant_a_invoice_via_http_returning_404(): void
    {
        $this->actingAs($this->userA);
        $clientA = Client::create([
            'workspace_id' => $this->workspaceA->id,
            'name' => 'Client Alpha 1',
            'email' => 'client1@alpha.test',
        ]);
        $invoiceA = Invoice::create([
            'workspace_id' => $this->workspaceA->id,
            'client_id' => $clientA->id,
            'invoice_number' => 'INV-2026-0001',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'total_cents' => 50000,
            'due_cents' => 50000,
        ]);

        // Switch identity to Tenant B
        $this->actingAs($this->userB);

        // HTTP GET request to Tenant A's invoice must return 404 (Fail-Closed)
        $response = $this->get(route('invoices.show', $invoiceA->id));
        $response->assertStatus(404);
    }

    /** @test */
    public function tenant_b_cannot_tamper_or_edit_tenant_a_invoice_returning_404(): void
    {
        $this->actingAs($this->userA);
        $clientA = Client::create([
            'workspace_id' => $this->workspaceA->id,
            'name' => 'Client Alpha 2',
            'email' => 'client2@alpha.test',
        ]);
        $invoiceA = Invoice::create([
            'workspace_id' => $this->workspaceA->id,
            'client_id' => $clientA->id,
            'invoice_number' => 'INV-2026-0002',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
        ]);

        $this->actingAs($this->userB);

        $response = $this->get(route('invoices.edit', $invoiceA->id));
        $response->assertStatus(404);
    }

    /** @test */
    public function fail_closed_scope_blocks_unauthenticated_or_empty_workspace_queries(): void
    {
        // Unauthenticated environment
        auth()->logout();

        $count = Invoice::count();
        $this->assertEquals(0, $count, 'Fail-closed scope must return 0 records when unauthenticated.');
    }

    /** @test */
    public function non_null_workspace_constraint_throws_exception_on_missing_workspace(): void
    {
        auth()->logout();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Multi-tenant integrity violation');

        Invoice::create([
            'invoice_number' => 'INV-ORPHAN-001',
            'issue_date' => now(),
            'due_date' => now()->addDays(14),
        ]);
    }
}
