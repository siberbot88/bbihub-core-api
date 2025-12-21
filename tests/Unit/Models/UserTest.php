<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can be assigned roles
     */
    public function test_user_can_be_assigned_role(): void
    {
        $role = Role::create(['name' => 'owner', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->guard_name = 'sanctum';
        $user->assignRole($role);

        $this->assertTrue($user->hasRole('owner'));
    }

    /**
     * Test user relationship with workshops
     */
    public function test_user_has_workshops_relationship(): void
    {
        $user = User::factory()->create();
        $workshop = Workshop::factory()->create(['user_uuid' => $user->id]);

        $this->assertCount(1, $user->workshops);
        $this->assertEquals($workshop->id, $user->workshops->first()->id);
    }

    /**
     * Test user email verification
     */
    public function test_user_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create();

        $this->assertFalse($user->hasVerifiedEmail());

        $user->markEmailAsVerified();

        $this->assertTrue($user->hasVerifiedEmail());
    }

    /**
     * Test user password hashing
     */
    public function test_user_password_is_hashed(): void
    {
        $user = User::factory()->create([
            'password' => 'TestP@ssw0rd2024!'
        ]);

        $this->assertNotEquals('TestP@ssw0rd2024!', $user->password);
        $this->assertTrue(\Hash::check('TestP@ssw0rd2024!', $user->password));
    }
}
