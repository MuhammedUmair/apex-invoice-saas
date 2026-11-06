<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected Workspace $workspace;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->workspace = Workspace::create([
            'name' => 'Security Test Workspace',
            'slug' => 'security-test',
            'currency' => 'USD',
            'country_code' => 'US',
        ]);
        $this->user = User::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Sec Admin',
            'email' => 'sec@test.com',
            'password' => bcrypt('StrongPass123!'),
            'role' => 'owner',
            'email_verified_at' => now(),
        ]);
    }

    /** @test */
    public function sql_injection_payloads_in_search_and_filters_are_sanitized(): void
    {
        $this->actingAs($this->user);

        $payload = "' OR '1'='1' -- ";
        $response = $this->get(route('invoices.index', ['status' => $payload]));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('invoices', ['status' => $payload]);
    }

    /** @test */
    public function malicious_executable_file_uploads_are_strictly_rejected(): void
    {
        $this->actingAs($this->user);
        Storage::fake('public');

        $maliciousFile = UploadedFile::fake()->create('exploit.php', 100, 'application/x-php');

        $response = $this->post(route('settings.update'), [
            'name' => 'Updated Workspace',
            'country_code' => 'US',
            'currency' => 'USD',
            'language' => 'en',
            'brand_color' => '#dc2626',
            'secondary_color' => '#0f172a',
            'logo' => $maliciousFile,
        ]);

        $response->assertSessionHasErrors('logo');
    }

    /** @test */
    public function malicious_svg_with_javascript_is_disinfected_or_rejected(): void
    {
        $this->actingAs($this->user);

        $maliciousSvg = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert("XSS")</script><circle cx="50" cy="50" r="40"/></svg>';
        $tempPath = tempnam(sys_get_temp_dir(), 'test_svg');
        file_put_contents($tempPath, $maliciousSvg);

        $file = new UploadedFile($tempPath, 'malicious.svg', 'image/svg+xml', null, true);

        $response = $this->post(route('settings.update'), [
            'name' => 'Updated Workspace',
            'country_code' => 'US',
            'currency' => 'USD',
            'language' => 'en',
            'brand_color' => '#dc2626',
            'secondary_color' => '#0f172a',
            'logo' => $file,
        ]);

        $response->assertSessionHasErrors('logo');
        @unlink($tempPath);
    }

    /** @test */
    public function automated_security_scanner_user_agents_are_blocked_by_firewall(): void
    {
        $scanners = ['sqlmap/1.5', 'nikto/2.1', 'Acunetix-Scanner'];

        foreach ($scanners as $scanner) {
            $response = $this->withHeaders(['User-Agent' => $scanner])->get('/');
            $response->assertStatus(403);
        }
    }
}
