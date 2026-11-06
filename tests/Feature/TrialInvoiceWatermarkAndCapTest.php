<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TrialInvoiceWatermarkAndCapTest extends TestCase
{
    use RefreshDatabase;

    protected Workspace $trialWorkspace;
    protected User $trialUser;
    protected Client $client;
    protected Invoice $trialInvoice;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->trialWorkspace = Workspace::create([
            'name' => 'Trial Evaluation Studio',
            'slug' => 'trial-studio',
            'currency' => 'USD',
            'country_code' => 'US',
            'daily_email_send_cap' => 5,
        ]);

        $this->trialUser = User::create([
            'workspace_id' => $this->trialWorkspace->id,
            'name' => 'Trial Owner',
            'email' => 'trial@test.com',
            'password' => bcrypt('Secret123!'),
            'role' => 'owner',
            'email_verified_at' => now(), // verified
        ]);

        $trialPlan = Plan::create([
            'name' => '14-Day Free Trial',
            'code' => 'trial',
            'price_cents' => 0,
            'currency' => 'USD',
            'invoice_limit' => 5,
            'is_trial' => true,
        ]);

        Subscription::create([
            'workspace_id' => $this->trialWorkspace->id,
            'plan_id' => $trialPlan->id,
            'gateway' => 'trial',
            'status' => 'trialing',
            'trial_ends_at' => now()->addDays(14),
        ]);

        $this->client = Client::create([
            'workspace_id' => $this->trialWorkspace->id,
            'name' => 'Client Test Corp',
            'email' => 'client@testcorp.test',
        ]);

        $this->trialInvoice = Invoice::create([
            'workspace_id' => $this->trialWorkspace->id,
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-2026-TRIAL1',
            'issue_date' => now(),
            'due_date' => now()->addDays(10),
            'total_cents' => 15000,
            'due_cents' => 15000,
            'public_secure_token' => 'trial_token_sample_12345',
        ]);
    }

    /** @test */
    public function trial_invoices_display_big_diagonal_watermark_in_pdf_and_portal(): void
    {
        $this->assertTrue($this->trialInvoice->isTrialInvoice());

        // Check PDF template view
        $pdfView = $this->get(route('invoices.pdf.download', $this->trialInvoice->public_secure_token));
        $pdfView->assertStatus(200);
        $pdfView->assertSee('TRIAL VERSION');
        $pdfView->assertSee('NOT FOR OFFICIAL ACCOUNTING');

        // Check Customer Portal view
        $portalView = $this->get(route('invoices.portal', $this->trialInvoice->public_secure_token));
        $portalView->assertStatus(200);
        $portalView->assertSee('Trial Version &bull; Not For Official Accounting', false);
    }

    /** @test */
    public function unverified_email_users_are_blocked_from_dispatching_invoices(): void
    {
        $this->trialUser->update(['email_verified_at' => null]);
        $this->actingAs($this->trialUser);

        $response = $this->post(route('invoices.send', $this->trialInvoice->id));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Email Verification Required', session('error'));

        Mail::assertNothingSent();
    }

    /** @test */
    public function trial_workspaces_are_strictly_capped_at_5_emails_per_day(): void
    {
        $this->actingAs($this->trialUser);

        // Exhaust the 5 email cap
        $this->trialWorkspace->update([
            'daily_emails_sent_count' => 5,
            'last_email_sent_date' => now()->toDateString(),
        ]);

        $response = $this->post(route('invoices.send', $this->trialInvoice->id));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Daily dispatch cap reached', session('error'));

        Mail::assertNothingSent();
    }
}
