<?php

namespace Tests\Feature\Security;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Seed roles
        foreach (['owner', 'admin', 'customer'] as $role) {
            if (!\Spatie\Permission\Models\Role::where('name', $role)->exists()) {
                \Spatie\Permission\Models\Role::create(['name' => $role, 'guard_name' => 'web']);
                \Spatie\Permission\Models\Role::create(['name' => $role, 'guard_name' => 'sanctum']);
            }
        }
    }

    /**
     * Test Owner A cannot update Workshop B
     */
    public function test_owner_cannot_update_others_workshop(): void
    {
        $ownerA = User::factory()->create();
        $ownerA->assignRole('owner');

        $ownerB = User::factory()->create();
        $ownerB->assignRole('owner');

        // Create Workshop owned by B
        $workshopB = Workshop::factory()->create(['user_uuid' => $ownerB->id]);

        $response = $this->actingAs($ownerA, 'sanctum')
            ->putJson("/api/v1/owners/workshops/{$workshopB->id}", [
                'name' => 'Hacked Workshop'
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test Owner can update OWN workshop
     */
    public function test_owner_can_update_own_workshop(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $workshop = Workshop::factory()->create(['user_uuid' => $owner->id]);

        $response = $this->actingAs($owner, 'sanctum')
            ->putJson("/api/v1/owners/workshops/{$workshop->id}", [
                'name' => 'Updated Workshop Name'
            ]);

        $response->assertStatus(200);
    }

    /**
     * Test Admin can view any vehicle
     */
    public function test_admin_can_access_any_vehicle(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create vehicle with default factory (auto-created customer)
        $vehicle = Vehicle::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/admins/vehicles/{$vehicle->id}");

        $response->assertStatus(200);
    }
}
