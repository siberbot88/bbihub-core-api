<?php

namespace App\Livewire\Admin\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Models\Report;
use Carbon\Carbon;

#[Title('Laporan')]
#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    // filter & pencarian
    #[Url(as: 'q')]
    public string $q = '';

    #[Url(as: 'status')]
    public string $status = 'all';      // all|baru|diproses|diterima|selesai

    #[Url(as: 'type')]
    public string $type = 'all';        // all|bug|keluhan|saran|ulasan (sesuaikan sendiri)

    #[Url(as: 'date')]
    public ?string $date = null;        // Y-m-d

    #[Url(as: 'pp')]
    public int $perPage = 10;

    // reset halaman kalau filter berubah
    public function updatingQ()       { $this->resetPage(); }
    public function updatingStatus()  { $this->resetPage(); }
    public function updatingType()    { $this->resetPage(); }
    public function updatingDate()    { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    /** Statistik untuk 4 kartu atas */
    public function getStatsProperty(): array
    {
        $base = Report::query();

        $total      = (clone $base)->count();
        $today      = (clone $base)->whereDate('created_at', Carbon::today())->count();
        $processing = (clone $base)->where('status', 'diproses')->count();
        $done       = (clone $base)->where('status', 'selesai')->count();

        return [
            [
                'title' => 'Total laporan masuk',
                'value' => $total,
                'trend' => '+0%',       // nanti kalau mau bisa dihitung beneran
                'icon'  => 'inbox',
            ],
            [
                'title' => 'Laporan masuk hari ini',
                'value' => $today,
                'trend' => '+0%',
                'icon'  => 'calendar',
            ],
            [
                'title' => 'Diproses',
                'value' => $processing,
                'trend' => '+0%',
                'icon'  => 'progress',
            ],
            [
                'title' => 'Selesai',
                'value' => $done,
                'trend' => '+0%',
                'icon'  => 'check',
            ],
        ];
    }

    /** Query untuk tabel laporan */
    public function getRowsProperty()
    {
        $q = Report::with('user')->latest('created_at');

        // search (pengirim, email, jenis, isi laporan)
        if ($this->q !== '') {
            $term = $this->q;
            $q->where(function ($w) use ($term) {
                $w->where('report_type', 'like', "%{$term}%")
                  ->orWhere('report_data', 'like', "%{$term}%")
                  ->orWhereHas('user', function ($u) use ($term) {
                      $u->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                  });
            });
        }

        // filter status
        if ($this->status !== 'all') {
            $q->where('status', $this->status);
        }

        // filter jenis laporan
        if ($this->type !== 'all') {
            $q->where('report_type', $this->type);
        }

        // filter tanggal (satu hari)
        if ($this->date) {
            $q->whereDate('created_at', $this->date);
        }

        return $q->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.admin.reports.index', [
            'stats' => $this->stats,  // dari accessor getStatsProperty
            'rows'  => $this->rows,   // dari accessor getRowsProperty
        ]);
    }
}
