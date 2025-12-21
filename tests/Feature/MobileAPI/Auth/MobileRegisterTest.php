<?php

namespace Tests\Feature\MobileAPI\Auth;

use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MobileRegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'sanctum']);
    }

    /**
     * Test successful mobile registration
     */
    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test Owner',
            'username' => 'testowner',
            'email' => 'newowner@example.com',
            'password' => 'NewOwner@2024!',
            'password_confirmation' => 'NewOwner@2024!',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'access_token',
                    'token_type',
                    'user' => ['id', 'name', 'email'],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newowner@example.com',
            'name' => 'Test Owner',
        ]);
    }

    /**
     * Test registration validation errors
     */
    public function test_registration_requires_all_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /**
     * Test password must meet policy requirements
     */
    public function test_registration_enforces_password_policy(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test email must be unique
     */
    public function test_registration_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'New User',
            'username' => 'newuser',
            'email' => 'existing@example.com',
            'password' => 'ValidP@ssw0rd!',
            'password_confirmation' => 'ValidP@ssw0rd!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test registration creates user with owner role
     */
    public function test_registered_user_gets_owner_role(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Business Owner',
            'username' => 'bizowner',
            'email' => 'business@example.com',
            'password' => 'UniqueBizOwner#2024$XyZ',
            'password_confirmation' => 'UniqueBizOwner#2024$XyZ',
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'business@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('owner'));
    }

    /**
     * Test password confirmation must match
     */
    public function test_registration_requires_password_confirmation(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'username' => 'testuser2',
            'email' => 'test@example.com',
            'password' => 'ValidP@ssw0rd!',
            'password_confirmation' => 'DifferentP@ssw0rd!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
