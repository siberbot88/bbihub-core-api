<?php

namespace App\Livewire\Admin\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

#[Title('Laporan')]
#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    // Tabs: overview|users|workshops|vehicles|finance
    #[Url(as: 'tab')]    public string  $tab   = 'overview';

    // Rentang tanggal
    #[Url(as: 'from')]   public ?string $from  = null;   // Y-m-d
    #[Url(as: 'to')]     public ?string $to    = null;   // Y-m-d
    #[Url(as: 'range')]  public string  $range = '30d';  // 7d|30d|90d|ytd|custom

    // Pencarian & paging
    #[Url(as: 'q')]      public string  $q     = '';
    #[Url(as: 'pp')]     public int     $perPage = 10;

    public function mount(): void
    {
        // Inisialisasi rentang jika kosong
        if (!$this->from || !$this->to) {
            $this->applyQuickRange($this->range ?: '30d');
        } else {
            $this->normalizeDates();
        }
    }

    /** Quick range helper */
    public function applyQuickRange(string $r): void
    {
        $this->range = $r;
        $today = Carbon::today();

        [$from, $to] = match ($r) {
            '7d'   => [$today->copy()->subDays(6),  $today],
            '30d'  => [$today->copy()->subDays(29), $today],
            '90d'  => [$today->copy()->subDays(89), $today],
            'ytd'  => [Carbon::create($today->year, 1, 1), $today],
            default=> [
                $this->from ? Carbon::parse($this->from) : $today->copy()->subDays(29),
                $this->to   ? Carbon::parse($this->to)   : $today
            ],
        };

        $this->from = $from->toDateString();
        $this->to   = $to->toDateString();

        $this->resetPage();
    }

    /** Normalisasi input tanggal dari URL/manual */
    protected function normalizeDates(): void
    {
        try {
            $from = Carbon::parse($this->from)->startOfDay();
            $to   = Carbon::parse($this->to)->endOfDay();
        } catch (\Throwable $e) {
            // Jika parsing gagal, fallback ke 30d
            $this->applyQuickRange('30d');
            return;
        }

        if ($from->greaterThan($to)) {
            // Tukar jika from > to
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $this->from = $from->toDateString();
        $this->to   = $to->toDateString();
    }

    /* Reset halaman saat filter berubah */
    public function updatingTab()     { $this->resetPage(); }
    public function updatingQ()       { $this->resetPage(); }
    public function updatingRange()   { $this->resetPage(); }
    public function updatingFrom()    { $this->resetPage(); }
    public function updatingTo()      { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    // ===============================
    // ====== DATA SEMENTARA =========
    // (ganti dengan query real)
    // ===============================

    /** KPI ringkasan */
    public function getKpisProperty(): array
    {
        // Contoh stat dummy; bisa disesuaikan dengan $this->tab, $this->from/$this->to
        return [
            ['label' => 'Total Pengguna',  'value' => 152,      'delta' => '+3%'],
            ['label' => 'Bengkel Aktif',   'value' => 48,       'delta' => '+5%'],
            ['label' => 'Transaksi',       'value' => 930,      'delta' => '+12%'],
            ['label' => 'Pendapatan',      'value' => 'Rp 125jt','delta' => '+8%'],
        ];
    }

    /** Data seri untuk chart (line) sesuai rentang */
    public function getSeriesProperty(): array
    {
        $from = Carbon::parse($this->from ?? Carbon::today()->subDays(29));
        $to   = Carbon::parse($this->to   ?? Carbon::today());

        // Generate value dummy per hari
        $series = [];
        $cursor = $from->copy();
        $seed   = crc32($this->tab . ($this->q ?? '') . $from->toDateString() . $to->toDateString());

        while ($cursor->lte($to)) {
            // contoh generator nilai pseudo-random yang stabil
            $hash = crc32($cursor->toDateString() . $seed);
            $val  = ($hash % 50) + 10; // 10..59
            $series[] = [
                'date'  => $cursor->toDateString(),
                'value' => $val,
            ];
            $cursor->addDay();
        }

        return $series;
    }

    // ======= Export placeholders (isi sesuai kebutuhan proyek) =======
    public function exportCsv(): void
    {
        // TODO: generate CSV berdasarkan $this->tab, $this->from, $this->to, $this->q
        $this->dispatch('toast', body: 'Export CSV diproses (contoh).');
    }

    public function exportPdf(): void
    {
        // TODO: generate PDF berdasarkan $this->tab, $this->from, $this->to, $this->q
        $this->dispatch('toast', body: 'Export PDF diproses (contoh).');
    }

    public function render()
    {
        // Catatan: ganti dataset/pagination sesuai tab & filter jika sudah ada modelnya
        return view('livewire.admin.reports.index', [
            'kpis'   => $this->kpis,    // accessor getKpisProperty()
            'series' => $this->series,  // accessor getSeriesProperty()
            // bila butuh tabel per tab:
            // 'rows' => Model::query()->...->paginate($this->perPage),
        ]);
    }
}
