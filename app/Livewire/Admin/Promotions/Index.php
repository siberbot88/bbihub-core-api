<?php

namespace App\Livewire\Admin\Promotions;

use App\Models\Promotion;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Manajemen Promosi')]
#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    #[Url(as: 'q')]      public string $q = '';
    #[Url(as: 'status')] public string $status = 'all';
    #[Url(as: 'pp')]     public int    $perPage = 10;

    public array $statusOptions = [
        'all'     => 'Semua Status',
        'active'  => 'Aktif',
        'draft'   => 'Draft',
        'expired' => 'Kadaluarsa',
    ];

    public function updatingQ()       { $this->resetPage(); }
    public function updatingStatus()  { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    // 🔹 dipanggil dari tombol "Refresh"
    public function refresh()
    {
        // Kalau mau sekalian balik ke page 1:
        $this->resetPage();
        // Livewire otomatis re-render setelah method dipanggil,
        // jadi nggak perlu ngapa-ngapain lagi di sini.
    }

    // 🔹 dipanggil dari tombol "Tambah Banner"
    public function openCreate()
    {
        // arahkan ke halaman form create banner
        return redirect()->route('admin.promotions.create');
    }

    public function render()
    {
        $q = Promotion::query();

        if ($this->q !== '') {
            $term = $this->q;
            $q->where(function ($w) use ($term) {
                $w->where('title', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            });
        }

        if ($this->status !== 'all' && Schema::hasColumn('promotions', 'status')) {
            $q->where('status', $this->status);
        }

        $promos = $q->latest()->paginate($this->perPage);

        return view('livewire.admin.promotions.index', [
            'promotions'    => $promos,
            'statusOptions' => $this->statusOptions,
        ]);
    }
}
