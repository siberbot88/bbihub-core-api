<div class="p-6">
  <div class="mx-auto max-w-3xl rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4">
      <div class="flex items-start gap-3">
        <div class="grid h-10 w-10 place-items-center rounded-xl bg-red-50 text-red-600">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 6v12m6-6H6"/>
          </svg>
        </div>
        <div>
          <h2 class="text-xl font-bold text-gray-900">Tambah Data — Pusat Data</h2>
          <p class="mt-1 text-sm text-gray-500">
            Form akan menyesuaikan kategori data yang dipilih.
          </p>
        </div>
      </div>

      <a href="{{ route('admin.data-center') }}"
         class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-600 hover:bg-gray-50">
        Tutup ✕
      </a>
    </div>

    {{-- Flash --}}
    @if (session()->has('message'))
      <div class="mt-4 rounded-xl bg-emerald-50 px-4 py-3 text-emerald-700">
        {{ session('message') }}
      </div>
    @endif
    @if (session()->has('error'))
      <div class="mt-4 rounded-xl bg-rose-50 px-4 py-3 text-rose-700">
        {{ session('error') }}
      </div>
    @endif

    <form wire:submit.prevent="save" class="mt-6 space-y-6">
      @csrf

      {{-- Kategori --}}
      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Kategori</label>
        <select wire:model.live="category"
                class="block w-full rounded-xl border-gray-200 bg-white px-3 py-2.5 shadow-sm focus:border-red-300 focus:ring-red-200">
          <option value="">Pilih Kategori</option>
          <option value="users">Pengguna</option>
          <option value="workshops">Bengkel</option>
          <option value="vehicles">Kendaraan</option>
        </select>
        @error('category') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
      </div>

      {{-- =========================
           FORM PENGGUNA
      ========================= --}}
      @if($category === 'users')
        <div class="rounded-2xl border border-gray-200 p-5">
          <div class="mb-4">
            <h3 class="text-base font-semibold text-gray-900">Form Pengguna</h3>
            <p class="text-sm text-gray-500">Lengkapi data pengguna yang akan ditambahkan.</p>
          </div>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-sm font-medium text-gray-700">Nama</label>
              <input type="text" wire:model.defer="name"
                     class="w-full rounded-xl border-gray-200 px-3 py-2.5 focus:border-red-300 focus:ring-red-200"
                     placeholder="Nama lengkap">
              @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-gray-700">Username</label>
              <input type="text" wire:model.defer="username"
                     class="w-full rounded-xl border-gray-200 px-3 py-2.5 focus:border-red-300 focus:ring-red-200"
                     placeholder="username">
              @error('username') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
              <label class="mb-1 block text-sm font-medium text-gray-700">Email</label>
              <input type="email" wire:model.defer="email"
                     class="w-full rounded-xl border-gray-200 px-3 py-2.5 focus:border-red-300 focus:ring-red-200"
                     placeholder="email@domain.com">
              @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
              <label class="mb-1 block text-sm font-medium text-gray-700">Password</label>
              <input type="password" wire:model.defer="password"
                     class="w-full rounded-xl border-gray-200 px-3 py-2.5 focus:border-red-300 focus:ring-red-200"
                     placeholder="Minimal 8 karakter">
              @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-gray-700">Role</label>
              <select wire:model.live="role"
                      class="w-full rounded-xl border-gray-200 bg-white px-3 py-2.5 focus:border-red-300 focus:ring-red-200">
                <option value="">Pilih Role</option>
                @foreach(($roles ?? []) as $r)
                  <option value="{{ $r['name'] }}">{{ ucwords(str_replace(['-','_'], ' ', $r['name'])) }}</option>
                @endforeach
              </select>
              @error('role') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
              <p class="mt-1 text-xs text-gray-400">Contoh: superadmin, admin, owner, mechanic.</p>
            </div>

            <div>
              @php $needsWorkshop = in_array($role ?? '', ['owner','mechanic']); @endphp
              <label class="mb-1 block text-sm font-medium text-gray-700">
                Bengkel <span class="text-xs text-gray-400">{{ $needsWorkshop ? '(Wajib)' : '(Opsional)' }}</span>
              </label>
              <select wire:model.defer="workshop_id"
                      class="w-full rounded-xl border-gray-200 bg-white px-3 py-2.5 focus:border-red-300 focus:ring-red-200">
                <option value="">Pilih Bengkel</option>
                @foreach(($workshops ?? []) as $w)
                  <option value="{{ $w['id'] }}">{{ $w['name'] }}</option>
                @endforeach
              </select>
              @error('workshop_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
          </div>
        </div>
      @endif

      {{-- =========================
           FORM BENGKEL
      ========================= --}}
      @if($category === 'workshops')
        <div class="rounded-2xl border border-gray-200 p-5">
          <div class="mb-4">
            <h3 class="text-base font-semibold text-gray-900">Form Bengkel</h3>
            <p class="text-sm text-gray-500">Lengkapi data bengkel yang akan ditambahkan.</p>
          </div>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
              <label class="mb-1 block text-sm font-medium text-gray-700">Nama Bengkel</label>
              <input type="text" wire:model.defer="workshop_name"
                     class="w-full rounded-xl border-gray-200 px-3 py-2.5 focus:border-red-300 focus:ring-red-200"
                     placeholder="Nama bengkel">
              @error('workshop_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-gray-700">Kode Bengkel (opsional)</label>
              <input type="text" wire:model.defer="workshop_code"
                     class="w-full rounded-xl border-gray-200 px-3 py-2.5 focus:border-red-300 focus:ring-red-200"
                     placeholder="WS-001 (boleh kosong, auto)">
              @error('workshop_code') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-gray-700">Kota (opsional)</label>
              <input type="text" wire:model.defer="workshop_city"
                     class="w-full rounded-xl border-gray-200 px-3 py-2.5 focus:border-red-300 focus:ring-red-200"
                     placeholder="Bandung / Jakarta / ...">
              @error('workshop_city') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
              <label class="mb-1 block text-sm font-medium text-gray-700">Status</label>
              <select wire:model.defer="workshop_status"
                      class="w-full rounded-xl border-gray-200 bg-white px-3 py-2.5 focus:border-red-300 focus:ring-red-200">
                <option value="active">Active</option>
                <option value="pending">Pending</option>
                <option value="inactive">Inactive</option>
              </select>
              @error('workshop_status') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
          </div>
        </div>
      @endif

      {{-- =========================
           FORM KENDARAAN
      ========================= --}}
      @if($category === 'vehicles')
        <div class="rounded-2xl border border-gray-200 p-5">
          <div class="mb-4">
            <h3 class="text-base font-semibold text-gray-900">Form Kendaraan</h3>
            <p class="text-sm text-gray-500">Kolom sesuai tabel: customer_uuid, code, name, type, category, brand, model, year, color, plate_number, odometer.</p>
          </div>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-sm font-medium text-gray-700">Customer UUID</label>
              <input type="text" wire:model.defer="vehicle_customer_uuid"
                     class="w-full rounded-xl border-gray-200 px-3 py-2.5 focus:border-red-300 focus:ring-red-200"
                     placeholder="uuid customer">
              @error('vehicle_customer_uuid') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-gray-700">Code (opsional)</label>
              <input type="text" wire:model.defer="vehicle_code"
                     class="w-full rounded-xl border-gray-200 px-3 py-2.5 focus:border-red-300 focus:ring-red-200"
                     placeholder="auto jika kosong">
              @error('vehicle_code') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
              <label class="mb-1 block text-sm font-medium text-gray-700">Name</label>
              <input type="text" wire:model.defer="vehicle_name"
                     class="w-full rounded-xl border-gray-200 px-3 py-2.5 focus:border-red-300 focus:ring-red-200"
                     placeholder="Nama kendaraan">
              @error('vehicle_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-gray-700">Type</label>
              <input type="text" wire:model.defer="vehicle_type"
                     class="w-full rounded-xl border-gray-200 px-3 py-2.5 focus:border-red-300 focus:ring-red-200">
              @error('vehicle_type') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-gray-700">Category</label>
              <input type="text" wire:model.defer="vehicle_category"
                     class="w-full rounded-xl border-gray-200 px-3 py-2.5 focus:border-red-300 focus:ring-red-200">
              @error('vehicle_category') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-gray-700">Brand</label>
              <input type="text" wire:model.defer="vehicle_brand"
                     class="w-full rounded-xl border-gray-200 px-3 py-2.5 focus:border-red-300 focus:ring-red-200">
              @error('vehicle_brand') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-gray-700">Model</label>
              <input type="text" wire:model.defer="vehicle_model"
                     class="w-full rounded-xl border-gray-200 px-3 py-2.5 focus:border-red-300 focus:ring-red-200">
              @error('vehicle_model') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-gray-700">Year</label>
              <input type="number" wire:model.defer="vehicle_year"
                     class="w-full rounded-xl border-gray-200 px-3 py-2.5 focus:border-red-300 focus:ring-red-200"
                     placeholder="2020">
              @error('vehicle_year') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-gray-700">Color</label>
              <input type="text" wire:model.defer="vehicle_color"
                     class="w-full rounded-xl border-gray-200 px-3 py-2.5 focus:border-red-300 focus:ring-red-200">
              @error('vehicle_color') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-gray-700">Plate Number</label>
              <input type="text" wire:model.defer="vehicle_plate_number"
                     class="w-full rounded-xl border-gray-200 px-3 py-2.5 focus:border-red-300 focus:ring-red-200"
                     placeholder="D 1234 ABC">
              @error('vehicle_plate_number') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-gray-700">Odometer</label>
              <input type="number" wire:model.defer="vehicle_odometer"
                     class="w-full rounded-xl border-gray-200 px-3 py-2.5 focus:border-red-300 focus:ring-red-200"
                     placeholder="15000">
              @error('vehicle_odometer') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
          </div>
        </div>
      @endif

      {{-- Tombol --}}
      <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.data-center') }}"
           class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
          Kembali
        </a>

        <button type="submit"
                class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-60"
                @disabled(!$category)>
          Simpan
        </button>
      </div>
    </form>
  </div>
</div>
