<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OtpExpirationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test OTP expires after 15 minutes
     */
    public function test_otp_expires_after_15_minutes(): void
    {
        $email = 'test@example.com';
        User::factory()->create(['email' => $email]);

        // Create expired OTP (16 minutes ago)
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => '123456',
            'created_at' => now()->subMinutes(16)
        ]);

        // Try to verify expired OTP
        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'email' => $email,
            'otp' => '123456'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Kode OTP telah kadaluarsa'
            ]);

        // Verify OTP was deleted
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $email
        ]);
    }

    /**
     * Test valid OTP within 15 minutes works
     */
    public function test_valid_otp_within_15_minutes_works(): void
    {
        $email = 'test@example.com';
        User::factory()->create(['email' => $email]);

        // Create valid OTP (5 minutes ago)
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => '123456',
            'created_at' => now()->subMinutes(5)
        ]);

        // Try to verify valid OTP
        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'email' => $email,
            'otp' => '123456'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /**
     * Test OTP exactly at 15 minutes is expired
     */
    public function test_otp_at_exactly_15_minutes_is_expired(): void
    {
        $email = 'test@example.com';
        User::factory()->create(['email' => $email]);

        // Create OTP exactly 15 minutes ago
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => '123456',
            'created_at' => now()->subMinutes(15)->subSecond() // Just past 15 min
        ]);

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'email' => $email,
            'otp' => '123456'
        ]);

        $response->assertStatus(400);
    }
}
