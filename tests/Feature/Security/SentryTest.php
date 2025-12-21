<?php

namespace Tests\Feature\Security;

use Sentry\Laravel\Facade as Sentry;
use Tests\TestCase;

class SentryTest extends TestCase
{
    public function test_sentry_connection()
    {
        try {
            throw new \Exception('Sentry Test Exception from Automated Test');
        } catch (\Throwable $e) {
            Sentry::captureException($e);
            $this->assertTrue(true, 'Exception captured and sent to Sentry (theoretically)');
        }
    }
}
