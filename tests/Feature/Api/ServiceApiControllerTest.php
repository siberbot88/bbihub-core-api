<?php

namespace Tests\Feature\Api;

use App\Models\Employment;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ServiceApiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()['cache']->forget('spatie.permission.cache');
        
        Role::create(['name' => 'owner', 'guard_name' => 'sanctum']);
        Role::create(['name' => 'admin', 'guard_name' => 'sanctum']);
        Role::create(['name' => 'mechanic', 'guard_name' => 'sanctum']);
    }

    public function test_admin_can_list_services_with_pagination()
    {
        $workshop = Workshop::factory()->create();
        $user = User::factory()->create();
        $user->guard_name = 'sanctum';
        $user->assignRole('admin');
        
        Employment::factory()->create([
            'user_uuid' => $user->id,
            'workshop_uuid' => $workshop->id,
        ]);

        $mechanic = Employment::factory()->create([
            'workshop_uuid' => $workshop->id,
        ]);

        $customer = \App\Models\Customer::factory()->create();

        $vehicle = \App\Models\Vehicle::factory()->create([
            'customer_uuid' => $customer->id,
        ]);

        Service::factory()->count(20)->create([
            'workshop_uuid' => $workshop->id,
            'mechanic_uuid' => $mechanic->id,
            'customer_uuid' => $customer->id,
            'vehicle_uuid' => $vehicle->id,
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('api.services.index', ['per_page' => 10]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
                'meta' => ['current_page', 'last_page', 'total']
            ]);
            
        $this->assertEquals(10, count($response->json('data')));
        $this->assertEquals(20, $response->json('meta.total'));
    }

    public function test_admin_can_create_service()
    {
        $workshop = Workshop::factory()->create();
        $user = User::factory()->create();
        $user->guard_name = 'sanctum';
        $user->assignRole('admin');
        
        Employment::factory()->create([
            'user_uuid' => $user->id,
            'workshop_uuid' => $workshop->id,
        ]);

        $vehicle = Vehicle::factory()->create();

        $data = [
            'workshop_uuid' => $workshop->id,
            'vehicle_uuid' => $vehicle->id,
            'name' => 'Service Berkala',
            'description' => 'Ganti oli dan tune up',
            'status' => 'pending',
            'scheduled_date' => now()->addDay()->toIso8601String(),
        ];

        $response = $this->actingAs($user)
            ->postJson(route('api.services.store'), $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Service Berkala']);
            
        $this->assertDatabaseHas('services', ['name' => 'Service Berkala']);
    }

    public function test_admin_can_update_service_status()
    {
        $workshop = Workshop::factory()->create();
        $user = User::factory()->create();
        $user->guard_name = 'sanctum';
        $user->assignRole('admin');
        
        Employment::factory()->create([
            'user_uuid' => $user->id,
            'workshop_uuid' => $workshop->id,
        ]);

        $service = Service::factory()->create([
            'workshop_uuid' => $workshop->id,
            'mechanic_uuid' => null,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($user)
            ->putJson(route('api.services.update', $service->id), [
                'status' => 'accept'
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'accept']);
            
        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'status' => 'accept'
        ]);
    }
}
