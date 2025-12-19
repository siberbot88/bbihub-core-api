<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PasswordPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Seed roles for both guards to ensure compatibility
        \Spatie\Permission\Models\Role::create(['name' => 'owner', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::create(['name' => 'owner', 'guard_name' => 'sanctum']);
    }

    /**
     * Test weak passwords are rejected
     */
    public function test_weak_password_rejected(): void
    {
        // Define list of weak passwords
        $weakPasswords = [
            'weak',          // Too short
            'no_number',     // Missing number
            'no_uppercase',  // Missing uppercase
            'no_symbol',     // Missing symbol
        ];

        foreach ($weakPasswords as $password) {
            $user = User::factory()->make();

            $response = $this->postJson('/api/v1/auth/register', [
                'name' => $user->name,
                'username' => 'testuser' . Str::random(8),
                'email' => $user->email,
                'password' => $password,
                'password_confirmation' => $password,
                'role' => 'owner'
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors('password');
        }
    }

    /**
     * Test strong password is accepted
     */
    public function test_strong_password_accepted(): void
    {
        $user = User::factory()->make();
        $password = 'StrongP@ssw0rd123!';

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => $user->name,
            'username' => 'testuser' . Str::random(8),
            'email' => $user->email,
            'password' => $password,
            'password_confirmation' => $password,
            'role' => 'owner'
        ]);

        if ($response->status() !== 201) {
            dump('Strong password registration failed');
            dump($response->json());
        }

        $response->assertStatus(201);
    }

    /**
     * Test minimum length requirement
     */
    public function test_password_min_length(): void
    {
        $user = User::factory()->make();
        $password = 'Sh0rt!';

        $this->postJson('/api/v1/auth/register', [
            'name' => $user->name,
            'username' => 'testuser' . Str::random(8),
            'email' => $user->email,
            'password' => $password,
            'password_confirmation' => $password,
            'role' => 'owner'
        ])->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    /**
     * Test symbol requirement
     */
    public function test_password_requires_symbol(): void
    {
        $user = User::factory()->make();
        $password = 'NoSymbol123';

        $this->postJson('/api/v1/auth/register', [
            'name' => $user->name,
            'username' => 'testuser' . Str::random(8),
            'email' => $user->email,
            'password' => $password,
            'password_confirmation' => $password,
            'role' => 'owner'
        ])->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    /**
     * Test number requirement
     */
    public function test_password_requires_number(): void
    {
        $user = User::factory()->make();
        $password = 'NoNumber!';

        $this->postJson('/api/v1/auth/register', [
            'name' => $user->name,
            'username' => 'testuser' . Str::random(8),
            'email' => $user->email,
            'password' => $password,
            'password_confirmation' => $password,
            'role' => 'owner'
        ])->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    /**
     * Test uppercase requirement
     */
    public function test_password_requires_uppercase(): void
    {
        $user = User::factory()->make();
        $password = 'lowercase123!';

        $this->postJson('/api/v1/auth/register', [
            'name' => $user->name,
            'username' => 'testuser' . Str::random(8),
            'email' => $user->email,
            'password' => $password,
            'password_confirmation' => $password,
            'role' => 'owner'
        ])->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }
}
