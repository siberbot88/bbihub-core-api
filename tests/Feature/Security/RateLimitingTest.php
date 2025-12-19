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

        // Make 3 requests
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/v1/auth/forgot-password', ['email' => $email]);
        }

        // 4th request should show rate limit error
        $response = $this->postJson('/api/v1/auth/forgot-password', ['email' => $email]);

        // Check response contains rate limit message
        $json = $response->json();
        $errorText = json_encode($json);

        $this->assertTrue(
            str_contains($errorText, 'Terlalu banyak') ||
            str_contains($errorText, 'Too many') ||
            $response->status() === 429,
            'Should be rate limited after 3 attempts'
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

        // 6th request should show rate limit error
        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'email' => $email,
            'otp' => '123456'
        ]);

        $json = $response->json();
        $errorText = json_encode($json);

        $this->assertTrue(
            str_contains($errorText, 'Terlalu banyak') ||
            str_contains($errorText, 'Too many') ||
            $response->status() === 429,
            'Should be rate limited after 5 attempts'
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

        // 6th attempt should show rate limit error
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $json = $response->json();
        $errorText = json_encode($json);

        $this->assertTrue(
            str_contains($errorText, 'Terlalu banyak') ||
            str_contains($errorText, 'Too many') ||
            $response->status() === 429,
            'Should be rate limited after 5 attempts'
        );
    }
}
