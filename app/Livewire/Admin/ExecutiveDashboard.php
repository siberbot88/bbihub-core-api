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

    public function mount(EisService $eisService)
    {
        $this->scorecard = $eisService->getKpiScorecard();
        $this->clvAnalysis = $eisService->getClvAnalysis();
        $this->marketGap = $eisService->getMarketGapAnalysis();
        $this->customerSegmentation = $eisService->getCustomerSegmentation();
    }

    public function render()
    {
        return view('livewire.admin.executive-dashboard');
    }
}
