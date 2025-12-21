<?php

namespace Tests\Feature;

use App\Models\Employment;
use App\Models\Service;
use App\Models\User;
use App\Models\Workshop;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected $owner;
    protected $workshop;
    protected $staff;
    protected $staffUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create role
        Role::create(['name' => 'owner', 'guard_name' => 'web']);

        // Create owner and workshop
        $this->owner = User::factory()->create();
        $this->owner->assignRole('owner');
        
        $this->workshop = Workshop::factory()->create(['user_uuid' => $this->owner->id]);

        // Create staff
        $this->staffUser = User::factory()->create();
        $this->staff = Employment::factory()->create([
            'workshop_uuid' => $this->workshop->id,
            'user_uuid' => $this->staffUser->id,
            'status' => 'active',
        ]);
    }

    public function test_can_get_all_staff_performance()
    {
        // Create services assigned to staff
        Service::factory()->count(3)->create([
            'workshop_uuid' => $this->workshop->id,
            'assigned_to_user_id' => $this->staffUser->id,
            'status' => 'completed',
            'price' => 100000,
            'completed_at' => Carbon::now(),
        ]);

        Service::factory()->count(2)->create([
            'workshop_uuid' => $this->workshop->id,
            'assigned_to_user_id' => $this->staffUser->id,
            'status' => 'in progress',
        ]);

        Sanctum::actingAs($this->owner);
        
        $response = $this->getJson(route('api.owner.staff.performance.index', [
                'workshop_uuid' => $this->workshop->id,
                'range' => 'month'
            ]));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'staff_id',
                        'staff_name',
                        'metrics' => [
                            'total_jobs_completed',
                            'total_revenue',
                            'active_jobs'
                        ]
                    ]
                ]
            ]);

        $data = $response->json('data.0');
        $this->assertEquals($this->staffUser->id, $data['staff_id']);
        $this->assertEquals(3, $data['metrics']['total_jobs_completed']);
        $this->assertEquals(300000, $data['metrics']['total_revenue']);
        $this->assertEquals(2, $data['metrics']['active_jobs']);
    }

    public function test_can_get_individual_staff_performance()
    {
        // Create completed service
        Service::factory()->create([
            'workshop_uuid' => $this->workshop->id,
            'assigned_to_user_id' => $this->staffUser->id,
            'status' => 'completed',
            'price' => 150000,
            'completed_at' => Carbon::now(),
        ]);

        Sanctum::actingAs($this->owner);

        $response = $this->getJson(route('api.owner.staff.performance.show', [
                'user_id' => $this->staffUser->id,
                'workshop_uuid' => $this->workshop->id,
                'range' => 'month'
            ]));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'staff_id',
                    'metrics',
                    'completed_jobs'
                ]
            ]);

        $this->assertEquals(150000, $response->json('data.metrics.total_revenue'));
    }

    public function test_date_range_filtering()
    {
        // Service completed last month
        Service::factory()->create([
            'workshop_uuid' => $this->workshop->id,
            'assigned_to_user_id' => $this->staffUser->id,
            'status' => 'completed',
            'price' => 100000,
            'completed_at' => Carbon::now()->subMonth(),
        ]);

        // Service completed today
        Service::factory()->create([
            'workshop_uuid' => $this->workshop->id,
            'assigned_to_user_id' => $this->staffUser->id,
            'status' => 'completed',
            'price' => 200000,
            'completed_at' => Carbon::now(),
        ]);

        Sanctum::actingAs($this->owner);

        // Test 'today' range
        $response = $this->getJson(route('api.owner.staff.performance.index', [
                'workshop_uuid' => $this->workshop->id,
                'range' => 'today'
            ]));

        $data = $response->json('data.0');
        $this->assertEquals(200000, $data['metrics']['total_revenue']); // Only today's service

        // Test 'month' range (default) - should include today's service, but maybe not last month's depending on date
        // If today is 1st of month, subMonth is previous month.
        // If today is 5th, subMonth is 5th of previous month.
        // 'month' range is current month. So subMonth service should NOT be included.
        
        $responseMonth = $this->getJson(route('api.owner.staff.performance.index', [
                'workshop_uuid' => $this->workshop->id,
                'range' => 'month'
            ]));
            
        $dataMonth = $responseMonth->json('data.0');
        $this->assertEquals(200000, $dataMonth['metrics']['total_revenue']);
    }

    public function test_authorization_checks()
    {
        $otherUser = User::factory()->create();

        Sanctum::actingAs($otherUser);

        $response = $this->getJson(route('api.owner.staff.performance.index', [
                'workshop_uuid' => $this->workshop->id
            ]));

        $response->assertStatus(403);
    }
}
