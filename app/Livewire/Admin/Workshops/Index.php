<?php

namespace App\Livewire\Admin\Workshops;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use App\Models\Workshop;

#[Title('Manajemen Bengkel')]
#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    // URL Query Params
    #[Url] public string $q = '';
    #[Url] public string $status = 'all';
    #[Url] public string $city = 'all';
    #[Url] public int $perPage = 10;

    // Modal states
    public bool $showDetail  = false;
    public bool $showEdit    = false;
    public bool $showDelete  = false;
    public bool $showSuspend = false;
    public bool $showReset   = false;

    // Selected workshop
    public ?Workshop $selectedWorkshop = null;

    // Password fields
    public string $oldPassword = '';
    public string $newPassword = '';
    public string $confirmPassword = '';

    // Dropdowns
    public array $statusOptions = [
        'all'       => 'Semua Status',
        'pending'   => 'Menunggu Verifikasi',
        'active'    => 'Aktif',
        'suspended' => 'Ditangguhkan',
    ];

    public array $cityOptions = ['all' => 'Semua Kota'];

    public function mount(): void
    {
        // Cache city options
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
                $options[$c] = ucfirst($c);
            }
            return $options;
        });

        $this->resetModal();
    }

    private function resetModal(): void
    {
        $this->showDetail  = false;
        $this->showEdit    = false;
        $this->showDelete  = false;
        $this->showSuspend = false;
        $this->showReset   = false;

        $this->selectedWorkshop = null;
        $this->oldPassword = '';
        $this->newPassword = '';
        $this->confirmPassword = '';
    }

    // Reset pagination
    public function updatingQ()       { $this->resetPage(); }
    public function updatingStatus()  { $this->resetPage(); }
    public function updatingCity()    { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    #[Computed]
    public function cards(): array
    {
        $base = Workshop::query();
        $hasStatus = Schema::hasColumn('workshops', 'status');

        return [
            [
                'label' => 'Total Bengkel',
                'value' => $base->count(),
                'hint'  => 'update +5%',
                'color' => 'blue',
            ],
            [
                'label' => 'Menunggu Verifikasi',
                'value' => $hasStatus ? (clone $base)->where('status', 'pending')->count() : 0,
                'hint'  => 'update +2%',
                'color' => 'yellow',
            ],
            [
                'label' => 'Bengkel Aktif',
                'value' => $hasStatus ? (clone $base)->where('status', 'active')->count() : 0,
                'hint'  => 'update +5%',
                'color' => 'green',
            ],
            [
                'label' => 'Bengkel Ditangguhkan',
                'value' => $hasStatus ? (clone $base)->where('status', 'suspended')->count() : 0,
                'hint'  => 'update +5%',
                'color' => 'red',
            ],
        ];
    }

    // ==========================
    // Modal Openers
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

    public function delete($id)
    {
        $this->selectedWorkshop = Workshop::findOrFail($id);
        $this->showDelete = true;
    }

    public function resetPassword($id)
    {
        $this->selectedWorkshop = Workshop::findOrFail($id);
        $this->showReset = true;
    }

    public function suspend($id)
    {
        $this->selectedWorkshop = Workshop::findOrFail($id);
        $this->showSuspend = true;
    }

    // ==========================
    // ACTIONS
    // ==========================
    public function updateWorkshop()
    {
        if ($this->selectedWorkshop) {
            $this->selectedWorkshop->save();
            session()->flash('message', 'Data bengkel berhasil diperbarui.');
        }
        $this->resetModal();
    }

    public function updatePassword()
    {
        $this->validate([
            'newPassword'     => 'required|min:8|same:confirmPassword',
            'confirmPassword' => 'required|min:8',
        ]);

        if ($this->selectedWorkshop) {
            $this->selectedWorkshop->update([
                'password' => Hash::make($this->newPassword),
            ]);
            session()->flash('message', 'Password berhasil diubah.');
        }

        $this->resetModal();
    }

    public function confirmDelete()
    {
        if ($this->selectedWorkshop) {
            $this->selectedWorkshop->delete();
            session()->flash('message', 'Bengkel berhasil dihapus.');
        }
        $this->resetModal();
    }

    public function confirmSuspend()
    {
        if ($this->selectedWorkshop && Schema::hasColumn('workshops', 'status')) {

            $newStatus = $this->selectedWorkshop->status === 'suspended'
                ? 'active'
                : 'suspended';

            $this->selectedWorkshop->update(['status' => $newStatus]);

            session()->flash('message', 'Status bengkel berhasil diperbarui.');
        }

        $this->resetModal();
    }

    public function closeModal()
    {
        $this->resetModal();
    }

    // ==========================
    // WORKSHOP LIST
    // ==========================
    #[Computed]
    public function workshops()
    {
        $hasStatus = Schema::hasColumn('workshops', 'status');
        $hasRating = Schema::hasColumn('workshops', 'rating');

        $columns = ['id', 'name', 'code', 'city', 'created_at'];
        if ($hasStatus) $columns[] = 'status';
        if ($hasRating) $columns[] = 'rating';

        $query = Workshop::query()->select($columns);

        if ($this->q !== '') {
            $query->where(function ($w) {
                $w->where('name', 'like', "%{$this->q}%")
                  ->orWhere('code', 'like', "%{$this->q}%");
            });
        }

        if ($this->status !== 'all' && $hasStatus) {
            $query->where('status', $this->status);
        }

        if ($this->city !== 'all') {
            $query->where('city', $this->city);
        }

        return $query->latest('id')->paginate($this->perPage);
    }

    // ==========================
    // RENDER
    // ==========================
    public function render()
    {
        return view('livewire.admin.workshops.index', [
            'rows' => $this->workshops(),
            'cards' => $this->cards,
            'statusOptions' => $this->statusOptions,
            'cityOptions' => $this->cityOptions,
        ]);
    }
}
