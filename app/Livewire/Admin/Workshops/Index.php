<?php

namespace App\Livewire\Admin\Workshops;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
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
    #[Url] public int $perPage = 8;

    // Modal control
    public bool $showDetail = false;
    public bool $showEdit   = false;
    public bool $showDelete = false;
    public bool $showReset  = false;
    public bool $showSuspend = false; // ⬅ WAJIB ADA

    // Selected workshop
    public ?Workshop $selectedWorkshop = null;

    // Password fields
    public string $oldPassword = '';
    public string $newPassword = '';
    public string $confirmPassword = '';

    // Dropdown options
    public array $statusOptions = [
        'all'       => 'Semua Status',
        'pending'   => 'Menunggu Verifikasi',
        'active'    => 'Aktif',
        'suspended' => 'Ditangguhkan',
    ];

    public array $cityOptions = ['all' => 'Semua Kota'];

    public function mount(): void
    {
        // Generate city dropdown from DB
        $cities = Workshop::query()
            ->select('city')
            ->distinct()
            ->pluck('city')
            ->filter()
            ->values();

        foreach ($cities as $c) {
            $this->cityOptions[$c] = ucfirst($c);
        }

        $this->resetModal();
    }

    private function resetModal(): void
    {
        $this->showDetail = false;
        $this->showEdit   = false;
        $this->showDelete = false;
        $this->showReset  = false;
        $this->showSuspend = false;

        $this->selectedWorkshop = null;

        $this->oldPassword = '';
        $this->newPassword = '';
        $this->confirmPassword = '';
    }

    // Reset pagination on filter change
    public function updatingQ()       { $this->resetPage(); }
    public function updatingStatus()  { $this->resetPage(); }
    public function updatingCity()    { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    /**
     * Summary Cards (same format as user management)
     */
    public function getCardsProperty(): array
    {
        $base = Workshop::query();
        $hasStatus = Schema::hasColumn('workshops', 'status');

        return [
            [
                'label' => 'Total Bengkel',
                'value' => (clone $base)->count(),
                'hint'  => 'update +5%',
                'icon'  => 'total_bengkel',
                'color' => 'blue',
            ],
            [
                'label' => 'Menunggu Verifikasi',
                'value' => $hasStatus ? (clone $base)->where('status', 'pending')->count() : 0,
                'hint'  => 'update +2%',
                'icon'  => 'total_verifikasi',
                'color' => 'yellow',
            ],
            [
                'label' => 'Bengkel Aktif',
                'value' => $hasStatus ? (clone $base)->where('status', 'active')->count() : 0,
                'hint'  => 'update +5%',
                'icon'  => 'akun_aktif',
                'color' => 'green',
            ],
            [
                'label' => 'Bengkel Ditangguhkan',
                'value' => $hasStatus ? (clone $base)->where('status', 'suspended')->count() : 0,
                'hint'  => 'update +5%',
                'icon'  => 'akun_tidak_aktif',
                'color' => 'red',
            ],
        ];
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
        $this->showSuspend = true; // ⬅ modal explicit
    }

    // ==========================
    // ACTION HANDLERS
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

    public function confirmDelete($id)
    {
        $workshop = Workshop::findOrFail($id);
        $workshop->delete();

        $this->resetModal();
        session()->flash('message', 'Bengkel berhasil dihapus.');
    }

    public function confirmSuspend()
    {
        if ($this->selectedWorkshop && Schema::hasColumn('workshops', 'status')) {

            $new = $this->selectedWorkshop->status === 'suspended'
                ? 'active'
                : 'suspended';

            $this->selectedWorkshop->update(['status' => $new]);

            session()->flash('message', 'Status bengkel berhasil diperbarui.');
        }

        $this->resetModal();
    }

    // ==========================
    // RENDER
    // ==========================
    public function render()
    {
        $query = Workshop::query();

        if ($this->q !== '') {
            $query->where(function ($w) {
                $w->where('name', 'like', "%{$this->q}%")
                  ->orWhere('code', 'like', "%{$this->q}%");
            });
        }

        if ($this->status !== 'all' && Schema::hasColumn('workshops', 'status')) {
            $query->where('status', $this->status);
        }

        if ($this->city !== 'all') {
            $query->where('city', $this->city);
        }

        $rows = $query->latest('id')->paginate($this->perPage);

        return view('livewire.admin.workshops.index', [
            'rows'     => $rows,
            'cards'    => $this->cards,
            'statusOptions' => $this->statusOptions,
            'cityOptions'   => $this->cityOptions,
        ]);
    }
}
