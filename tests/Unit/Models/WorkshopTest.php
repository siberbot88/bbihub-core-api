<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Workshop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WorkshopTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test workshop belongs to user
     */
    public function test_workshop_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $workshop = Workshop::factory()->create(['user_uuid' => $user->id]);

        $this->assertInstanceOf(User::class, $workshop->owner);
        $this->assertEquals($user->id, $workshop->owner->id);
    }

    /**
     * Test workshop has required attributes
     */
    public function test_workshop_has_required_attributes(): void
    {
        $workshop = Workshop::factory()->create([
            'name' => 'Test Workshop',
            'address' => '123 Test St',
            'phone' => '08123456789',
        ]);

        $this->assertEquals('Test Workshop', $workshop->name);
        $this->assertEquals('123 Test St', $workshop->address);
        $this->assertEquals('08123456789', $workshop->phone);
    }

    /**
     * Test workshop status enum
     */
    public function test_workshop_status_can_be_set(): void
    {
        $workshop = Workshop::factory()->create(['status' => 'active']);

        $this->assertEquals('active', $workshop->status);
    }
}
