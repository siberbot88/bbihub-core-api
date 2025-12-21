<?php

namespace Tests\Feature\Employment;

use App\Models\Employment;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmploymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure roles exist
        Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'sanctum']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'sanctum']);
        Role::firstOrCreate(['name' => 'mechanic', 'guard_name' => 'sanctum']);
    }

    /**
     * Helper: Create an owner with a workshop
     */
    private function createOwnerWithWorkshop(): array
    {
        $owner = User::factory()->create([
            'email' => 'owner@example.com',
            'password' => Hash::make('password'),
        ]);
        $owner->guard_name = 'sanctum';
        $owner->assignRole('owner');

        $workshop = Workshop::create([
            'user_uuid' => $owner->id,
            'name' => 'Test Workshop',
            'description' => 'A test workshop',
            'address' => '123 Test St',
            'phone' => '08123456789',
            'email' => 'workshop@example.com',
            'city' => 'Test City',
            'province' => 'Test Province',
            'country' => 'Indonesia',
            'postal_code' => '12345',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'maps_url' => 'https://maps.google.com',
            'opening_time' => '08:00',
            'closing_time' => '17:00',
            'operational_days' => 'Mon-Fri',
        ]);

        $token = $owner->createToken('test_token')->plainTextToken;

        return [$owner, $workshop, $token];
    }

    /* =================== CREATE EMPLOYEE TESTS =================== */

    public function test_owner_can_create_admin_employee()
    {
        [$owner, $workshop, $token] = $this->createOwnerWithWorkshop();

        $response = $this->withToken($token)->postJson(route('api.owner.employee.store'), [
            'name' => 'Admin User',
            'username' => 'adminuser',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'workshop_uuid' => $workshop->id,
            'specialist' => 'Management',
            'jobdesk' => 'Manage workshop operations',
            'status' => 'active',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'data' => [
                        'id',
                        'user_uuid',
                        'workshop_uuid',
                        'code',
                        'specialist',
                        'jobdesk',
                        'status',
                    ],
                    'email_sent',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'username' => 'adminuser',
        ]);

        $this->assertDatabaseHas('employments', [
            'workshop_uuid' => $workshop->id,
            'specialist' => 'Management',
        ]);
    }

    public function test_owner_can_create_mechanic_employee()
    {
        [$owner, $workshop, $token] = $this->createOwnerWithWorkshop();

        $response = $this->withToken($token)->postJson(route('api.owner.employee.store'), [
            'name' => 'Mechanic User',
            'username' => 'mechanicuser',
            'email' => 'mechanic@example.com',
            'role' => 'mechanic',
            'workshop_uuid' => $workshop->id,
            'specialist' => 'Engine Repair',
            'jobdesk' => 'Fix engines',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => 'mechanic@example.com',
        ]);
    }

    public function test_create_employee_validation_errors()
    {
        [$owner, $workshop, $token] = $this->createOwnerWithWorkshop();

        $response = $this->withToken($token)->postJson(route('api.owner.employee.store'), [
            'name' => '', // Required
            'username' => '', // Required
            'email' => 'not-an-email', // Invalid email
            'role' => 'invalid-role', // Invalid role
            'workshop_uuid' => 'not-a-uuid', // Invalid UUID
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'username', 'email', 'role', 'workshop_uuid']);
    }

    public function test_cannot_create_employee_for_other_owner_workshop()
    {
        [$owner, $workshop, $token] = $this->createOwnerWithWorkshop();

        // Create another owner's workshop
        $otherOwner = User::factory()->create();
        $otherOwner->guard_name = 'sanctum';
        $otherOwner->assignRole('owner');

        $otherWorkshop = Workshop::create([
            'user_uuid' => $otherOwner->id,
            'name' => 'Other Workshop',
            'description' => 'Another workshop',
            'address' => '456 Other St',
            'phone' => '08987654321',
            'email' => 'other@example.com',
            'city' => 'Other City',
            'province' => 'Other Province',
            'country' => 'Indonesia',
            'postal_code' => '54321',
            'latitude' => -6.300000,
            'longitude' => 106.900000,
            'maps_url' => 'https://maps.google.com',
            'opening_time' => '09:00',
            'closing_time' => '18:00',
            'operational_days' => 'Mon-Sat',
        ]);

        $response = $this->withToken($token)->postJson(route('api.owner.employee.store'), [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'role' => 'admin',
            'workshop_uuid' => $otherWorkshop->id, // Not owned by current user
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['workshop_uuid']);
    }

    /* =================== LIST EMPLOYEES TESTS =================== */

    public function test_owner_can_list_employees()
    {
        [$owner, $workshop, $token] = $this->createOwnerWithWorkshop();

        // Create employees
        $user1 = User::factory()->create();
        $user1->guard_name = 'sanctum';
        $user1->assignRole('admin');

        Employment::create([
            'user_uuid' => $user1->id,
            'workshop_uuid' => $workshop->id,
            'code' => 'ST00001',
            'specialist' => 'Admin',
            'status' => 'active',
        ]);

        $response = $this->withToken($token)->getJson(route('api.owner.employee.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'user_uuid',
                        'workshop_uuid',
                        'code',
                        'user',
                        'workshop',
                    ],
                ],
            ]);
    }

    public function test_owner_gets_empty_list_without_employees()
    {
        [$owner, $workshop, $token] = $this->createOwnerWithWorkshop();

        $response = $this->withToken($token)->getJson(route('api.owner.employee.index'));

        $response->assertStatus(200)
            ->assertJson([
                'data' => [],
            ]);
    }

    /* =================== SHOW EMPLOYEE TESTS =================== */

    public function test_owner_can_view_employee_details()
    {
        [$owner, $workshop, $token] = $this->createOwnerWithWorkshop();

        $user = User::factory()->create();
        $user->guard_name = 'sanctum';
        $user->assignRole('mechanic');

        $employment = Employment::create([
            'user_uuid' => $user->id,
            'workshop_uuid' => $workshop->id,
            'code' => 'ST00001',
            'specialist' => 'Engine',
            'status' => 'active',
        ]);

        $response = $this->withToken($token)->getJson(route('api.owner.employee.show', $employment));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'user',
                    'workshop',
                ],
            ]);
    }

    public function test_cannot_view_other_owner_employee()
    {
        [$owner, $workshop, $token] = $this->createOwnerWithWorkshop();

        // Create another owner's employee
        $otherOwner = User::factory()->create();
        $otherOwner->guard_name = 'sanctum';
        $otherOwner->assignRole('owner');

        $otherWorkshop = Workshop::create([
            'user_uuid' => $otherOwner->id,
            'name' => 'Other Workshop',
            'description' => 'Another workshop',
            'address' => '456 Other St',
            'phone' => '08987654321',
            'email' => 'other@example.com',
            'city' => 'Other City',
            'province' => 'Other Province',
            'country' => 'Indonesia',
            'postal_code' => '54321',
            'latitude' => -6.300000,
            'longitude' => 106.900000,
            'maps_url' => 'https://maps.google.com',
            'opening_time' => '09:00',
            'closing_time' => '18:00',
            'operational_days' => 'Mon-Sat',
        ]);

        $otherUser = User::factory()->create();
        $otherUser->guard_name = 'sanctum';
        $otherUser->assignRole('admin');

        $otherEmployment = Employment::create([
            'user_uuid' => $otherUser->id,
            'workshop_uuid' => $otherWorkshop->id,
            'code' => 'ST00002',
            'status' => 'active',
        ]);

        $response = $this->withToken($token)->getJson(route('api.owner.employee.show', $otherEmployment));

        $response->assertStatus(403);
    }

    /* =================== UPDATE EMPLOYEE TESTS =================== */

    public function test_owner_can_update_employee()
    {
        [$owner, $workshop, $token] = $this->createOwnerWithWorkshop();

        $user = User::factory()->create(['name' => 'Old Name']);
        $user->guard_name = 'sanctum';
        $user->assignRole('mechanic');

        $employment = Employment::create([
            'user_uuid' => $user->id,
            'workshop_uuid' => $workshop->id,
            'code' => 'ST00001',
            'specialist' => 'Old Specialist',
            'status' => 'active',
        ]);

        $response = $this->withToken($token)->putJson(route('api.owner.employee.update', $employment), [
            'name' => 'New Name',
            'specialist' => 'New Specialist',
            'jobdesk' => 'New Jobdesk',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
        ]);

        $this->assertDatabaseHas('employments', [
            'id' => $employment->id,
            'specialist' => 'New Specialist',
        ]);
    }

    public function test_owner_can_change_employee_role()
    {
        [$owner, $workshop, $token] = $this->createOwnerWithWorkshop();

        $user = User::factory()->create();
        $user->guard_name = 'sanctum';
        $user->assignRole('mechanic');

        $employment = Employment::create([
            'user_uuid' => $user->id,
            'workshop_uuid' => $workshop->id,
            'code' => 'ST00001',
            'status' => 'active',
        ]);

        $response = $this->withToken($token)->putJson(route('api.owner.employee.update', $employment), [
            'role' => 'admin',
        ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertTrue($user->hasRole('admin', 'sanctum'));
        $this->assertFalse($user->hasRole('mechanic', 'sanctum'));
    }

    public function test_cannot_update_other_owner_employee()
    {
        [$owner, $workshop, $token] = $this->createOwnerWithWorkshop();

        // Create another owner's employee
        $otherOwner = User::factory()->create();
        $otherOwner->guard_name = 'sanctum';
        $otherOwner->assignRole('owner');

        $otherWorkshop = Workshop::create([
            'user_uuid' => $otherOwner->id,
            'name' => 'Other Workshop',
            'description' => 'Another workshop',
            'address' => '456 Other St',
            'phone' => '08987654321',
            'email' => 'other@example.com',
            'city' => 'Other City',
            'province' => 'Other Province',
            'country' => 'Indonesia',
            'postal_code' => '54321',
            'latitude' => -6.300000,
            'longitude' => 106.900000,
            'maps_url' => 'https://maps.google.com',
            'opening_time' => '09:00',
            'closing_time' => '18:00',
            'operational_days' => 'Mon-Sat',
        ]);

        $otherUser = User::factory()->create();
        $otherUser->guard_name = 'sanctum';
        $otherUser->assignRole('admin');

        $otherEmployment = Employment::create([
            'user_uuid' => $otherUser->id,
            'workshop_uuid' => $otherWorkshop->id,
            'code' => 'ST00002',
            'status' => 'active',
        ]);

        $response = $this->withToken($token)->putJson(route('api.owner.employee.update', $otherEmployment), [
            'name' => 'Hacked Name',
        ]);

        $response->assertStatus(403);
    }

    /* =================== UPDATE STATUS TESTS =================== */

    public function test_owner_can_update_employee_status()
    {
        [$owner, $workshop, $token] = $this->createOwnerWithWorkshop();

        $user = User::factory()->create();
        $user->guard_name = 'sanctum';
        $user->assignRole('mechanic');

        $employment = Employment::create([
            'user_uuid' => $user->id,
            'workshop_uuid' => $workshop->id,
            'code' => 'ST00001',
            'status' => 'active',
        ]);

        $response = $this->withToken($token)->patchJson(
            route('api.owner.employee.updateStatus', $employment),
            ['status' => 'inactive']
        );

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'inactive']);

        $this->assertDatabaseHas('employments', [
            'id' => $employment->id,
            'status' => 'inactive',
        ]);
    }

    public function test_cannot_update_status_of_other_owner_employee()
    {
        [$owner, $workshop, $token] = $this->createOwnerWithWorkshop();

        // Create another owner's employee
        $otherOwner = User::factory()->create();
        $otherOwner->guard_name = 'sanctum';
        $otherOwner->assignRole('owner');

        $otherWorkshop = Workshop::create([
            'user_uuid' => $otherOwner->id,
            'name' => 'Other Workshop',
            'description' => 'Another workshop',
            'address' => '456 Other St',
            'phone' => '08987654321',
            'email' => 'other@example.com',
            'city' => 'Other City',
            'province' => 'Other Province',
            'country' => 'Indonesia',
            'postal_code' => '54321',
            'latitude' => -6.300000,
            'longitude' => 106.900000,
            'maps_url' => 'https://maps.google.com',
            'opening_time' => '09:00',
            'closing_time' => '18:00',
            'operational_days' => 'Mon-Sat',
        ]);

        $otherUser = User::factory()->create();
        $otherUser->guard_name = 'sanctum';
        $otherUser->assignRole('admin');

        $otherEmployment = Employment::create([
            'user_uuid' => $otherUser->id,
            'workshop_uuid' => $otherWorkshop->id,
            'code' => 'ST00002',
            'status' => 'active',
        ]);

        $response = $this->withToken($token)->patchJson(
            route('api.owner.employee.updateStatus', $otherEmployment),
            ['status' => 'inactive']
        );

        $response->assertStatus(403);
    }

    /* =================== DELETE EMPLOYEE TESTS =================== */

    public function test_owner_can_delete_employee()
    {
        [$owner, $workshop, $token] = $this->createOwnerWithWorkshop();

        $user = User::factory()->create();
        $user->guard_name = 'sanctum';
        $user->assignRole('mechanic');

        $employment = Employment::create([
            'user_uuid' => $user->id,
            'workshop_uuid' => $workshop->id,
            'code' => 'ST00001',
            'status' => 'active',
        ]);

        $userId = $user->id;
        $employmentId = $employment->id;

        $response = $this->withToken($token)->deleteJson(route('api.owner.employee.destroy', $employment));

        $response->assertStatus(204);

        // Verify both employment and user are deleted
        $this->assertDatabaseMissing('employments', ['id' => $employmentId]);
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    public function test_cannot_delete_other_owner_employee()
    {
        [$owner, $workshop, $token] = $this->createOwnerWithWorkshop();

        // Create another owner's employee
        $otherOwner = User::factory()->create();
        $otherOwner->guard_name = 'sanctum';
        $otherOwner->assignRole('owner');

        $otherWorkshop = Workshop::create([
            'user_uuid' => $otherOwner->id,
            'name' => 'Other Workshop',
            'description' => 'Another workshop',
            'address' => '456 Other St',
            'phone' => '08987654321',
            'email' => 'other@example.com',
            'city' => 'Other City',
            'province' => 'Other Province',
            'country' => 'Indonesia',
            'postal_code' => '54321',
            'latitude' => -6.300000,
            'longitude' => 106.900000,
            'maps_url' => 'https://maps.google.com',
            'opening_time' => '09:00',
            'closing_time' => '18:00',
            'operational_days' => 'Mon-Sat',
        ]);

        $otherUser = User::factory()->create();
        $otherUser->guard_name = 'sanctum';
        $otherUser->assignRole('admin');

        $otherEmployment = Employment::create([
            'user_uuid' => $otherUser->id,
            'workshop_uuid' => $otherWorkshop->id,
            'code' => 'ST00002',
            'status' => 'active',
        ]);

        $response = $this->withToken($token)->deleteJson(route('api.owner.employee.destroy', $otherEmployment));

        $response->assertStatus(403);

        // Verify employee still exists
        $this->assertDatabaseHas('employments', ['id' => $otherEmployment->id]);
    }
}
