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
        $user = User::factory()->create([
            'password' => bcrypt('TestPass123!')
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'TestPass123!'
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event' => 'login'
        ]);
    }

    /**
     * Test logout creates audit log
     */
    public function test_logout_creates_audit_log(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/auth/logout');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event' => 'logout'
        ]);
    }

    /**
     * Test audit log contains user agent and IP
     */
    public function test_audit_log_contains_metadata(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('TestPass123!')
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'TestPass123!'
        ], [
            'User-Agent' => 'TestAgent/1.0'
        ]);

        $log = AuditLog::where('user_id', $user->id)
            ->where('event', 'login')
            ->first();

        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
        $this->assertEquals($user->email, $log->user_email);
    }

    /**
     * Test audit log can be filtered by event
     */
    public function test_audit_logs_can_be_filtered(): void
    {
        $user = User::factory()->create();

        // Create different event types
        AuditLog::create([
            'user_id' => $user->id,
            'user_email' => $user->email,
            'event' => 'login',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test'
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'user_email' => $user->email,
            'event' => 'password_changed',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test'
        ]);

        // Filter by event
        $loginLogs = AuditLog::where('event', 'login')->get();
        $passwordLogs = AuditLog::where('event', 'password_changed')->get();

        $this->assertCount(1, $loginLogs);
        $this->assertCount(1, $passwordLogs);
    }
}
