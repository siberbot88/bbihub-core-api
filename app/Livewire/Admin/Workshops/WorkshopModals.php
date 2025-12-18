<?php

namespace App\Livewire\Admin\Workshops;

use App\Models\Workshop;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class WorkshopModals extends Component
{
    // Modal control
    public bool $showDetail  = false;
    public bool $showCreate  = false;
    public bool $showEdit    = false;
    public bool $showDelete  = false;
    public bool $showSuspend = false;

    public ?Workshop $selectedWorkshop = null;

    // ====== FORM FIELDS (sesuai tabel) ======
    public string $user_uuid = '';          // required
    public string $status = 'pending';      // required enum
    public ?string $code = null;            // required (tapi bisa auto-generate)
    public string $name = '';               // required
    public string $description = '';        // required
    public string $address = '';            // required
    public string $phone = '';              // required
    public string $email = '';              // required
    public ?string $photo = null;           // required (tapi model kamu auto-generate kalau kosong)
    public string $city = '';               // required
    public string $province = '';           // required
    public string $country = '';            // required
    public string $postal_code = '';        // required
    public float|string $latitude = '';     // required decimal
    public float|string $longitude = '';    // required decimal
    public ?string $maps_url = null;        // nullable
    public string $opening_time = '';       // required time
    public string $closing_time = '';       // required time
    public string $operational_days = '';   // required varchar (isi string)
    public bool $is_active = true;          // required tinyint(1)

    public function mount(): void
    {
        $this->resetModal();
    }

    public function resetModal(): void
    {
        $this->showDetail  = false;
        $this->showCreate  = false;
        $this->showEdit    = false;
        $this->showDelete  = false;
        $this->showSuspend = false;

        $this->selectedWorkshop = null;

        // default values
        $this->user_uuid = (string) (auth()->id() ?? '');
        $this->status = 'pending';
        $this->code = null;
        $this->name = '';
        $this->description = '';
        $this->address = '';
        $this->phone = '';
        $this->email = '';
        $this->photo = null;
        $this->city = '';
        $this->province = '';
        $this->country = '';
        $this->postal_code = '';
        $this->latitude = '';
        $this->longitude = '';
        $this->maps_url = null;
        $this->opening_time = '';
        $this->closing_time = '';
        $this->operational_days = '';
        $this->is_active = true;

        $this->resetErrorBag();
        $this->resetValidation();
    }

    // ==========================
    // EVENT LISTENERS
    // ==========================
    #[On('workshop:create')]
    public function openCreate(): void
    {
        $this->resetModal();
        $this->showCreate = true;
    }

    #[On('workshop:view')]
    public function openView(string $id): void
    {
        $this->resetModal();
        $this->selectedWorkshop = Workshop::findOrFail($id);
        $this->showDetail = true;
    }

    #[On('workshop:edit')]
    public function openEdit(string $id): void
    {
        $this->resetModal();
        $this->selectedWorkshop = Workshop::findOrFail($id);

        $w = $this->selectedWorkshop;

        $this->user_uuid = (string) $w->user_uuid;
        $this->status = (string) $w->status;
        $this->code = (string) $w->code;
        $this->name = (string) $w->name;
        $this->description = (string) $w->description;
        $this->address = (string) $w->address;
        $this->phone = (string) $w->phone;
        $this->email = (string) $w->email;
        $this->photo = (string) $w->photo;
        $this->city = (string) $w->city;
        $this->province = (string) $w->province;
        $this->country = (string) $w->country;
        $this->postal_code = (string) $w->postal_code;
        $this->latitude = (string) $w->latitude;
        $this->longitude = (string) $w->longitude;
        $this->maps_url = $w->maps_url;
        $this->opening_time = (string) $w->opening_time;
        $this->closing_time = (string) $w->closing_time;
        $this->operational_days = (string) $w->operational_days;
        $this->is_active = (bool) $w->is_active;

        $this->showEdit = true;
    }

    #[On('workshop:delete')]
    public function openDelete(string $id): void
    {
        $this->resetModal();
        $this->selectedWorkshop = Workshop::findOrFail($id);
        $this->showDelete = true;
    }

    #[On('workshop:suspend')]
    public function openSuspend(string $id): void
    {
        $this->resetModal();
        $this->selectedWorkshop = Workshop::findOrFail($id);
        $this->showSuspend = true;
    }

    // ==========================
    // VALIDATION
    // ==========================
    private function rulesCreate(): array
    {
        return [
            'user_uuid' => ['required', 'string', 'size:36'],
            'status' => ['required', 'in:pending,active,suspended,rejected'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', 'unique:workshops,code'],
            'description' => ['required', 'string'],
            'address' => ['required', 'string'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'photo' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'maps_url' => ['nullable', 'string'],
            'opening_time' => ['required', 'date_format:H:i'],
            'closing_time' => ['required', 'date_format:H:i'],
            'operational_days' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    private function rulesUpdate(string $id): array
    {
        return [
            'user_uuid' => ['required', 'string', 'size:36'],
            'status' => ['required', 'in:pending,active,suspended,rejected'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', 'unique:workshops,code,' . $id],
            'description' => ['required', 'string'],
            'address' => ['required', 'string'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'photo' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'maps_url' => ['nullable', 'string'],
            'opening_time' => ['required', 'date_format:H:i'],
            'closing_time' => ['required', 'date_format:H:i'],
            'operational_days' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    private function sanitize(): void
    {
        $this->user_uuid = trim($this->user_uuid);
        $this->status = trim($this->status);
        $this->name = trim($this->name);
        $this->code = $this->code !== null ? trim((string) $this->code) : null;

        $this->description = trim($this->description);
        $this->address = trim($this->address);
        $this->phone = trim($this->phone);
        $this->email = Str::lower(trim($this->email));

        $this->photo = $this->photo !== null ? trim((string) $this->photo) : null;

        $this->city = trim($this->city);
        $this->province = trim($this->province);
        $this->country = trim($this->country);
        $this->postal_code = trim($this->postal_code);

        $this->maps_url = $this->maps_url !== null ? trim((string) $this->maps_url) : null;
        $this->opening_time = trim($this->opening_time);
        $this->closing_time = trim($this->closing_time);
        $this->operational_days = trim($this->operational_days);
    }

    private function ensureCode(): void
    {
        if (!blank($this->code)) return;

        $base = 'WS-' . Str::upper(Str::substr(Str::slug($this->name ?: 'WORKSHOP'), 0, 8));
        $candidate = $base;
        $i = 1;

        while (Workshop::where('code', $candidate)->exists()) {
            $candidate = $base . '-' . $i++;
            if ($i > 2000) {
                $candidate = $base . '-' . Str::upper(Str::random(5));
                break;
            }
        }

        $this->code = $candidate;
    }

    // ==========================
    // CRUD
    // ==========================
    public function createWorkshop(): void
    {
        $this->sanitize();
        $this->ensureCode();

        $this->validate($this->rulesCreate());

        DB::beginTransaction();
        try {
            Workshop::create([
                'user_uuid' => $this->user_uuid,
                'status' => $this->status,
                'code' => $this->code,
                'name' => $this->name,
                'description' => $this->description,
                'address' => $this->address,
                'phone' => $this->phone,
                'email' => $this->email,
                // kalau kosong, model Workshop::booted() kamu akan isi default photo
                'photo' => $this->photo,
                'city' => $this->city,
                'province' => $this->province,
                'country' => $this->country,
                'postal_code' => $this->postal_code,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'maps_url' => $this->maps_url,
                'opening_time' => $this->opening_time,
                'closing_time' => $this->closing_time,
                'operational_days' => $this->operational_days,
                'is_active' => $this->is_active,
            ]);

            DB::commit();

            $this->resetModal();
            session()->flash('message', 'Bengkel berhasil ditambahkan.');
            $this->dispatch('workshops:refresh'); // kalau kamu pakai listener di index
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            session()->flash('error', $e->getMessage());
        }
    }

    public function updateWorkshop(): void
    {
        if (!$this->selectedWorkshop) {
            session()->flash('error', 'Bengkel tidak ditemukan.');
            return;
        }

        $this->sanitize();
        $this->ensureCode();

        $this->validate($this->rulesUpdate($this->selectedWorkshop->id));

        DB::beginTransaction();
        try {
            $this->selectedWorkshop->update([
                'user_uuid' => $this->user_uuid,
                'status' => $this->status,
                'code' => $this->code,
                'name' => $this->name,
                'description' => $this->description,
                'address' => $this->address,
                'phone' => $this->phone,
                'email' => $this->email,
                'photo' => $this->photo,
                'city' => $this->city,
                'province' => $this->province,
                'country' => $this->country,
                'postal_code' => $this->postal_code,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'maps_url' => $this->maps_url,
                'opening_time' => $this->opening_time,
                'closing_time' => $this->closing_time,
                'operational_days' => $this->operational_days,
                'is_active' => $this->is_active,
            ]);

            DB::commit();

            $this->resetModal();
            session()->flash('message', 'Bengkel berhasil diperbarui.');
            $this->dispatch('workshops:refresh');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            session()->flash('error', $e->getMessage());
        }
    }

    public function closeModal(): void
    {
        $this->resetModal();
    }

    public function render()
    {
        return view('livewire.admin.workshops.modals');
    }
}
