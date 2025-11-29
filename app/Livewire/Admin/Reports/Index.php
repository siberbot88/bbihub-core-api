<?php

namespace App\Livewire\Admin\Reports;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Report;

#[Layout('layouts.app')]
#[Title('Laporan')]
class Index extends Component
{
    public array $cards = [];

    public function mount(): void
    {
        $total     = Report::count();
        $today     = Report::whereDate('created_at', today())->count();
        $diproses  = Report::where('status', 'diproses')->count();
        $selesai   = Report::where('status', 'selesai')->count();

        $this->cards = [
            [
                'label' => 'Total laporan masuk',
                'value' => $total,
            ],
            [
                'label' => 'Laporan masuk hari ini',
                'value' => $today,
            ],
            [
                'label' => 'Diproses',
                'value' => $diproses,
            ],
            [
                'label' => 'Selesai',
                'value' => $selesai,
            ],
        ];
    }

    public function render()
    {
        $reports = Report::with('user')->latest()->paginate(10);

        return view('livewire.admin.reports.index', [
            'reports' => $reports,
            'cards'   => $this->cards,
        ]);
    }
}
