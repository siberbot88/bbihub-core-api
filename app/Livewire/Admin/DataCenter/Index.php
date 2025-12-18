<?php

namespace App\Livewire\Admin\DataCenter;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;

use App\Models\User;
use App\Models\Workshop;
use App\Models\Promotion;
use App\Models\Vehicle;

#[Title('Pusat Data')]
#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    /** ID yang dicentang di tabel (users/workshops/promotions/vehicles) */
    public array $selected = [];

    public bool $showDetailModal = false;
    public ?User $selectedUser   = null;

    // Query params tersimpan di URL
    #[Url(as: 'q')]       public string $q = '';
    #[Url(as: 'status')]  public string $status = 'all';
    #[Url(as: 'cat')]     public string $category = '';    // '', 'users', 'workshops', 'promotions', 'vehicles'
    #[Url(as: 'pp')]      public int $perPage = 8;

    public array $categoryOptions = [
        ''           => 'Pilih data…',
        'users'      => 'Pengguna',
        'workshops'  => 'Bengkel',
        'promotions' => 'Promosi',
        'vehicles'   => 'Kendaraan',
    ];

    public array $statusOptions = [
        'all'      => 'Semua Status',
        'active'   => 'Aktif',
        'inactive' => 'Nonaktif',
        'pending'  => 'Menunggu verifikasi',
    ];

    /* ============================================
     * HELPERS
     * ============================================ */

    /** Paginator kosong yang aman */
    protected function emptyPaginator(): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], 0, $this->perPage, $this->page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }

    /** Ambil semua ID baris pada halaman saat ini (untuk select all per halaman) */
    protected function currentRowIds(): array
    {
        $rows = $this->rows;

        if (! $rows) return [];

        // LengthAwarePaginator punya items()
        $items = method_exists($rows, 'items') ? $rows->items() : (is_iterable($rows) ? $rows : []);

        return collect($items)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();
    }

    public function editSelected(): void
    {
        if (count($this->selected) !== 1) {
            $this->dispatch('notify', message: 'Pilih satu baris untuk diedit.');
            return;
        }

        $id = (string) $this->selected[0];

        if ($this->category === 'users') {
            // BUKA MODAL EDIT USER (pakai UserModals component)
            $this->dispatch('user:edit', id: $id);
            return;
        }

        if ($this->category === 'workshops') {
            $this->redirectRoute('admin.workshops.edit', ['workshop' => $id], navigate: true);
            return;
        }

        if ($this->category === 'promotions') {
            $this->redirectRoute('admin.promotions.edit', ['promotion' => $id], navigate: true);
            return;
        }

        $this->dispatch('notify', message: 'Edit untuk kategori ini belum tersedia.');
    }


    /* ============================================
     * UI ACTIONS
     * ============================================ */

    /** Checkbox Select All (per halaman) */
    public function toggleSelectAll(): void
    {
        $ids = $this->currentRowIds();

        if (empty($ids)) {
            $this->selected = [];
            return;
        }

        // kalau semua id pada halaman sudah kepilih -> unselect semua, else select semua
        $this->selected = (count($this->selected) === count($ids)) ? [] : $ids;
    }

    /* ============================================
     * DETAIL USER (MODAL)
     * ============================================ */
    public function detail(string $userId): void
    {
        if ($this->category !== 'users') return;

        $this->selectedUser    = User::findOrFail($userId);
        $this->showDetailModal = true;
    }

    public function closeDetail(): void
    {
        $this->showDetailModal = false;
        $this->selectedUser    = null;
    }

    /* ============================================
     * DELETE SINGLE
     * ============================================ */
    public function deleteRow(string $id): void
    {
        if ($this->category === 'users') {
            if ($u = User::find($id)) $u->delete();
        } elseif ($this->category === 'workshops') {
            if ($w = Workshop::find($id)) $w->delete();
        } elseif ($this->category === 'promotions') {
            if ($p = Promotion::find($id)) $p->delete();
        } elseif ($this->category === 'vehicles' && class_exists(Vehicle::class)) {
            if ($v = Vehicle::find($id)) $v->delete();
        }

        session()->flash('message', 'Item berhasil dihapus.');
        $this->selected = [];
        $this->resetPage();
    }

    /* ============================================
     * DELETE SELECTED
     * ============================================ */
    public function deleteSelected($ids = null): void
    {
        // kalau tidak dikirim dari view, pakai property selected
        if ($ids === null) {
            $ids = $this->selected;
        }

        // kalau dikirim sebagai string JSON
        if (is_string($ids)) {
            $ids = json_decode($ids, true) ?: [];
        }

        if (!is_array($ids) || empty($ids)) return;

        $deleted = 0;

        foreach ($ids as $id) {
            if ($this->category === 'users') {
                if ($u = User::find($id)) { $u->delete(); $deleted++; }
            } elseif ($this->category === 'workshops') {
                if ($w = Workshop::find($id)) { $w->delete(); $deleted++; }
            } elseif ($this->category === 'promotions') {
                if ($p = Promotion::find($id)) { $p->delete(); $deleted++; }
            } elseif ($this->category === 'vehicles' && class_exists(Vehicle::class)) {
                if ($v = Vehicle::find($id)) { $v->delete(); $deleted++; }
            }
        }

        if ($deleted > 0) {
            session()->flash('message', "{$deleted} item berhasil dihapus.");
        }

        $this->selected = [];
        $this->resetPage();
    }

    /* ============================================
     * RESET PAGE / SELECTED saat filter berubah
     * ============================================ */
    public function updatingCategory(): void
    {
        $this->resetPage();
        $this->q = '';
        $this->status = 'all';
        $this->selected = [];
    }

    public function updatingQ(): void
    {
        $this->resetPage();
        $this->selected = [];
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
        $this->selected = [];
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
        $this->selected = [];
    }

    /** Saat pindah halaman pagination */
    public function updatedPage(): void
    {
        $this->selected = [];
    }

    /* ============================================
     * ROWS (Computed)
     * ============================================ */
    public function getRowsProperty()
    {
        return match ($this->category) {
            'users'      => $this->queryUsers(),
            'workshops'  => $this->queryWorkshops(),
            'promotions' => $this->queryPromotions(),
            'vehicles'   => $this->queryVehicles(),
            default      => null,
        };
    }

    protected function queryPromotions()
    {
        if (!class_exists(Promotion::class)) return $this->emptyPaginator();

        $q = Promotion::query();

        if ($this->q !== '') {
            $term = $this->q;
            $q->where(function ($w) use ($term) {
                $w->where('title', 'like', "%{$term}%")
                  ->orWhere('code', 'like', "%{$term}%");
            });
        }

        if ($this->status !== 'all' && Schema::hasColumn('promotions', 'status')) {
            $q->where('status', $this->status);
        }

        return $q->latest('id')->paginate($this->perPage);
    }

    protected function queryUsers()
    {
        $q = User::query();

        if ($this->q !== '') {
            $term = $this->q;
            $q->where(function ($w) use ($term) {
                $w->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
            });
        }

        if ($this->status !== 'all') {
            if (Schema::hasColumn('users', 'status')) {
                $q->where('status', $this->status);
            } elseif (Schema::hasColumn('users', 'email_verified_at')) {
                if ($this->status === 'active') $q->whereNotNull('email_verified_at');
                if ($this->status === 'inactive') $q->whereNull('email_verified_at');
            }
        }

        return $q->latest('id')->paginate($this->perPage);
    }

    protected function queryWorkshops()
    {
        $q = Workshop::query();

        if ($this->q !== '') {
            $term = $this->q;
            $q->where(function ($w) use ($term) {
                $w->where('name', 'like', "%{$term}%")
                  ->orWhere('code', 'like', "%{$term}%");
            });
        }

        if ($this->status !== 'all' && Schema::hasColumn('workshops', 'status')) {
            $q->where('status', $this->status);
        }

        return $q->latest('id')->paginate($this->perPage);
    }

    protected function queryVehicles()
    {
        if (!class_exists(Vehicle::class)) return $this->emptyPaginator();

        $q = Vehicle::query();

        if ($this->q !== '') {
            $term = $this->q;
            $q->where(function ($w) use ($term) {
                $w->where('plate_number', 'like', "%{$term}%")
                  ->orWhere('owner_name', 'like', "%{$term}%");
            });
        }

        if ($this->status !== 'all' && Schema::hasColumn('vehicles', 'status')) {
            $q->where('status', $this->status);
        }

        return $q->latest('id')->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.admin.data-center.index', [
            'rows'            => $this->rows,
            'categoryOptions' => $this->categoryOptions,
            'statusOptions'   => $this->statusOptions,
        ]);
    }
}
