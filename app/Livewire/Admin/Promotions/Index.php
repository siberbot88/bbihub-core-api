<?php

namespace App\Livewire\Admin\Promotions;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Promotion;

#[Title('Manajemen Promosi')]
#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $placement = 'all'; // ⬅️ WAJIB ADA
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

     public function updatingPlacement()
    {
        $this->resetPage();
    }
    
    public function refresh()
    {
        $this->resetPage();
    }

    public function openCreate()
    {
        return redirect()->route('admin.promotions.create');
    }

    // 🔹 EDIT – arahkan ke halaman edit
    public function edit(int $id)
    {
        return redirect()->route('admin.promotions.edit', $id);
    }

    // 🔹 HAPUS – hapus langsung dari sini
    public function delete(int $id)
    {
        Promotion::findOrFail($id)->delete();

        session()->flash('success', 'Banner berhasil dihapus.');
        $this->resetPage(); // refresh data
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
