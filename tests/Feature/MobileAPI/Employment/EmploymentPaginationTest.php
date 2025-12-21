<?php

namespace Tests\Feature;

use App\Models\Employment;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EmploymentPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_get_paginated_employees()
    {
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $owner = User::factory()->create();
        $owner->assignRole($role);
        $workshop = Workshop::factory()->create(['user_uuid' => $owner->id]);

        // Create 20 employees
        Employment::factory()->count(20)->create(['workshop_uuid' => $workshop->id]);

        $this->actingAs($owner);

        $response = $this->getJson('/api/v1/owners/employee?per_page=10');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'data',
                    'current_page',
                    'total',
                ]
            ])
            ->assertJsonCount(10, 'data.data');
    }

    public function test_owner_can_search_employees()
    {
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $owner = User::factory()->create();
        $owner->assignRole($role);
        $workshop = Workshop::factory()->create(['user_uuid' => $owner->id]);

        // Create specific employee
        $user1 = User::factory()->create(['name' => 'UniqueNameSearch']);
        Employment::factory()->create(['workshop_uuid' => $workshop->id, 'user_uuid' => $user1->id]);

        // Create random employees
        Employment::factory()->count(5)->create(['workshop_uuid' => $workshop->id]);

        $this->actingAs($owner);

        $response = $this->getJson('/api/v1/owners/employee?search=UniqueName');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonFragment(['name' => 'UniqueNameSearch']);
    }
}
