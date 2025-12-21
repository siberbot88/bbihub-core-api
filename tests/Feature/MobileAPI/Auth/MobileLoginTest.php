<?php

namespace Tests\Feature\MobileAPI\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MobileLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'sanctum']);
    }

    /**
     * Test successful mobile login with valid credentials
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'owner@example.com',
            'password' => Hash::make('TestP@ssw0rd2024!'),
            'email_verified_at' => now(),
        ]);
        $user->guard_name = 'sanctum';
        $user->assignRole('owner');

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'owner@example.com',
            'password' => 'TestP@ssw0rd2024!',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'access_token',
                    'token_type',
                    'user' => ['id', 'name', 'email'],
                ],
            ]);

        $this->assertNotNull($response->json('data.access_token'));
        $this->assertEquals('Bearer', $response->json('data.token_type'));
    }

    /**
     * Test login fails with invalid credentials
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'owner@example.com',
            'password' => Hash::make('TestP@ssw0rd2024!'),
        ]);
        $user->guard_name = 'sanctum';
        $user->assignRole('owner');

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'owner@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test login requires email and password
     */
    public function test_login_validation_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Test login with unverified email (if email verification required)
     */
    public function test_login_works_with_unverified_email(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'unverified@example.com',
            'password' => Hash::make('TestP@ssw0rd2024!'),
        ]);
        $user->guard_name = 'sanctum';
        $user->assignRole('owner');

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'unverified@example.com',
            'password' => 'TestP@ssw0rd2024!',
        ]);

        // Should allow login but may return email_verified flag
        $response->assertStatus(200);
    }
}
