<div class="space-y-6">
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold">Tambah Pengguna</h1>
      <p class="text-sm text-gray-500">Isi data pengguna baru di bawah ini.</p>
    </div>
    <a href="{{ route('admin.users') }}" class="rounded-lg bg-gray-100 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200">
      Kembali
    </a>
  </div>

  {{-- Form Tambah Pengguna --}}
  <form wire:submit.prevent="save" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-5">
    {{-- Upload Foto --}}
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Foto Profil *</label>
      <input
        type="file"
        wire:model="photo"
        accept="image/*"
        class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100"
      >
      @error('photo') <span class="text-sm text-red-600">{{ $message }}</span> @enderror

      {{-- Preview sementara --}}
      @if ($photo)
        <div class="mt-3">
          <p class="text-xs text-gray-500 mb-1">Preview:</p>
          <img src="{{ $photo->temporaryUrl() }}" class="h-20 w-20 rounded-full object-cover border">
        </div>
      @endif
    </div>

    {{-- Username --}}
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Username *</label>
      <input
        type="text"
        wire:model.defer="username"
        placeholder="Masukkan username"
        class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100"
      >
      @error('username') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
    </div>

    {{-- Nama Lengkap --}}
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
      <input
        type="text"
        wire:model.defer="name"
        placeholder="Masukkan nama lengkap"
        class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100"
      >
      @error('name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
    </div>

    {{-- Email --}}
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
      <input
        type="email"
        wire:model.defer="email"
        placeholder="Masukkan email"
        class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100"
      >
      @error('email') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
    </div>

    {{-- No. HP --}}
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">No. HP *</label>
      <input
        type="text"
        wire:model.defer="phone"
        placeholder="Masukkan nomor HP"
        class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100"
      >
      @error('phone') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
    </div>

    {{-- Role --}}
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
      <select
        wire:model="role"
        class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100"
      >
        <option value="">Pilih Role</option>
        <option value="superadmin">Superadmin</option>
        <option value="owner">Owner</option>
        <option value="admin">Admin</option>
        <option value="mechanic">Mekanik</option>
      </select>
      @error('role') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
    </div>

    {{-- Nama Bengkel & Alamat / Lokasi --}}
    @if ($role && $role !== 'superadmin')
      {{-- Nama Bengkel --}}
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Nama Bengkel *
          @if (in_array($role, ['admin','mechanic']))
            <span class="text-xs text-gray-500">(ketik untuk mencari)</span>
          @endif
        </label>

        {{-- OWNER: input biasa --}}
        @if ($role === 'owner')
          <input
            type="text"
            wire:model.defer="nama_bengkel"
            placeholder="Masukkan nama bengkel"
            class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100"
          >
        @else
          {{-- ADMIN / MEKANIK: auto-complete workshop --}}
          <div class="relative">
            <input
              type="text"
              wire:model.debounce.500ms="nama_bengkel"
              placeholder="Ketik nama bengkel"
              class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100"
            >

            {{-- dropdown suggestion --}}
            @if (!empty($bengkelOptions) && strlen($nama_bengkel ?? '') >= 2)
              <div class="absolute z-20 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-md max-h-64 overflow-auto">
                @forelse ($bengkelOptions as $bengkel)
                  <button
                    type="button"
                    wire:click="pilihBengkel('{{ $bengkel->id }}')"
                    class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-100"
                  >
                    <div class="font-medium">{{ $bengkel->name }}</div>
                    <div class="text-xs text-gray-500">
                      {{ $bengkel->city ?? '-' }}{{ $bengkel->province ? ' - '.$bengkel->province : '' }}
                    </div>
                  </button>
                @empty
                  <div class="px-3 py-2 text-sm text-gray-500">Tidak ada hasil</div>
                @endforelse
              </div>
            @endif
          </div>
        @endif

        @error('nama_bengkel') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
        @error('selected_workshop_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
      </div>

      {{-- Alamat + Kota / Provinsi / Negara --}}
      @if ($role === 'owner')
        {{-- Owner isi sendiri semua --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Alamat *</label>
          <textarea
            wire:model.defer="alamat"
            rows="3"
            placeholder="Masukkan alamat bengkel"
            class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100"
          ></textarea>
          @error('alamat') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kota *</label>
            <input
              type="text"
              wire:model.defer="city"
              placeholder="Masukkan kota"
              class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100"
            >
            @error('city') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Provinsi *</label>
            <input
              type="text"
              wire:model.defer="province"
              placeholder="Masukkan provinsi"
              class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100"
            >
            @error('province') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Negara *</label>
            <input
              type="text"
              wire:model.defer="country"
              placeholder="Masukkan negara"
              class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100"
            >
            @error('country') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
          </div>
        </div>
      @elseif (in_array($role, ['admin','mechanic']) && $nama_bengkel)
        {{-- Admin / Mekanik: tampilkan data bengkel yang terpilih (read-only) --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Bengkel</label>
          <textarea
            rows="3"
            class="w-full rounded-lg border-gray-200 bg-gray-50 text-sm"
            readonly
          >{{ $alamat }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kota</label>
            <input
              type="text"
              class="w-full rounded-lg border-gray-200 bg-gray-50 text-sm"
              readonly
              value="{{ $city }}"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Provinsi</label>
            <input
              type="text"
              class="w-full rounded-lg border-gray-200 bg-gray-50 text-sm"
              readonly
              value="{{ $province }}"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Negara</label>
            <input
              type="text"
              class="w-full rounded-lg border-gray-200 bg-gray-50 text-sm"
              readonly
              value="{{ $country }}"
            >
          </div>
        </div>
      @endif
    @endif

    {{-- Password --}}
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
      <input
        type="password"
        wire:model.defer="password"
        placeholder="Masukkan password"
        class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100"
      >
      @error('password') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
    </div>

    {{-- Tombol Simpan --}}
    <div class="flex items-center justify-end gap-3 pt-4">
      <a href="{{ route('admin.users') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
        Batal
      </a>
      <button type="submit" class="rounded-lg bg-red-600 px-5 py-2 text-sm font-semibold text-white hover:bg-red-700 shadow">
        Simpan Pengguna
      </button>
    </div>
  </form>
</div>
