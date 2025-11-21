<?php

namespace App\Livewire\Admin\Workshops;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Schema;
use App\Models\Workshop;

#[Title('Manajemen Bengkel')]
#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    #[Url] public string $q = '';
    #[Url] public string $status = 'all';
    #[Url] public string $city = 'all';
    #[Url] public int $perPage = 8;

    public array $statusOptions = [
        'all'       => 'Semua Status',
        'pending'   => 'Menunggu Verifikasi',
        'active'    => 'Aktif',
        'suspended' => 'Ditangguhkan',
    ];

    public array $cityOptions = ['all' => 'Semua Kota'];

    public function mount(): void
    {
        // Dropdown kota (distinct, non-null)
        $cities = Workshop::query()
            ->select('city')
            ->distinct()
            ->pluck('city')
            ->filter()
            ->values();

        foreach ($cities as $c) {
            $this->cityOptions[$c] = $c;
        }
    }

    // Reset pagination saat filter/search berubah
    public function updatingQ()       { $this->resetPage(); }
    public function updatingStatus()  { $this->resetPage(); }
    public function updatingCity()    { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    /**
     * Ringkasan kartu (untuk tampilan seperti manajemen pengguna)
     * Gunakan struktur yang sama agar bisa pakai <x-summary-card>.
     */
    public function getCardsProperty(): array
    {
        $base = Workshop::query();

        // Pastikan aman kalau kolom status belum ada
        $hasStatus = Schema::hasColumn('workshops', 'status');

        $total      = (clone $base)->count();
        $pending    = $hasStatus ? (clone $base)->where('status', 'pending')->count() : 0;
        $active     = $hasStatus ? (clone $base)->where('status', 'active')->count() : 0;
        $suspended  = $hasStatus ? (clone $base)->where('status', 'suspended')->count() : 0;

        return [
            [
                'label' => 'Total Bengkel',
                'value' => $total,
                'hint'  => 'update +5%',
                'icon'  => 'total_bengkel',
                'color' => 'blue',
            ],
            [
                'label' => 'Menunggu Verifikasi',
                'value' => $pending,
                'hint'  => 'update +2%',
                'icon'  => 'total_verifikasi',
                'color' => 'yellow',
            ],
            [
                'label' => 'Bengkel Aktif',
                'value' => $active,
                'hint'  => 'update +5%',
                'icon'  => 'akun_aktif',
                'color' => 'green',
            ],
            [
                'label' => 'Bengkel Ditangguhkan',
                'value' => $suspended,
                'hint'  => 'update +5%',
                'icon'  => 'akun_tidak_aktif',
                'color' => 'red',
            ],
        ];
    }

    public function render()
    {
        $query = Workshop::query();

        // Pencarian
        if ($this->q !== '') {
            $q = $this->q;
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('code', 'like', "%{$q}%");
            });
        }

        // Filter status → hanya jika kolom 'status' ada
        if ($this->status !== 'all' && Schema::hasColumn('workshops', 'status')) {
            $query->where('status', $this->status);
        }

        // Filter kota
        if ($this->city !== 'all') {
            $query->where('city', $this->city);
        }

        $rows = $query->latest('id')->paginate($this->perPage);

        return view('livewire.admin.workshops.index', [
            'rows'          => $rows,
            'cards'         => $this->cards, // gunakan accessor getCardsProperty()
            'statusOptions' => $this->statusOptions,
            'cityOptions'   => $this->cityOptions,
        ]);
    }
}
