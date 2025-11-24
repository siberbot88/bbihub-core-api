<?php

namespace App\Livewire\Admin\Workshops;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
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
    #[Url] public int $perPage = 10;

    // Modal control
    public bool $showDetail = false;
    public bool $showEdit = false;
    public bool $showDelete = false;
    public bool $showSuspend = false;

    // Selected workshop
    public ?Workshop $selectedWorkshop = null;

    public array $statusOptions = [
        'all'       => 'Semua Status',
        'pending'   => 'Menunggu Verifikasi',
        'active'    => 'Aktif',
        'suspended' => 'Ditangguhkan',
    ];

    public array $cityOptions = ['all' => 'Semua Kota'];

    public function mount(): void
    {
        // Cache city options for 1 hour
        $this->cityOptions = Cache::remember('workshop_cities', 3600, function () {
            $cities = Workshop::query()
                ->select('city')
                ->distinct()
                ->whereNotNull('city')
                ->pluck('city')
                ->filter()
                ->values();

            $options = ['all' => 'Semua Kota'];
            foreach ($cities as $c) {
                $options[$c] = $c;
            }
            return $options;
        });
    }

    // Reset pagination saat filter/search berubah
    public function updatingQ()       { $this->resetPage(); }
    public function updatingStatus()  { $this->resetPage(); }
    public function updatingCity()    { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    /**
     * Computed property for summary cards with lazy loading
     */
    #[Computed]
    public function cards(): array
    {
        $hasStatus = Schema::hasColumn('workshops', 'status');

        // Use selectRaw for better performance
        $total = Workshop::count();
        $pending = $hasStatus ? Workshop::where('status', 'pending')->count() : 0;
        $active = $hasStatus ? Workshop::where('status', 'active')->count() : 0;
        $suspended = $hasStatus ? Workshop::where('status', 'suspended')->count() : 0;

        return [
            [
                'label' => 'Total Bengkel',
                'value' => $total,
                'hint'  => 'update +5%',
                'color' => 'blue',
            ],
            [
                'label' => 'Menunggu Verifikasi',
                'value' => $pending,
                'hint'  => 'update +2%',
                'color' => 'yellow',
            ],
            [
                'label' => 'Bengkel Aktif',
                'value' => $active,
                'hint'  => 'update +5%',
                'color' => 'green',
            ],
            [
                'label' => 'Bengkel Ditangguhkan',
                'value' => $suspended,
                'hint'  => 'update +5%',
                'color' => 'red',
            ],
        ];
    }

    /**
     * Computed property for workshops with optimized query
     */
    #[Computed]
    public function workshops()
    {
        $hasStatus = Schema::hasColumn('workshops', 'status');
        $hasRating = Schema::hasColumn('workshops', 'rating');
        
        // Build select columns dynamically - only add columns that exist
        $columns = [
            'id',
            'name',
            'code',
            'city',
            'created_at',
        ];
        
        if ($hasStatus) {
            $columns[] = 'status';
        }
        
        if ($hasRating) {
            $columns[] = 'rating';
        }
        
        $query = Workshop::query()->select($columns);

        // Pencarian
        if ($this->q !== '') {
            $q = $this->q;
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('code', 'like', "%{$q}%");
            });
        }

        // Filter status - only if column exists
        if ($this->status !== 'all' && $hasStatus) {
            $query->where('status', $this->status);
        }

        // Filter kota
        if ($this->city !== 'all') {
            $query->where('city', $this->city);
        }

        return $query->latest('id')->paginate($this->perPage);
    }

    // ==========================
    // MODAL OPENERS
    // ==========================

    public function view($id)
    {
        $this->selectedWorkshop = Workshop::findOrFail($id);
        $this->showDetail = true;
    }

    public function edit($id)
    {
        $this->selectedWorkshop = Workshop::findOrFail($id);
        $this->showEdit = true;
    }

    public function suspend($id)
    {
        $this->selectedWorkshop = Workshop::findOrFail($id);
        $this->showSuspend = true;
    }

    public function delete($id)
    {
        $this->selectedWorkshop = Workshop::findOrFail($id);
        $this->showDelete = true;
    }

    // ==========================
    // CRUD ACTIONS
    // ==========================

    public function confirmSuspend()
    {
        if ($this->selectedWorkshop && Schema::hasColumn('workshops', 'status')) {
            $newStatus = $this->selectedWorkshop->status === 'suspended' ? 'active' : 'suspended';
            $this->selectedWorkshop->update(['status' => $newStatus]);
            
            $this->reset(['showSuspend', 'selectedWorkshop']);
            session()->flash('message', 'Status bengkel berhasil diubah.');
        }
    }

    public function confirmDelete()
    {
        if ($this->selectedWorkshop) {
            $this->selectedWorkshop->delete();
            
            $this->reset(['showDelete', 'selectedWorkshop']);
            session()->flash('message', 'Bengkel berhasil dihapus.');
        }
    }

    public function closeModal()
    {
        $this->reset(['showDetail', 'showEdit', 'showDelete', 'showSuspend', 'selectedWorkshop']);
    }

    public function render()
    {
        return view('livewire.admin.workshops.index', [
            'statusOptions' => $this->statusOptions,
            'cityOptions'   => $this->cityOptions,
        ]);
    }
}
