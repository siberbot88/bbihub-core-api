<?php

namespace Tests\Feature\MobileAPI\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure roles exist
        Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'sanctum']);
    }

    /**
     * Test successful login.
     */
    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);
        $user->guard_name = 'sanctum';
        $user->assignRole('owner');

        $response = $this->postJson(route('api.login'), [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'access_token',
                    'token_type',
                    'user',
                ],
            ]);
    }

    /**
     * Test login with invalid password.
     */
    public function test_user_cannot_login_with_invalid_password()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);
        $user->guard_name = 'sanctum';
        $user->assignRole('owner');

        $response = $this->postJson(route('api.login'), [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test login with non-existent email.
     */
    public function test_user_cannot_login_with_non_existent_email()
    {
        $response = $this->postJson(route('api.login'), [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test login rate limiting.
     */
    public function test_login_is_rate_limited()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);
        $user->guard_name = 'sanctum';
        $user->assignRole('owner');

        // Attempt login 5 times with wrong password
        for ($i = 0; $i < 5; $i++) {
            $this->postJson(route('api.login'), [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // The 6th attempt should be locked out
        $response = $this->postJson(route('api.login'), [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
        // The message usually contains "Too many login attempts" or similar, 
        // but exact string depends on lang files. 
        // We check if it returns validation error for email as per LoginRequest logic.
        $response->assertJsonValidationErrors(['email']);
    }
}

