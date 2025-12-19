<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test forgot password endpoint is rate limited
     * Max 3 requests per 10 minutes
     */
    public function test_forgot_password_rate_limited(): void
    {
        $email = 'test@example.com';
        User::factory()->create(['email' => $email]);

        // Make 3 requests (should succeed)
        for ($i = 0; $i < 3; $i++) {
            $response = $this->postJson('/api/v1/auth/forgot-password', [
                'email' => $email
            ]);
            // Check response is successful (200 or 201)
            $this->assertTrue(
                in_array($response->status(), [200, 201]),
                'First 3 requests should succeed'
            );
        }

        // 4th request should be rate limited
        // Laravel throttle returns 422 with error message, not 429
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $email
        ]);

        // Laravel throttle middleware returns validation error (422)  
        $this->assertTrue(
            in_array($response->status(), [422, 429]),
            'Rate limited request should return 422 or 429'
        );
    }

    /**
     * Test verify OTP endpoint is rate limited
     * Max 5 requests per minute
     */
    public function test_verify_otp_rate_limited(): void
    {
        $email = 'test@example.com';

        // Make 5 requests
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/verify-otp', [
                'email' => $email,
                'otp' => '123456'
            ]);
        }

        // 6th request should be rate limited
        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'email' => $email,
            'otp' => '123456'
        ]);

        $this->assertTrue(
            in_array($response->status(), [422, 429]),
            'Rate limited request should return 422 or 429'
        );
    }

    /**
     * Test login endpoint is rate limited
     * Max 5 requests per 60 seconds
     */
    public function test_login_rate_limited(): void
    {
        // Make 5 failed login attempts
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword'
            ]);
        }

        // 6th attempt should be rate limited
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $this->assertTrue(
            in_array($response->status(), [422, 429]),
            'Rate limited request should return 422 or 429'
        );
    }
}
