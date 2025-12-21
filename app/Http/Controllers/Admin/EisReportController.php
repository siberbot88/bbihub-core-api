<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EisService;
use Illuminate\Http\Request;

class EisReportController extends Controller
{
    public function print(Request $request, EisService $eisService)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        // Fetch Data & Analysis
        $scorecard = $eisService->getKpiScorecard($month, $year);
        $analysis = $eisService->generateAnalysis($month, $year);
        $clv = $eisService->getClvAnalysis();
        $segmentation = $eisService->getCustomerSegmentation();

        return view('admin.eis.print-report', compact(
            'scorecard',
            'analysis',
            'clv',
            'segmentation',
            'month',
            'year'
        ));
    }
}
