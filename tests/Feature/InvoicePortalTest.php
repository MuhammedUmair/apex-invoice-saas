<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePortalTest extends TestCase
{
    use RefreshDatabase;

    protected Workspace $workspace;
    protected Client $client;
    protected Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->workspace = Workspace::create([
            'name' => 'Portal Test Studio',
            'slug' => 'portal-studio',
            'currency' => 'USD',
            'country_code' => 'US',
        ]);

        $this->client = Client::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Acme Corporation',
            'email' => 'billing@acme.test',
        ]);

        $this->invoice = Invoice::create([
            'workspace_id' => $this->workspace->id,
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-2026-PORTAL1',
            'issue_date' => now(),
            'due_date' => now()->addDays(15),
            'subtotal_cents' => 10000,
            'total_cents' => 10000,
            'due_cents' => 10000,
            'is_pdf_password_protected' => true,
            'pdf_password_hash' => bcrypt('9876'),
            'public_secure_token' => 'sample_secure_token_random_hex_64_characters_long_for_customer_view',
            'portal_token_expires_at' => now()->addDays(30),
        ]);
    }

    /** @test */
    public function customer_portal_loads_securely_via_token_route(): void
    {
        $response = $this->get(route('invoices.portal', $this->invoice->public_secure_token));
        $response->assertStatus(200);
        $response->assertSee('INV-2026-PORTAL1');
        $response->assertSee('Acme Corporation');
    }

    /** @test */
    public function expired_portal_token_returns_410_gone(): void
    {
        $this->invoice->update(['portal_token_expires_at' => now()->subDay()]);

        $response = $this->get(route('invoices.portal', $this->invoice->public_secure_token));
        $response->assertStatus(410);
    }

    /** @test */
    public function three_incorrect_pin_attempts_locks_the_token_for_60_minutes(): void
    {
        $token = $this->invoice->public_secure_token;

        // Attempt 1: Failed PIN
        $res1 = $this->get(route('invoices.pdf.download', ['token' => $token, 'password' => '0000']));
        $res1->assertRedirect(route('invoices.pdf.challenge', $token));
        $this->assertStringContainsString('2 attempt(s) remaining', session('errors')->first('password'));

        // Attempt 2: Failed PIN
        $res2 = $this->get(route('invoices.pdf.download', ['token' => $token, 'password' => '1111']));
        $res2->assertRedirect(route('invoices.pdf.challenge', $token));
        $this->assertStringContainsString('1 attempt(s) remaining', session('errors')->first('password'));

        // Attempt 3: Failed PIN -> Document locked for 60 minutes
        $res3 = $this->get(route('invoices.pdf.download', ['token' => $token, 'password' => '2222']));
        $res3->assertRedirect(route('invoices.pdf.challenge', $token));
        $this->assertStringContainsString('blocked for 60 minutes', session('errors')->first('password'));

        // Refresh invoice model to verify lock timestamp
        $this->invoice->refresh();
        $this->assertTrue($this->invoice->isPinLocked());

        // Subsequent attempt with correct PIN is still blocked due to active lockout
        $res4 = $this->get(route('invoices.pdf.download', ['token' => $token, 'password' => '9876']));
        $res4->assertRedirect(route('invoices.pdf.challenge', $token));
        $this->assertStringContainsString('Access locked', session('errors')->first('password'));
    }
}
