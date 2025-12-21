<?php

namespace Tests\Feature\MobileAPI\Auth;

use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create roles needed for testing
        Role::create(['name' => 'owner', 'guard_name' => 'sanctum']);
        Role::create(['name' => 'admin', 'guard_name' => 'sanctum']);
        Role::create(['name' => 'mechanic', 'guard_name' => 'sanctum']);
    }

    /**
     * Test user registration successfully.
     */
    public function test_user_can_register()
    {
        $response = $this->postJson(route('api.register'), [
            'name' => 'Test Owner',
            'username' => 'testowner',
            'email' => 'owner@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'access_token',
                    'token_type',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'username',
                        'roles',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'owner@example.com',
            'username' => 'testowner',
        ]);
    }

    /**
     * Test registration validation errors.
     */
    public function test_registration_validation_errors()
    {
        $response = $this->postJson(route('api.register'), [
            'name' => '', // Required
            'username' => '', // Required
            'email' => 'not-an-email', // Invalid email
            'password' => 'short', // Too short (assuming default is 8)
            'password_confirmation' => 'different',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'username', 'email', 'password']);
    }

    /**
     * Test complete flow: Register -> Create Workshop -> Create Document.
     */
    public function test_complete_registration_flow()
    {
        // 1. Register User
        $registerResponse = $this->postJson(route('api.register'), [
            'name' => 'Flow Owner',
            'username' => 'flowowner',
            'email' => 'flow@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $registerResponse->assertStatus(201);
        $token = $registerResponse->json('data.access_token');

        // 2. Create Workshop
        $workshopData = [
            'name' => 'Flow Workshop',
            'description' => 'A test workshop for flow',
            'address' => '123 Test St',
            'phone' => '08123456789',
            'email' => 'workshop@example.com',
            'city' => 'Test City',
            'province' => 'Test Province',
            'country' => 'Test Country',
            'postal_code' => '12345',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'maps_url' => 'https://maps.google.com',
            'opening_time' => '08:00',
            'closing_time' => '17:00',
            'operational_days' => 'Mon-Fri',
        ];

        $workshopResponse = $this->withToken($token)
            ->postJson(route('api.owner.workshops.store'), $workshopData);

        $workshopResponse->assertStatus(201)
            ->assertJsonFragment(['name' => 'Flow Workshop']);

        $workshopId = $workshopResponse->json('data.id');
        $this->assertNotNull($workshopId);

        // 3. Create Workshop Document
        $documentData = [
            'workshop_uuid' => $workshopId,
            'nib' => '1234567890123',
            'npwp' => '12.345.678.9-012.345',
        ];

        $documentResponse = $this->withToken($token)
            ->postJson(route('api.owner.documents.store'), $documentData);

        $documentResponse->assertStatus(201)
            ->assertJsonFragment(['nib' => '1234567890123']);

        $this->assertDatabaseHas('workshop_documents', [
            'workshop_uuid' => $workshopId,
            'nib' => '1234567890123',
        ]);
    }
}

