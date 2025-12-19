<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordPolicyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test weak password is rejected
     */
    public function test_weak_password_rejected(): void
    {
        $weakPasswords = [
            '123456',           // Too short, no complexity
            'password',         // Common, no numbers
            'abc123',           // Too short, no uppercase/symbols
            'Password',         // No numbers/symbols
            'Pass123',          // No symbols
        ];

        foreach ($weakPasswords as $password) {
            $response = $this->postJson('/api/v1/auth/register', [
                'name' => 'Test User',
                'email' => 'test' . rand() . '@example.com',
                'username' => 'testuser' . rand(),
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
        $strongPasswords = [
            'SecureP@ss123',
            'MyP@ssw0rd!',
            'Str0ng#Pass',
            'C0mplex!Pass',
        ];

        foreach ($strongPasswords as $password) {
            $response = $this->postJson('/api/v1/auth/register', [
                'name' => 'Test User',
                'email' => 'test' . rand() . '@example.com',
                'username' => 'testuser' . rand(),
                'password' => $password,
                'password_confirmation' => $password,
                'role' => 'owner'
            ]);

            $response->assertStatus(201);
        }
    }

    /**
     * Test password must be at least 8 characters
     */
    public function test_password_minimum_length():void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'username' => 'testuser',
            'password' => 'Pass1!', // Only 6 characters
            'password_confirmation' => 'Pass1!',
            'role' => 'owner'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    /**
     * Test password must contain mixed case
     */
    public function test_password_must_have_mixed_case(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'username' => 'testuser',
            'password' => 'password123!', // No uppercase
            'password_confirmation' => 'password123!',
            'role' => 'owner'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    /**
     * Test password must contain numbers
     */
    public function test_password_must_have_numbers(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'username' => 'testuser',
            'password' => 'Password!', // No numbers
            'password_confirmation' => 'Password!',
            'role' => 'owner'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    /**
     * Test password must contain symbols
     */
    public function test_password_must_have_symbols(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'username' => 'testuser',
            'password' => 'Password123', // No symbols
            'password_confirmation' => 'Password123',
            'role' => 'owner'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }
}
