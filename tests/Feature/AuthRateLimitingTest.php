<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthRateLimitingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('login_attempt:lockout@test.com|127.0.0.1');

        $workspace = Workspace::create([
            'name' => 'Lockout Corp',
            'slug' => 'lockout-corp',
            'currency' => 'USD',
            'country_code' => 'US',
        ]);

        $this->user = User::create([
            'workspace_id' => $workspace->id,
            'name' => 'Lockout User',
            'email' => 'lockout@test.com',
            'password' => bcrypt('CorrectPassword123!'),
            'role' => 'owner',
            'email_verified_at' => now(),
        ]);
    }

    /** @test */
    public function three_incorrect_password_attempts_trigger_a_60_minute_login_lockout(): void
    {
        // Attempt 1: Failed
        $res1 = $this->post(route('login.post'), [
            'email' => 'lockout@test.com',
            'password' => 'WrongPass1',
        ]);
        $res1->assertSessionHasErrors('email');
        $this->assertStringContainsString('2 attempt(s) remaining', session('errors')->first('email'));

        // Attempt 2: Failed
        $res2 = $this->post(route('login.post'), [
            'email' => 'lockout@test.com',
            'password' => 'WrongPass2',
        ]);
        $res2->assertSessionHasErrors('email');
        $this->assertStringContainsString('1 attempt(s) remaining', session('errors')->first('email'));

        // Attempt 3: Failed -> Locks account for 60 minutes
        $res3 = $this->post(route('login.post'), [
            'email' => 'lockout@test.com',
            'password' => 'WrongPass3',
        ]);
        $res3->assertSessionHasErrors('email');
        $this->assertStringContainsString('blocked for 60 minutes', session('errors')->first('email'));

        // Attempt 4: Even with correct password, login must be blocked (HTTP 429)
        $res4 = $this->post(route('login.post'), [
            'email' => 'lockout@test.com',
            'password' => 'CorrectPassword123!',
        ]);
        $res4->assertStatus(429);
        $this->assertStringContainsString('Your login is blocked', session('errors')->first('email'));
    }
}
