<?php

namespace Tests\Feature\Security;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test password change creates audit log
     */
    public function test_password_change_creates_audit_log(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('OldPass123!')
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/auth/change-password', [
                'current_password' => 'OldPass123!',
                'new_password' => 'NewSecure123!',
                'new_password_confirmation' => 'NewSecure123!'
            ]);

        // Verify audit log was created
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event' => 'password_changed'
        ]);

        $log = AuditLog::where('user_id', $user->id)
            ->where('event', 'password_changed')
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    /**
     * Test login creates audit log
     */
    public function test_login_creates_audit_log(): void
    {
        // Reset cached roles
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Ensure role exists for login perms
        if (!\Spatie\Permission\Models\Role::where('name', 'owner')->exists()) {
            \Spatie\Permission\Models\Role::create(['name' => 'owner', 'guard_name' => 'web']);
            \Spatie\Permission\Models\Role::create(['name' => 'owner', 'guard_name' => 'sanctum']);
        }

        $user = User::factory()->create([
            'password' => bcrypt('TestPass123!')
        ]);
        $user->assignRole('owner');

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'TestPass123!'
        ]);

        $response->assertOk();

        // Check if audit log was created (flexible check)
        $logExists = AuditLog::where('user_id', $user->id)
            ->where('event', 'login')
            ->exists();

        $this->assertTrue($logExists, 'Login audit log should be created');
    }

    /**
     * Test verified manual creation (Debug test)
     */
    public function test_manual_log_creation_works(): void
    {
        $user = User::factory()->create();

        $log = AuditLog::log('manual_test_event', $user);

        $this->assertNotNull($log, 'AuditLog::log returned null');

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'manual_test_event',
            'user_id' => $user->id
        ]);
    }

    /**
     * Test logout creates audit log
     */
    public function test_logout_creates_audit_log(): void
    {
        // Reset cached roles
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        if (!\Spatie\Permission\Models\Role::where('name', 'owner')->exists()) {
            \Spatie\Permission\Models\Role::create(['name' => 'owner', 'guard_name' => 'web']);
            \Spatie\Permission\Models\Role::create(['name' => 'owner', 'guard_name' => 'sanctum']);
        }

        $user = User::factory()->create([
            'password' => bcrypt('TestPass123!')
        ]);
        $user->assignRole('owner');

        // 1. Login to get Real Token
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'TestPass123!'
        ]);
        $token = $loginResponse->json('data.access_token');
        $this->assertNotNull($token, 'Login failed to return token in logout test');

        // 2. Logout using the token
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/logout');

        $response->assertOk();

        // 3. Verify audit log
        $logExists = AuditLog::where('user_id', $user->id)
            ->where('event', 'logout')
            ->exists();

        $this->assertTrue($logExists, 'Logout audit log should be created');
    }

    /**
     * Test audit log contains user agent and IP
     */
    public function test_audit_log_contains_metadata(): void
    {
        // Reset cached roles
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        if (!\Spatie\Permission\Models\Role::where('name', 'owner')->exists()) {
            \Spatie\Permission\Models\Role::create(['name' => 'owner', 'guard_name' => 'web']);
            \Spatie\Permission\Models\Role::create(['name' => 'owner', 'guard_name' => 'sanctum']);
        }

        $user = User::factory()->create([
            'password' => bcrypt('TestPass123!')
        ]);
        $user->assignRole('owner');

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'TestPass123!'
        ], [
            'User-Agent' => 'TestAgent/1.0'
        ]);

        $log = AuditLog::where('user_id', $user->id)
            ->where('event', 'login')
            ->first();

        $this->assertNotNull($log, 'Login audit log should exist for metadata check');
        $this->assertNotNull($log->ip_address, 'IP address should not be null');
        $this->assertNotNull($log->user_agent, 'User agent should not be null');
        $this->assertEquals($user->email, $log->user_email);
    }

    /**
     * Test audit log can be filtered by event
     */
    public function test_audit_logs_can_be_filtered(): void
    {
        $user = User::factory()->create();

        // Create different event types via Model directly (bypass controller)
        AuditLog::log('login', $user);
        AuditLog::log('password_changed', $user);

        // Filter by event
        $loginLogs = AuditLog::where('event', 'login')->get();
        $passwordLogs = AuditLog::where('event', 'password_changed')->get();

        $this->assertCount(1, $loginLogs);
        $this->assertCount(1, $passwordLogs);
    }
}
