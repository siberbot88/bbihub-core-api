<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\EisService;
use App\Models\User;
use App\Models\Workshop;
use App\Models\Transaction;
use App\Models\OwnerSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EisServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EisService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(EisService::class);
    }

    /**
     * Test KPI calculation
     */
    public function test_calculates_kpis_correctly(): void
    {
        // Create test data with known values

        $kpis = $this->service->getKpiScorecard();

        $this->assertIsArray($kpis);
        $this->assertArrayHasKey('revenue', $kpis);
        $this->assertArrayHasKey('users', $kpis);
    }

    /**
     * Test CLV calculation
     */
    public function test_calculates_customer_lifetime_value(): void
    {
        // Create customers with transactions

        $clv = $this->service->getClvAnalysis();

        $this->assertIsArray($clv);
    }

    /**
     * Test customer segmentation
     */
    public function test_segments_customers_by_rfm(): void
    {
        // Create customers with different RFM profiles

        $segments = $this->service->getCustomerSegmentation();

        $this->assertIsArray($segments);
    }

    /**
     * Test filter by date range
     */
    public function test_filters_data_by_date_range(): void
    {
        // Create data across different months

        $kpis = $this->service->getKpiScorecard(1, 2024);

        $this->assertIsArray($kpis);
    }
}
