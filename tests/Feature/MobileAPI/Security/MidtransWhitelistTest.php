<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use Illuminate\Support\Facades\Config;

class MidtransWhitelistTest extends TestCase
{
    /**
     * Test allowed IP can access webhook
     */
    public function test_allowed_ip_can_access_webhook(): void
    {
        // Mocking config
        Config::set('services.midtrans.allowed_ips', ['127.0.0.1', '34.101.66.130']);

        $response = $this->withServerVariables(['REMOTE_ADDR' => '34.101.66.130'])
            ->postJson(route('webhook.midtrans'), [
                'transaction_status' => 'capture',
                'order_id' => 'ORDER-123',
                'gross_amount' => 10000
            ]);

        // Should NOT be 403 Forbidden
        // It might be 200 or 404/500 depending on controller logic (we just care about middleware passing)
        $this->assertNotEquals(403, $response->status());
    }

    /**
     * Test blocked IP cannot access webhook
     */
    public function test_blocked_ip_cannot_access_webhook(): void
    {
        // Mocking config
        Config::set('services.midtrans.allowed_ips', ['127.0.0.1']);

        $response = $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.5'])
            ->postJson(route('webhook.midtrans'), [
                'transaction_status' => 'capture',
                'order_id' => 'ORDER-123',
                'gross_amount' => 10000
            ]);

        if ($response->status() !== 403) {
            dump($response->status());
            dump($response->content());
        }

        $response->assertStatus(403)
            ->assertJson(['message' => 'Unauthorized IP address']);
    }

    /**
     * Test local environment bypass
     */
    public function test_local_env_bypasses_whitelist(): void
    {
        // Force local env
        app()->detectEnvironment(function () {
            return 'local';
        });

        $response = $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.5'])
            ->postJson(route('webhook.midtrans'), [
                'transaction_status' => 'capture',
                'order_id' => 'ORDER-123',
                'gross_amount' => 10000
            ]);

        $this->assertNotEquals(403, $response->status());
    }
}
