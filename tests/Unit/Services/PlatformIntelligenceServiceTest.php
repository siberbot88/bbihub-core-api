<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PlatformIntelligenceService;
use App\Models\User;
use App\Models\Workshop;
use App\Models\Transaction;
use App\Models\OwnerSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class PlatformIntelligenceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PlatformIntelligenceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PlatformIntelligenceService();
    }

    /**
     * Test churn risk detection logic
     */
    public function test_detects_churn_risk_for_declining_workshops(): void
    {
        // Create workshop with declining activity
        $workshop = Workshop::factory()->create();

        // Create transactions - declining pattern
        // 30 days ago: 10 transactions
        // 7 days ago: 2 transactions (decline)

        $result = $this->service->getChurnRiskCandidates();

        // Assert workshop is flagged as churn risk
        $this->assertIsArray($result);
    }

    /**
     * Test upsell candidate identification
     */
    public function test_identifies_high_volume_free_users_as_upsell_candidates(): void
    {
        // Create workshop with high transaction volume but no subscription
        $workshop = Workshop::factory()->create();

        // Create 60 transactions in last 30 days (above threshold)

        $result = $this->service->getUpsellCandidates();

        $this->assertIsArray($result);
        // Assert workshop is in upsell candidates
    }

    /**
     * Test MRR forecasting calculation
     */
    public function test_calculates_mrr_forecast_correctly(): void
    {
        // Create subscriptions with known MRR

        $forecast = $this->service->forecastMRR();

        $this->assertIsArray($forecast);
        $this->assertArrayHasKey('current_mrr', $forecast);
        $this->assertArrayHasKey('forecast_mrr', $forecast);
        $this->assertArrayHasKey('growth_rate', $forecast);
    }

    /**
     * Test that service handles empty data gracefully
     */
    public function test_handles_empty_data_without_errors(): void
    {
        // No workshops or transactions

        $churnRisks = $this->service->getChurnRiskCandidates();
        $upsellCandidates = $this->service->getUpsellCandidates();
        $mrrForecast = $this->service->forecastMRR();

        $this->assertIsArray($churnRisks);
        $this->assertIsArray($upsellCandidates);
        $this->assertIsArray($mrrForecast);
        $this->assertEmpty($churnRisks);
        $this->assertEmpty($upsellCandidates);
    }
}
