<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Workshop;
use App\Models\User;
use App\Models\Employment;
use App\Models\Feedback;
use App\Models\Transaction;
use App\Models\AuditLog;
use App\Models\Report;
use App\Models\OwnerSubscription;
use App\Models\MembershipTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Title('Dashboard')]
#[Layout('layouts.app')]
class Dashboard extends Component
{
    public array $cards = [];
    public array $activityLogs = [];
    public array $serviceMonthly = [];
    public array $revenueByWorkshop = [];
    public array $appRevenue = [];
    public array $quickActions = [];

    public function mount(): void
    {
        $this->loadCards();
        $this->loadActivityLogs();
        $this->loadServiceChart();
        $this->loadRevenueChart();
        $this->loadAppRevenueTrend();
        $this->loadQuickActions();
    }

    private function loadCards(): void
    {
        // 1. Total Bengkel
        $totalWorkshops = Workshop::count();
        $activeWorkshops = Workshop::where('status', 'active')->count();
        // Simple delta: just comparing to 0 for now as strict monthly history isn't tracked yet
        // In real app, compare with count from start of month

        // 2. Total User (Pelanggan)
        $totalUsers = User::doesntHave('roles')->count();

        // 3. Total Teknisi
        $totalMechanics = Employment::where('status', 'active')->count();

        // 4. Total Feedback (Today)
        $totalFeedback = Feedback::whereDate('created_at', Carbon::today())->count();
        $totalFeedbackAll = Feedback::count(); // Fallback if today is 0 to show something

        $this->cards = [
            [
                'title' => 'Total Bengkel',
                'value' => $totalWorkshops,
                'desc' => "{$activeWorkshops} Bengkel Aktif",
                'icon' => 'bengkel',
                'delta' => '+0%', // Placeholder until historical data is tracked
                'chart' => $this->getSparklineData(Workshop::class)
            ],
            [
                'title' => 'Total User',
                'value' => $totalUsers,
                'desc' => 'Pelanggan terdaftar',
                'icon' => 'pengguna',
                'delta' => '+0%',
                'chart' => $this->getSparklineData(User::class)
            ],
            [
                'title' => 'Total Teknisi',
                'value' => $totalMechanics,
                'desc' => 'Mekanik terverifikasi',
                'icon' => 'tech',
                'delta' => '+0%',
                'chart' => [0, 0, 0, 0, 0, 0, 0] // Employment dates might vary, keeping simple
            ],
            [
                'title' => 'Total Feedback',
                'value' => $totalFeedback > 0 ? $totalFeedback : $totalFeedbackAll,
                'desc' => $totalFeedback > 0 ? 'Feedback hari ini' : 'Total semua feedback',
                'icon' => 'feedback',
                'delta' => '+0%',
                'chart' => $this->getSparklineData(Feedback::class)
            ],
        ];
    }

    private function loadActivityLogs(): void
    {
        // Get latest 5 audit logs or generic logs
        $logs = AuditLog::with('user')->latest()->take(5)->get();

        $this->activityLogs = $logs->map(function ($log) {
            return [
                'title' => $log->event . ' - ' . ($log->user->name ?? $log->user_email ?? 'System'),
                'time' => $log->created_at->diffForHumans(),
            ];
        })->toArray();

        // Fallback if empty (e.g. fresh install)
        if (empty($this->activityLogs)) {
            $this->activityLogs[] = ['title' => 'Belum ada aktivitas tercatat', 'time' => '-'];
        }
    }

    private function loadServiceChart(): void
    {
        // Get Counts for last 6 months
        $data = [];
        $labels = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = Transaction::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $data[] = $count;
            $labels[] = $date->format('M');
        }

        $this->serviceMonthly = [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function loadAppRevenueTrend(): void
    {
        // Calculate Revenue for last 6 months
        $labels = [];
        $totalRevenue = [];
        $subscriptionRevenue = [];
        $membershipRevenue = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $month = $date->month;
            $year = $date->year;

            // 1. Owner Subscriptions (Active/Paid)
            // Assuming 'active' implies paid, or check transaction status if available.
            // Using created_at for simplicity of revenue recognition time
            $subs = OwnerSubscription::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->sum('gross_amount');

            // 2. Customer Memberships (Completed)
            $mems = MembershipTransaction::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where('payment_status', 'completed')
                ->sum('amount');

            $labels[] = $date->format('M Y');
            $subscriptionRevenue[] = $subs;
            $membershipRevenue[] = $mems;
            $totalRevenue[] = $subs + $mems;
        }

        $this->appRevenue = [
            'labels' => $labels,
            'total' => $totalRevenue,
            'breakdown' => [
                'subscriptions' => $subscriptionRevenue,
                'memberships' => $membershipRevenue
            ],
            'sources_pie' => [
                'labels' => ['Komisi Service', 'Langganan Bengkel', 'Membership'], // Placeholder for commission
                'data' => [
                    0, // Commission (Not implemented yet)
                    array_sum($subscriptionRevenue),
                    array_sum($membershipRevenue)
                ]
            ]
        ];
    }

    private function loadRevenueChart(): void
    {
        // Top 5 Workshops by Revenue (Keep existing logic for workshop performance)
        $topWorkshops = Transaction::select('workshop_uuid', DB::raw('sum(amount) as revenue'))
            ->groupBy('workshop_uuid')
            ->orderByDesc('revenue')
            ->take(5)
            ->with('workshop:id,name') // Ensure relation exists
            ->get();

        $labels = [];
        $data = [];

        foreach ($topWorkshops as $item) {
            $name = $item->workshop->name ?? 'Unknown';
            $labels[] = Str::limit($name, 15);
            $data[] = $item->revenue;
        }

        // Dummy data if empty to show something nice
        if (empty($data)) {
            $labels = ['Belum ada data'];
            $data = [0];
        }

        $this->revenueByWorkshop = [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function loadQuickActions(): void
    {
        $this->quickActions = [
            'pending_workshops' => Workshop::where('status', 'pending')->count(),
            'pending_reports' => Report::where('status', 'pending')->count(),
            'suspended_workshops' => Workshop::where('status', 'suspended')->count(),
        ];
    }

    private function getSparklineData($modelClass): array
    {
        // Get daily counts for last 7 days
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $data[] = $modelClass::whereDate('created_at', $date)->count();
        }
        return $data;
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
