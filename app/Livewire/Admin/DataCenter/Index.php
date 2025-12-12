<?php

namespace App\Livewire\Admin\DataCenter;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Workshop;
use App\Models\Promotion;
// Vehicle opsional – guard saat class tak ada
use App\Models\Vehicle;

#[Title('Pusat Data')]
#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public bool $showDetailModal = false;
    public ?User $selectedUser   = null;

    // Query params tersimpan di URL
    #[Url(as: 'q')]       public string $q = '';
    #[Url(as: 'status')]  public string $status = 'all';
    #[Url(as: 'cat')]     public string $category = '';    // '', 'users', 'workshops', 'vehicles'
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
    


    public function detail(string $userId): void
    {
        if ($this->category !== 'users') {
            return;
        }

        $this->selectedUser    = User::findOrFail($userId);
        $this->showDetailModal = true;
    }

    public function deleteRow(string $id): void
    {
        if ($this->category === 'users') {
            $u = \App\Models\User::find($id);
            if ($u) {
                $u->delete();
                session()->flash('message', 'Pengguna berhasil dihapus.');
            }
        } elseif ($this->category === 'workshops') {
            $w = \App\Models\Workshop::find($id);
            if ($w) {
                $w->delete();
                session()->flash('message', 'Bengkel berhasil dihapus.');
            }
        } elseif ($this->category === 'promotions') {
            $p = \App\Models\Promotion::find($id);
            if ($p) {
                $p->delete();
                session()->flash('message', 'Promosi berhasil dihapus.');
            }
        }

        $this->resetPage();
    }

    public function closeDetail(): void
    {
        $this->showDetailModal = false;
        $this->selectedUser    = null;
    }

    /** Delete selected rows based on category */
    public function deleteSelected($ids): void
    {
        if (is_string($ids)) {
            $ids = json_decode($ids, true) ?: [];
        }

        if (!is_array($ids) || empty($ids)) {
            return;
        }

        $deleted = 0;
        foreach ($ids as $id) {
            if ($this->category === 'users') {
                $u = User::find($id);
                if ($u) {
                    $u->delete();
                    $deleted++;
                }
            } elseif ($this->category === 'workshops') {
                $w = Workshop::find($id);
                if ($w) {
                    $w->delete();
                    $deleted++;
                }
            } elseif ($this->category === 'promotions') {
                $p = Promotion::find($id);
                if ($p) {
                    $p->delete();
                    $deleted++;
                }
            }
        }

        if ($deleted > 0) {
            session()->flash('message', "$deleted item berhasil dihapus.");
        }
        $this->resetPage();
    }
    
    // Reset halaman saat filter berubah
    public function updatingCategory() { $this->resetPage(); $this->q=''; $this->status='all'; }
    public function updatingQ()        { $this->resetPage(); }
    public function updatingStatus()   { $this->resetPage(); }
    public function updatingPerPage()  { $this->resetPage(); }

    /** Accessor rows: hasil sesuai kategori */
    public function getRowsProperty()
    {
        return match ($this->category) {
            'users' => $this->queryUsers(),
            'workshops' => $this->queryWorkshops(),
            'promotions' => $this->queryPromotions(),
            'vehicles' => $this->queryVehicles(),
            default => null,
        };
    }

    protected function queryPromotions()
    {
        if (!class_exists(Promotion::class)) {
            return collect([])->paginate($this->perPage);
        }

        $q = Promotion::query();

        if ($this->q !== '') {
            $term = $this->q;
            $q->where(function ($w) use ($term) {
                $w->where('title','like',"%{$term}%")
                  ->orWhere('code','like',"%{$term}%");
            });
        }

        if ($this->status !== 'all' && Schema::hasColumn('promotions', 'status')) {
            $q->where('status', $this->status);
        }

        return $q->latest('id')->paginate($this->perPage);
    }

    /** Query Users (aman bila tak ada kolom status) */
    protected function queryUsers()
    {
        $q = User::query();

        if ($this->q !== '') {
            $term = $this->q;
            $q->where(function ($w) use ($term) {
                $w->where('name','like',"%{$term}%")
                  ->orWhere('email','like',"%{$term}%");
            });
        }

        if ($this->status !== 'all') {
            // gunakan kolom yang ada: status / email_verified_at / is_active dll.
            if (Schema::hasColumn('users', 'status')) {
                $q->where('status', $this->status);
            } elseif (Schema::hasColumn('users', 'email_verified_at')) {
                // contoh mapping sederhana
                if ($this->status === 'active') {
                    $q->whereNotNull('email_verified_at');
                } elseif ($this->status === 'inactive') {
                    $q->whereNull('email_verified_at');
                }
            }
        }

        return $q->latest('id')->paginate($this->perPage);
    }

    /** Query Workshops (cek kolom status dulu) */
    protected function queryWorkshops()
    {
        $q = Workshop::query();

        if ($this->q !== '') {
            $term = $this->q;
            $q->where(function ($w) use ($term) {
                $w->where('name','like',"%{$term}%")
                  ->orWhere('code','like',"%{$term}%");
            });
        }

        if ($this->status !== 'all' && Schema::hasColumn('workshops', 'status')) {
            $q->where('status', $this->status);
        }

        return $q->latest('id')->paginate($this->perPage);
    }

    /** Query Vehicles (opsional; guard jika model/tabel/kolom belum ada) */
    protected function queryVehicles()
    {
        // Jika model Vehicle belum dibuat, kembalikan koleksi kosong ter-paginate
        if (!class_exists(Vehicle::class)) {
            return collect([])->paginate($this->perPage);
        }

        $q = Vehicle::query();

        if ($this->q !== '') {
            $term = $this->q;
            $q->where(function ($w) use ($term) {
                $w->where('plate_number','like',"%{$term}%")
                  ->orWhere('owner_name','like',"%{$term}%");
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
            'rows'           => $this->rows,            // bisa null bila category kosong
            'categoryOptions'=> $this->categoryOptions,
            'statusOptions'  => $this->statusOptions,
        ]);
    }
}
