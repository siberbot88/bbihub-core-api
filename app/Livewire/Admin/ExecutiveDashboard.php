<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Services\EisService;

#[Title('Executive EIS')]
#[Layout('layouts.app')]
class ExecutiveDashboard extends Component
{
    public array $scorecard = [];
    public array $clvAnalysis = [];
    public array $marketGap = [];
    public array $customerSegmentation = [];
    public array $platformOutlook = [];

    // Filter State
    public int $selectedMonth;
    public int $selectedYear;
    public bool $showFilter = false;

    public function mount(EisService $eisService, \App\Services\PlatformIntelligenceService $platformService)
    {
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;

        $this->loadData($eisService, $platformService);
    }

    public function loadData(EisService $eisService, \App\Services\PlatformIntelligenceService $platformService)
    {
        $this->scorecard = $eisService->getKpiScorecard($this->selectedMonth, $this->selectedYear);
        // These metrics are currently global snapshots, but cached
        $this->clvAnalysis = $eisService->getClvAnalysis();
        $this->marketGap = $eisService->getMarketGapAnalysis();
        $this->customerSegmentation = $eisService->getCustomerSegmentation();

        // Platform Intelligence (SaaS)
        $this->platformOutlook = [
            'churn_candidates' => $platformService->getChurnRiskCandidates(),
            'upsell_candidates' => $platformService->getUpsellCandidates(),
            'mrr_forecast' => $platformService->forecastMRR()
        ];
    }

    public function refresh(EisService $eisService, \App\Services\PlatformIntelligenceService $platformService)
    {
        // Clear specific caches
        \Illuminate\Support\Facades\Cache::forget("eis_scorecard_{$this->selectedYear}_{$this->selectedMonth}");
        \Illuminate\Support\Facades\Cache::forget("eis_clv");
        \Illuminate\Support\Facades\Cache::forget("eis_market_gap");
        \Illuminate\Support\Facades\Cache::forget("eis_segmentation");

        $this->loadData($eisService, $platformService);

        $this->dispatch('refresh-charts'); // Signal JS to re-render charts
    }

    public function applyFilter(EisService $eisService, \App\Services\PlatformIntelligenceService $platformService)
    {
        $this->showFilter = false;
        $this->loadData($eisService, $platformService);
        $this->dispatch('refresh-charts');
    }

    public function exportData()
    {
        return redirect()->route('admin.eis.print', [
            'month' => $this->selectedMonth,
            'year' => $this->selectedYear
        ]);
    }

    public function render()
    {
        return view('livewire.admin.executive-dashboard');
    }
}
