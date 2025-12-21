<?php

namespace App\Services;

use App\Models\OwnerSubscription;
use App\Models\Transaction;
use App\Models\Workshop;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PlatformIntelligenceService
{
    /**
     * Identify Workshops at Risk of Churn
     * Logic: Workshops with > 50% drop in transaction volume compared to last month.
     * Only considers active workshops.
     */
    public function getChurnRiskCandidates(): array
    {
        // 1. Get Activity Last 30 Days vs Previous 30 Days
        $today = Carbon::now();
        $lastMonthStart = $today->copy()->subDays(30);
        $prevMonthStart = $today->copy()->subDays(60);

        // Get Transaction Counts per Workshop for both periods
        $stats = Transaction::select(
            'workshop_uuid',
            DB::raw("SUM(CASE WHEN created_at >= '{$lastMonthStart}' THEN 1 ELSE 0 END) as current_vol"),
            DB::raw("SUM(CASE WHEN created_at >= '{$prevMonthStart}' AND created_at < '{$lastMonthStart}' THEN 1 ELSE 0 END) as prev_vol")
        )
            ->where('created_at', '>=', $prevMonthStart)
            ->groupBy('workshop_uuid')
            ->get();

        $risky = [];
        foreach ($stats as $stat) {
            // Filter: Must have had 'some' activity previously to be considered dropping
            if ($stat->prev_vol > 5) {
                // Check for > 50% drop
                $dropRate = 1 - ($stat->current_vol / $stat->prev_vol);
                if ($dropRate >= 0.5) {
                    $workshop = Workshop::find($stat->workshop_uuid);
                    if ($workshop) {
                        $risky[] = [
                            'name' => $workshop->name,
                            'owner' => $workshop->owner->name ?? 'Unknown',
                            'prev_vol' => $stat->prev_vol,
                            'current_vol' => $stat->current_vol,
                            'drop_rate' => round($dropRate * 100, 1) . '%'
                        ];
                    }
                }
            }
        }

        return $risky;
    }

    /**
     * Identify Upsell Candidates
     * Logic: "Free" tier workshops with high transaction volume (> 50/month).
     */
    public function getUpsellCandidates(): array
    {
        // Assuming 'free' tier is identified by lack of active paid subscription or specific plan_id
        // For MVP: Workshops NOT in OwnerSubscription(active) BUT having high transactions

        $activeSubWorkshopIds = OwnerSubscription::where('status', 'active')->pluck('user_id');
        // Note: user_id in subscription maps to User(Owner), Workshop maps to Owner.
        // It's safer to check Workshop -> Owner -> Subscription.

        // Simplified Query: High Volume Workshops
        $candidates = Transaction::select('workshop_uuid', DB::raw('count(*) as total'))
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('workshop_uuid')
            ->having('total', '>', 50) // Threshold for "Heavy User"
            ->with('workshop.owner')
            ->get();

        $upsells = [];
        foreach ($candidates as $c) {
            $owner = $c->workshop->owner;
            if (!$owner)
                continue;

            // Check if Owner has active sub
            $hasActiveSub = OwnerSubscription::where('user_id', $owner->id)
                ->where('status', 'active')
                ->exists();

            if (!$hasActiveSub) {
                $upsells[] = [
                    'workshop' => $c->workshop->name,
                    'owner' => $owner->name,
                    'volume' => $c->total,
                    'reason' => 'High Volume Free User'
                ];
            }
        }

        return $upsells;
    }

    /**
     * Forecast MRR using Linear Regression
     * Predicts next month's MRR based on last 6 months history.
     */
    public function forecastMRR(): array
    {
        // 1. Get Historical MRR (Active Subs Value per Month)
        // This requires temporal data. For MVP, we simulate or calculate if possible.
        // In real app, we should have a 'metrics_history' table.
        // We will calculate backwards:

        $history = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i)->endOfMonth();
            $count = OwnerSubscription::where('created_at', '<=', $date)->count();
            $mrr = $count * 150000;

            $history[] = [
                'x' => 6 - $i, // 1 to 6
                'y' => $mrr,
                'label' => $date->format('M Y')
            ];
        }

        // 2. Linear Regression (Least Squares)
        $n = count($history);
        if ($n < 2)
            return ['prediction' => 0, 'trend' => 'flat'];

        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumXX = 0;
        foreach ($history as $pt) {
            $sumX += $pt['x'];
            $sumY += $pt['y'];
            $sumXY += ($pt['x'] * $pt['y']);
            $sumXX += ($pt['x'] * $pt['x']);
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumXX - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;

        // Predict next month (x = 7)
        $nextX = 7;
        $prediction = ($slope * $nextX) + $intercept;

        return [
            'history' => $history,
            'prediction' => max(0, $prediction),
            'growth_rate' => $history[0]['y'] > 0 ? (($prediction - $history[0]['y']) / $history[0]['y']) * 100 : 0
        ];
    }
}
